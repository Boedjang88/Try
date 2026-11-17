<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\AuditLog;
use App\Models\Course;
use App\Models\MbkmConversion;
use App\Models\MbkmProgram;
use App\Models\Student;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MbkmConversionController extends Controller
{
    /**
     * Student MBKM Workspace: Check eligibility and list registrations.
     */
    public function studentIndex(Request $request): View
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            abort(403, 'Akses terbatas untuk mahasiswa aktif.');
        }

        $activePeriod = AcademicPeriod::where('is_active', true)->first();
        $isEligible = ($student->current_semester >= 5);

        $programs = MbkmProgram::where('student_id', $student->id)
            ->with(['conversions.course', 'academicPeriod'])
            ->orderByDesc('created_at')
            ->get();

        $studyProgramCourses = Course::where('study_program_id', $student->study_program_id)
            ->orderBy('semester')
            ->orderBy('name')
            ->get();

        return view('portal.student.mbkm', [
            'student' => $student,
            'isEligible' => $isEligible,
            'activePeriod' => $activePeriod,
            'programs' => $programs,
            'studyProgramCourses' => $studyProgramCourses,
        ]);
    }

    /**
     * Student registers new MBKM program.
     */
    public function storeProgram(Request $request): RedirectResponse|JsonResponse
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Mahasiswa tidak ditemukan.'], 403);
        }

        if ($student->current_semester < 5) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pendaftaran MBKM hanya diperbolehkan untuk mahasiswa minimal Semester 5.',
                ], 422);
            }
            return redirect()->back()->with('error', 'Pendaftaran MBKM hanya diperbolehkan untuk mahasiswa minimal Semester 5.');
        }

        $validated = $request->validate([
            'program_type' => 'required|string|in:magang_industri,pertukaran_mahasiswa,studi_independen,riset,proyek_kemanusiaan',
            'partner_name' => 'required|string|max:255',
            'partner_address' => 'nullable|string|max:255',
            'mentor_name' => 'nullable|string|max:150',
            'mentor_contact' => 'nullable|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'logbook_document' => 'nullable|file|mimes:pdf,zip|max:10240',
        ]);

        $activePeriod = AcademicPeriod::where('is_active', true)->firstOrFail();

        $logbookPath = null;
        if ($request->hasFile('logbook_document')) {
            $logbookPath = $request->file('logbook_document')->store('private/mbkm_documents', 'local');
        }

        $mbkm = MbkmProgram::create([
            'id' => (string) Str::uuid(),
            'student_id' => $student->id,
            'academic_period_id' => $activePeriod->id,
            'program_type' => $validated['program_type'],
            'partner_name' => $validated['partner_name'],
            'partner_address' => $validated['partner_address'] ?? null,
            'mentor_name' => $validated['mentor_name'] ?? null,
            'mentor_contact' => $validated['mentor_contact'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'logbook_document_path' => $logbookPath,
            'status' => 'pending',
        ]);

        // Notify Kaprodi
        $kaprodiUser = \App\Models\User::whereHas('lecturer', function ($q) use ($student) {
            $q->where('study_program_id', $student->study_program_id);
        })->where('role', 'kaprodi')->first();

        if ($kaprodiUser) {
            NotificationService::notify(
                $kaprodiUser,
                'mbkm_submitted',
                'Pengajuan Program MBKM Mahasiswa: ' . $student->nim,
                "Mahasiswa {$student->user->full_name} ({$student->nim}) mengajukan program MBKM [{$mbkm->partner_name}] untuk konversi SKS.",
                '/portal/kaprodi/mbkm'
            );
        }

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'MBKM_PROGRAM_REGISTERED',
            'description' => "Student {$student->nim} registered MBKM program [{$mbkm->program_type}] with [{$mbkm->partner_name}].",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Program MBKM berhasil didaftarkan dan menunggu verifikasi konversi Kaprodi.',
                'program' => $mbkm,
            ]);
        }

        return redirect()->back()->with('success', 'Program MBKM berhasil didaftarkan dan menunggu konversi SKS oleh Kaprodi.');
    }

    /**
     * Kaprodi MBKM Workspace: View and approve SKS conversion mappings.
     */
    public function kaprodiIndex(Request $request): View
    {
        $user = Auth::user();
        $lecturer = $user->lecturer;

        if (!$lecturer || !$lecturer->study_program_id) {
            abort(403, 'Akses terbatas untuk Kaprodi.');
        }

        $programs = MbkmProgram::whereHas('student', function ($q) use ($lecturer) {
            $q->where('study_program_id', $lecturer->study_program_id);
        })
        ->with(['student.user', 'academicPeriod', 'conversions.course'])
        ->orderByDesc('created_at')
        ->paginate(15);

        $studyProgramCourses = Course::where('study_program_id', $lecturer->study_program_id)
            ->orderBy('semester')
            ->orderBy('name')
            ->get();

        return view('portal.kaprodi.mbkm', [
            'programs' => $programs,
            'courses' => $studyProgramCourses,
            'lecturer' => $lecturer,
        ]);
    }

    /**
     * Kaprodi Approves MBKM SKS Conversion (Up to 20 SKS limit).
     */
    public function approveConversion(Request $request, MbkmProgram $mbkmProgram): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $lecturer = $user->lecturer;

        $validated = $request->validate([
            'conversions' => 'required|array|min:1',
            'conversions.*.course_id' => 'required|uuid|exists:courses,id',
            'conversions.*.equivalent_sks' => 'required|integer|min:1|max:6',
            'conversions.*.converted_numeric_score' => 'required|numeric|min:0|max:100',
            'approval_notes' => 'nullable|string|max:500',
        ]);

        // Calculate total SKS: Maximum 20 SKS policy enforcement
        $totalSks = collect($validated['conversions'])->sum('equivalent_sks');
        if ($totalSks > 20) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Total konversi MBKM melebihi batas maksimum 20 SKS (Direncanakan: {$totalSks} SKS).",
                ], 422);
            }
            return redirect()->back()->with('error', "Total konversi MBKM melebihi batas 20 SKS (Direncanakan: {$totalSks} SKS).");
        }

        DB::transaction(function () use ($validated, $mbkmProgram, $lecturer, $totalSks, $user, $request) {
            // Delete previous draft conversions if any
            $mbkmProgram->conversions()->delete();

            foreach ($validated['conversions'] as $item) {
                $score = (float)$item['converted_numeric_score'];
                $gradeInfo = \App\Models\KrsItem::convertToGradePoint($score);

                MbkmConversion::create([
                    'id' => (string) Str::uuid(),
                    'mbkm_program_id' => $mbkmProgram->id,
                    'course_id' => $item['course_id'],
                    'equivalent_sks' => $item['equivalent_sks'],
                    'converted_numeric_score' => $score,
                    'converted_letter_grade' => $gradeInfo['letter'],
                    'grade_point' => $gradeInfo['point'],
                ]);
            }

            $mbkmProgram->update([
                'status' => 'approved',
                'approval_notes' => $validated['approval_notes'] ?? 'Disetujui oleh Kaprodi dengan konversi ' . $totalSks . ' SKS.',
                'approved_by_kaprodi_id' => $lecturer ? $lecturer->id : null,
                'approved_at' => now(),
            ]);

            // Notify student
            if ($mbkmProgram->student && $mbkmProgram->student->user) {
                NotificationService::notify(
                    $mbkmProgram->student->user,
                    'mbkm_approved',
                    'Konversi SKS MBKM Disetujui!',
                    "Program MBKM di [{$mbkmProgram->partner_name}] telah disetujui oleh Kaprodi dengan total {$totalSks} SKS ekuivalen.",
                    '/portal/student/mbkm'
                );
            }

            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'MBKM_CONVERSION_APPROVED',
                'description' => "Kaprodi approved MBKM conversion for Student NIM: {$mbkmProgram->student->nim} ({$totalSks} SKS converted).",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Konversi MBKM berhasil disetujui ({$totalSks} SKS ekuivalen).",
                'total_sks' => $totalSks,
            ]);
        }

        return redirect()->back()->with('success', "Konversi MBKM berhasil disetujui ({$totalSks} SKS ekuivalen).");
    }

    /**
     * Kaprodi Rejects MBKM Registration.
     */
    public function rejectProgram(Request $request, MbkmProgram $mbkmProgram): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $mbkmProgram->update([
            'status' => 'rejected',
            'approval_notes' => $validated['rejection_reason'],
            'approved_at' => now(),
        ]);

        if ($mbkmProgram->student && $mbkmProgram->student->user) {
            NotificationService::notify(
                $mbkmProgram->student->user,
                'mbkm_rejected',
                'Pengajuan MBKM Ditolak',
                "Pengajuan MBKM di [{$mbkmProgram->partner_name}] ditolak oleh Kaprodi: {$validated['rejection_reason']}",
                '/portal/student/mbkm'
            );
        }

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'MBKM_PROGRAM_REJECTED',
            'description' => "MBKM program for Student {$mbkmProgram->student->nim} rejected. Reason: {$validated['rejection_reason']}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pengajuan MBKM telah ditolak.',
            ]);
        }

        return redirect()->back()->with('warning', 'Pengajuan MBKM telah ditolak.');
    }
}
