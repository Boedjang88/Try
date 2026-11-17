<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AcademicLeaveRequest;
use App\Models\AcademicPeriod;
use App\Models\AuditLog;
use App\Models\Student;
use App\Models\StudentInvoice;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AcademicLeaveController extends Controller
{
    /**
     * Display Academic Leave Workspace for Students, Advisors, and Kaprodi.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $student = $user->student;
        $activePeriod = AcademicPeriod::where('is_active', true)->first();

        // If student: display their own leave requests
        $myRequests = $student ? AcademicLeaveRequest::where('student_id', $student->id)
            ->with(['academicPeriod', 'advisor', 'kaprodi'])
            ->latest()
            ->get() : collect();

        // If lecturer/advisor: display advisee requests
        $adviseeRequests = collect();
        if ($user->hasRole('lecturer') && $user->lecturer) {
            $adviseeStudentIds = Student::where('academic_advisor_id', $user->lecturer->id)->pluck('id');
            $adviseeRequests = AcademicLeaveRequest::whereIn('student_id', $adviseeStudentIds)
                ->with(['student.user', 'academicPeriod'])
                ->latest()
                ->get();
        }

        // If kaprodi: display department requests
        $prodiRequests = collect();
        if ($user->hasRole('kaprodi') || $user->hasRole('superadmin')) {
            $prodiStudentIds = Student::where('study_program_id', $user->study_program_id)->pluck('id');
            $prodiRequests = AcademicLeaveRequest::whereIn('student_id', $prodiStudentIds)
                ->with(['student.user', 'academicPeriod', 'advisor'])
                ->latest()
                ->get();
        }

        return view('portal.student.leave-request', compact(
            'user',
            'student',
            'activePeriod',
            'myRequests',
            'adviseeRequests',
            'prodiRequests'
        ));
    }

    /**
     * Student submits a new Academic Leave Request.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            abort(403, 'Akses ditolak: Hanya akun mahasiswa yang dapat mengajukan cuti akademik.');
        }

        $activePeriod = AcademicPeriod::where('is_active', true)->firstOrFail();

        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:1000',
            'supporting_doc' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $docPath = null;
        if ($request->hasFile('supporting_doc')) {
            $docPath = $request->file('supporting_doc')->store('leave_documents', 'public');
        }

        $leaveRequest = AcademicLeaveRequest::create([
            'student_id' => $student->id,
            'academic_period_id' => $activePeriod->id,
            'reason' => $validated['reason'],
            'document_path' => $docPath,
            'status' => 'pending',
        ]);

        // Notify Academic Advisor (Dosen PA)
        if ($student->academicAdvisor && $student->academicAdvisor->user) {
            NotificationService::notify(
                $student->academicAdvisor->user,
                'leave_submitted',
                'Pengajuan Cuti Akademik Mahasiswa',
                "Mahasiswa {$student->user->full_name} ({$student->nim}) mengajukan Cuti Akademik untuk periode {$activePeriod->name}.",
                '/portal/student/leave-request'
            );
        }

        return redirect()->back()->with('success', 'Pengajuan cuti akademik berhasil diajukan dan sedang menunggu persetujuan Dosen Pembimbing Akademik.');
    }

    /**
     * Dosen PA approves leave request and forwards to Kaprodi.
     */
    public function advisorApprove(Request $request, AcademicLeaveRequest $leaveRequest)
    {
        $user = Auth::user();
        $student = $leaveRequest->student;

        // Verify advisor scope unless superadmin
        if (!$user->hasRole('superadmin')) {
            if (!$user->lecturer || $student->academic_advisor_id !== $user->lecturer->id) {
                abort(403, 'Akses ditolak: Anda bukan Dosen Pembimbing Akademik mahasiswa ini.');
            }
        }

        $leaveRequest->update([
            'status' => 'advisor_approved',
            'approved_by_advisor_id' => $user->id,
        ]);

        // Notify Kaprodi
        $kaprodi = $student->studyProgram?->headOfProgram;
        if ($kaprodi) {
            NotificationService::notify(
                $kaprodi,
                'leave_advisor_approved',
                'Rekomendasi Cuti Akademik dari Dosen PA',
                "Dosen PA telah menyetujui pengajuan cuti mahasiswa {$student->user->full_name} ({$student->nim}). Membutuhkan persetujuan akhir Kaprodi.",
                '/portal/student/leave-request'
            );
        }

        // Notify Student
        NotificationService::notify(
            $student->user,
            'leave_advisor_approved',
            'Rekomendasi Dosen PA Diterima',
            'Pengajuan cuti akademik Anda telah disetujui Dosen PA dan diteruskan ke Kaprodi untuk pengesahan final.',
            '/portal/student/leave-request'
        );

        return redirect()->back()->with('success', 'Rekomendasi cuti akademik berhasil disetujui dan diteruskan ke Kaprodi.');
    }

    /**
     * Kaprodi gives final approval, updates student status, and adjusts tuition to holding fee.
     */
    public function kaprodiApprove(Request $request, AcademicLeaveRequest $leaveRequest)
    {
        $user = Auth::user();
        $student = $leaveRequest->student;

        // Verify Kaprodi prodi scope unless superadmin
        if (!$user->hasRole('superadmin')) {
            if (!$user->hasRole('kaprodi') || $user->study_program_id !== $student->study_program_id) {
                abort(403, 'Akses ditolak: Anda tidak memiliki wewenang untuk menyetujui cuti di program studi ini.');
            }
        }

        $leaveRequest->update([
            'status' => 'approved',
            'approved_by_kaprodi_id' => $user->id,
        ]);

        // Update student academic status to on_leave
        $student->update([
            'academic_status' => 'on_leave',
        ]);

        // Adjust active tuition invoice to administrative holding fee (Rp 1.500.000)
        $invoice = StudentInvoice::where('student_id', $student->id)
            ->where('academic_period_id', $leaveRequest->academic_period_id)
            ->where('payment_status', '!=', 'paid')
            ->first();

        if ($invoice) {
            $invoice->update([
                'title' => 'Biaya Administrasi Cuti Akademik (Holding Fee)',
                'amount' => 1500000.00,
            ]);
        }

        // Audit Trail
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'ACADEMIC_LEAVE_APPROVED',
            'auditable_type' => AcademicLeaveRequest::class,
            'auditable_id' => $leaveRequest->id,
            'old_values' => ['academic_status' => 'active'],
            'new_values' => ['academic_status' => 'on_leave', 'holding_fee' => 1500000.00],
            'reason' => "Pengesahan cuti akademik resmi periode {$leaveRequest->academicPeriod->name} oleh Kaprodi",
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'user_agent' => $request->userAgent() ?? 'Browser',
        ]);

        // Notify Student
        NotificationService::notify(
            $student->user,
            'leave_approved',
            'Cuti Akademik Resmi Disetujui',
            "Pengajuan Cuti Akademik Anda untuk periode {$leaveRequest->academicPeriod->name} telah disetujui resmi oleh Kaprodi. Tagihan SPP telah disesuaikan menjadi biaya administrasi cuti (Rp 1.500.000).",
            '/portal/student/billing'
        );

        return redirect()->back()->with('success', 'Cuti akademik resmi disahkan. Status mahasiswa telah diubah menjadi Cuti dan tagihan disesuaikan ke biaya holding.');
    }

    /**
     * Reject Leave Request with reason.
     */
    public function reject(Request $request, AcademicLeaveRequest $leaveRequest)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:5|max:500',
        ]);

        $leaveRequest->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        // Notify Student
        NotificationService::notify(
            $leaveRequest->student->user,
            'leave_rejected',
            'Pengajuan Cuti Akademik Ditolak',
            "Pengajuan Cuti Akademik Anda ditolak dengan alasan: {$validated['rejection_reason']}",
            '/portal/student/leave-request'
        );

        return redirect()->back()->with('info', 'Pengajuan cuti akademik telah ditolak.');
    }
}
