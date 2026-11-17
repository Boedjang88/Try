<?php

namespace App\Http\Controllers;

use App\Actions\CalculateMaxSksAction;
use App\Models\AcademicPeriod;
use App\Models\ClassSection;
use App\Models\Course;
use App\Models\KrsItem;
use App\Models\KrsSubmission;
use App\Models\Student;
use App\Services\TimetableConflictService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class KrsController extends Controller
{
    /**
     * Show Student KRS War Cart Workspace.
     */
    public function index(Request $request, CalculateMaxSksAction $calculateMaxSks): View|RedirectResponse
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            abort(403, 'Akses terbatas untuk akun mahasiswa.');
        }

        // Financial Clearance Gate
        if (!$student->is_financially_cleared) {
            return redirect('/portal/student/finance')->with('error', 'Status keuangan Anda belum terverifikasi lunas (Financial Hold). Selesaikan tagihan untuk membuka akses KRS War.');
        }

        $activePeriod = AcademicPeriod::where('is_active', true)->first();
        if (!$activePeriod) {
            abort(422, 'Tidak ada periode akademik aktif saat ini. Hubungi BAAK.');
        }

        // Compute authoritative max SKS allowed
        $maxSks = $calculateMaxSks->execute($student);

        // Fetch or create draft submission
        $submission = KrsSubmission::firstOrCreate([
            'student_id' => $student->id,
            'academic_period_id' => $activePeriod->id,
        ], [
            'total_sks' => 0,
            'status' => 'draft',
        ]);

        $submission->load(['items.classSection.course', 'items.classSection.lecturer.user', 'items.classSection.room']);

        // Available Class Sections for Student's Study Program in Active Period
        $availableSections = ClassSection::where('academic_period_id', $activePeriod->id)
            ->whereHas('course', function ($q) use ($student) {
                $q->where('study_program_id', $student->study_program_id);
            })
            ->with(['course.prerequisites', 'lecturer.user', 'room'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        $enrolledSectionIds = $submission->items->pluck('class_section_id')->toArray();

        return view('krs.student-cart', compact(
            'student',
            'activePeriod',
            'maxSks',
            'submission',
            'availableSections',
            'enrolledSectionIds'
        ));
    }

    /**
     * Atomic Course Enrollment in KRS Cart (Pessimistic Seat Quota Lock & Clash Check).
     */
    public function addCourse(Request $request, TimetableConflictService $conflictService): JsonResponse
    {
        $validated = $request->validate([
            'class_section_id' => 'required|exists:class_sections,id',
        ]);

        $user = Auth::user();
        $student = $user->student;

        if (!$student || !$student->is_financially_cleared) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Anda memiliki penangguhan akademik atau finansial (Financial Hold).',
            ], 403);
        }

        $activePeriod = AcademicPeriod::where('is_active', true)->firstOrFail();
        if (!$activePeriod->isKrsWindowOpen()) {
            return response()->json([
                'success' => false,
                'message' => 'Periode pengisian KRS online sedang ditutup.',
            ], 403);
        }

        return DB::transaction(function () use ($student, $activePeriod, $validated, $conflictService) {
            // 1. Pessimistic Lock on ClassSection row to eliminate race conditions
            $section = ClassSection::where('id', $validated['class_section_id'])
                ->lockForUpdate()
                ->with(['course.prerequisites', 'room'])
                ->firstOrFail();

            // 2. Validate Quota Availability
            if ($section->is_closed || $section->enrolled_count >= $section->max_quota) {
                return response()->json([
                    'success' => false,
                    'code' => 'SEATS_EXHAUSTED',
                    'message' => "Kuota kursi untuk kelas {$section->course->code} ({$section->section_code}) telah penuh ({$section->enrolled_count}/{$section->max_quota}).",
                ], 422);
            }

            // 3. Find or Create Draft Submission
            $submission = KrsSubmission::firstOrCreate([
                'student_id' => $student->id,
                'academic_period_id' => $activePeriod->id,
            ], [
                'total_sks' => 0,
                'status' => 'draft',
            ]);

            if ($submission->status === 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'KRS Anda telah disetujui Dosen PA dan tidak dapat diubah kembali.',
                ], 422);
            }

            // 4. Duplicate Check: Cannot take same section or same course twice in one semester
            $existingItem = KrsItem::where('krs_submission_id', $submission->id)
                ->where('class_section_id', $section->id)
                ->exists();

            if ($existingItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mata kuliah ini telah ada di dalam keranjang KRS Anda.',
                ], 422);
            }

            // 5. Schedule Conflict Check
            $clashingSection = $conflictService->hasStudentClash($student->id, $section->id, $activePeriod->id);
            if ($clashingSection) {
                return response()->json([
                    'success' => false,
                    'code' => 'SCHEDULE_CLASH',
                    'message' => "Jadwal bentrok dengan mata kuliah {$clashingSection->course->name_id} ({$clashingSection->day_name}, {$clashingSection->start_time} - {$clashingSection->end_time}).",
                ], 422);
            }

            // 6. Max SKS Allowance Check
            $newTotalSks = $submission->total_sks + $section->course->sks;
            if ($newTotalSks > $student->max_sks_allowed) {
                return response()->json([
                    'success' => false,
                    'code' => 'SKS_EXCEEDED',
                    'message' => "Batas maksimum SKS terlampaui. Maksimal yang diizinkan: {$student->max_sks_allowed} SKS. Total jika ditambahkan: {$newTotalSks} SKS.",
                ], 422);
            }

            // 7. Prerequisite Verification
            if ($section->course->prerequisites->isNotEmpty()) {
                $passedCourseIds = KrsItem::whereHas('krsSubmission', function ($q) use ($student) {
                    $q->where('student_id', $student->id)->where('status', 'approved');
                })
                ->whereNotNull('letter_grade')
                ->whereNotIn('letter_grade', ['E', 'D', 'F'])
                ->pluck('class_section_id');

                $passedCourses = ClassSection::whereIn('id', $passedCourseIds)->pluck('course_id')->toArray();

                foreach ($section->course->prerequisites as $prereq) {
                    if (!in_array($prereq->id, $passedCourses, true)) {
                        return response()->json([
                            'success' => false,
                            'code' => 'PREREQUISITE_UNFULFILLED',
                            'message' => "Prasyarat belum terpenuhi: Anda wajib lulus mata kuliah {$prereq->code} ({$prereq->name_id}) dengan nilai minimum {$prereq->pivot->minimum_grade}.",
                        ], 422);
                    }
                }
            }

            // 8. Atomic Seat Increment & KrsItem Creation
            $section->enrolled_count++;
            if ($section->enrolled_count >= $section->max_quota) {
                $section->is_closed = true;
            }
            $section->save();

            // Check if course was previously taken by student in any semester (Retake / Mengulang)
            $isRetake = KrsItem::whereHas('krsSubmission', function ($q) use ($student) {
                $q->where('student_id', $student->id);
            })
            ->whereHas('classSection', function ($q) use ($section) {
                $q->where('course_id', $section->course_id);
            })
            ->exists();

            KrsItem::create([
                'krs_submission_id' => $submission->id,
                'class_section_id' => $section->id,
                'is_retake' => $isRetake,
            ]);

            $submission->total_sks = $newTotalSks;
            $submission->save();

            // Audit
            $student->recordAudit(
                'KRS_SEAT_LOCKED',
                "Seat booked in {$section->course->code} ({$section->section_code}) for student [{$student->nim}]"
            );

            return response()->json([
                'success' => true,
                'message' => "Mata kuliah {$section->course->name_id} berhasil ditambahkan ke KRS.",
                'current_sks' => $submission->total_sks,
                'max_sks' => $student->max_sks_allowed,
                'enrolled_count' => $section->enrolled_count,
                'max_quota' => $section->max_quota,
                'remaining_seats' => $section->remaining_seats,
            ]);
        });
    }

    /**
     * Remove Course from KRS Cart (Releases Seat Quota under lockForUpdate).
     */
    public function removeCourse(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'class_section_id' => 'required|exists:class_sections,id',
        ]);

        $user = Auth::user();
        $student = $user->student;
        $activePeriod = AcademicPeriod::where('is_active', true)->firstOrFail();

        return DB::transaction(function () use ($student, $activePeriod, $validated) {
            $submission = KrsSubmission::where('student_id', $student->id)
                ->where('academic_period_id', $activePeriod->id)
                ->firstOrFail();

            if ($submission->status === 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'KRS yang telah disetujui Dosen PA tidak dapat diubah.',
                ], 422);
            }

            $item = KrsItem::where('krs_submission_id', $submission->id)
                ->where('class_section_id', $validated['class_section_id'])
                ->first();

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mata kuliah tidak ditemukan dalam KRS Anda.',
                ], 404);
            }

            // Lock section and decrement seats
            $section = ClassSection::where('id', $validated['class_section_id'])
                ->lockForUpdate()
                ->with('course')
                ->firstOrFail();

            $section->enrolled_count = max(0, $section->enrolled_count - 1);
            if ($section->enrolled_count < $section->max_quota) {
                $section->is_closed = false;
            }
            $section->save();

            $submission->total_sks = max(0, $submission->total_sks - $section->course->sks);
            $submission->save();

            $item->delete();

            $student->recordAudit(
                'KRS_SEAT_RELEASED',
                "Seat released from {$section->course->code} ({$section->section_code}) for student [{$student->nim}]"
            );

            return response()->json([
                'success' => true,
                'message' => "Mata kuliah {$section->course->name_id} berhasil dibatalkan dari KRS.",
                'current_sks' => $submission->total_sks,
                'max_sks' => $student->max_sks_allowed,
                'enrolled_count' => $section->enrolled_count,
                'max_quota' => $section->max_quota,
                'remaining_seats' => $section->remaining_seats,
            ]);
        });
    }

    /**
     * Submit KRS to Dosen Pembimbing Akademik (Dosen PA).
     */
    public function submitKrs(Request $request): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $student = $user->student;
        $activePeriod = AcademicPeriod::where('is_active', true)->firstOrFail();

        $submission = KrsSubmission::where('student_id', $student->id)
            ->where('academic_period_id', $activePeriod->id)
            ->firstOrFail();

        if ($submission->status === 'submitted') {
            return response()->json([
                'success' => true,
                'message' => 'KRS telah berhasil diajukan sebelumnya (Idempotent).',
                'qr_token' => $submission->qr_token,
            ]);
        }

        if ($submission->status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'KRS telah disetujui Dosen PA dan tidak dapat diajukan ulang.',
            ], 422);
        }

        if ($submission->items()->count() === 0) {
            $msg = 'Keranjang KRS Anda masih kosong. Pilih mata kuliah terlebih dahulu.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        $lock = \Illuminate\Support\Facades\Cache::lock('krs_submit_' . $student->id, 5);
        if (!$lock->get()) {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan KRS sedang diproses dalam antrean transaksi aman.',
            ], 429);
        }

        try {
            $submission->status = 'submitted';
            $submission->qr_token = 'QRS-KRS-' . strtoupper(Str::random(12));
            $submission->save();

            $student->recordAudit('KRS_SUBMITTED', "KRS submitted to Academic Advisor with total {$submission->total_sks} SKS");

            // Notify Academic Advisor
            if ($student->academicAdvisor && $student->academicAdvisor->user) {
                \App\Services\NotificationService::notify(
                    $student->academicAdvisor->user,
                    'krs_submitted',
                    'Pengajuan KRS Mahasiswa',
                    "Mahasiswa {$student->user->full_name} ({$student->nim}) telah mengajukan KRS ({$submission->total_sks} SKS) untuk persetujuan Dosen PA.",
                    '/portal/lecturer/krs-approval'
                );
            }
        } finally {
            $lock->release();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'KRS berhasil diajukan ke Dosen Pembimbing Akademik (Dosen PA).',
                'qr_token' => $submission->qr_token,
            ]);
        }

        return back()->with('success', 'KRS berhasil diajukan ke Dosen Pembimbing Akademik untuk peninjauan.');
    }

    /**
     * Dosen PA Approval Workspace.
     */
    public function advisorApprovalWorkspace(Request $request): View
    {
        $user = Auth::user();
        $lecturer = $user->lecturer;

        if (!$lecturer) {
            abort(403, 'Akses khusus untuk Dosen Pembimbing Akademik.');
        }

        $activePeriod = AcademicPeriod::where('is_active', true)->firstOrFail();

        // Get advisees' KRS submissions
        $adviseeSubmissions = KrsSubmission::whereHas('student', function ($q) use ($lecturer) {
            $q->where('academic_advisor_id', $lecturer->id);
        })
        ->where('academic_period_id', $activePeriod->id)
        ->with(['student.user', 'items.classSection.course', 'items.classSection.room'])
        ->orderByRaw("CASE status WHEN 'submitted' THEN 1 WHEN 'draft' THEN 2 WHEN 'approved' THEN 3 WHEN 'rejected' THEN 4 ELSE 5 END")
        ->get();

        return view('krs.advisor-approval', compact('lecturer', 'activePeriod', 'adviseeSubmissions'));
    }

    /**
     * Dosen PA Approves Student KRS.
     */
    public function approveKrs(Request $request, KrsSubmission $krsSubmission): JsonResponse
    {
        $user = Auth::user();
        $lecturer = $user->lecturer;

        // Anti-IDOR Authorization: Only assigned Academic Advisor or superadmin/baak may approve
        if ($user->role === 'lecturer') {
            if (!$lecturer || $krsSubmission->student->academic_advisor_id !== $lecturer->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak: Anda bukan Dosen Pembimbing Akademik yang ditugaskan untuk mahasiswa ini.',
                ], 403);
            }
        } elseif (!in_array($user->role, ['superadmin', 'baak'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Hanya Dosen Pembimbing Akademik yang berwenang menyetujui KRS.',
            ], 403);
        }

        $krsSubmission->status = 'approved';
        $krsSubmission->approved_by_lecturer_id = $lecturer ? $lecturer->id : null;
        $krsSubmission->approved_at = now();
        $krsSubmission->rejection_note = null;
        $krsSubmission->save();

        $krsSubmission->student->recordAudit(
            'KRS_APPROVED',
            "KRS approved by Academic Advisor [{$user->full_name}] with {$krsSubmission->total_sks} SKS"
        );

        // Notify Student
        if ($krsSubmission->student->user) {
            \App\Services\NotificationService::notify(
                $krsSubmission->student->user,
                'krs_approved',
                'KRS Telah Disetujui Dosen PA',
                "KRS Anda untuk periode ini telah disetujui resmi oleh Dosen Pembimbing Akademik ({$user->full_name}). Cetak Kartu Rencana Studi resmi kini aktif.",
                '/portal/student/krs'
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'KRS mahasiswa berhasil disetujui resmi.',
        ]);
    }

    /**
     * Dosen PA Rejects Student KRS with Guidance Notes.
     */
    public function rejectKrs(Request $request, KrsSubmission $krsSubmission): JsonResponse
    {
        $user = Auth::user();
        $lecturer = $user->lecturer;

        // Anti-IDOR Authorization: Only assigned Academic Advisor or superadmin/baak may reject
        if ($user->role === 'lecturer') {
            if (!$lecturer || $krsSubmission->student->academic_advisor_id !== $lecturer->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak: Anda bukan Dosen Pembimbing Akademik yang ditugaskan untuk mahasiswa ini.',
                ], 403);
            }
        } elseif (!in_array($user->role, ['superadmin', 'baak'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Hanya Dosen Pembimbing Akademik yang berwenang menolak KRS.',
            ], 403);
        }

        $validated = $request->validate([
            'rejection_note' => 'required|string|max:500',
        ]);

        $krsSubmission->status = 'rejected';
        $krsSubmission->rejection_note = $validated['rejection_note'];
        $krsSubmission->save();

        $krsSubmission->student->recordAudit(
            'KRS_REVISION_REQUESTED',
            "KRS revision requested by Academic Advisor [{$user->full_name}]: {$validated['rejection_note']}"
        );

        // Notify Student
        if ($krsSubmission->student->user) {
            \App\Services\NotificationService::notify(
                $krsSubmission->student->user,
                'krs_revision_requested',
                'Catatan Revisi KRS dari Dosen PA',
                "Dosen Pembimbing Akademik meminta revisi pada pengisian KRS Anda. Catatan: {$validated['rejection_note']}",
                '/portal/student/krs'
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Catatan revisi KRS berhasil dikirimkan ke mahasiswa.',
        ]);
    }

    /**
     * Print Official QR-Verified Kartu Rencana Studi (KRS) PDF.
     */
    public function printPdf(KrsSubmission $krsSubmission): View
    {
        $krsSubmission->load([
            'student.user',
            'student.studyProgram.faculty',
            'student.academicAdvisor.user',
            'academicPeriod',
            'items.classSection.course',
            'items.classSection.lecturer.user',
            'items.classSection.room',
            'approvedByLecturer.user'
        ]);

        return view('krs.print-pdf', compact('krsSubmission'));
    }
}
