<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\GradeAppeal;
use App\Models\KrsItem;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GradeAppealController extends Controller
{
    /**
     * Provide detailed OBE Score breakdown & CPMK attainment for a course.
     */
    public function getBreakdown(Request $request, KrsItem $krsItem): JsonResponse
    {
        $user = Auth::user();
        $student = $user->student;

        // Ensure authorization (student owner, lecturer, or kaprodi)
        if ($student && $krsItem->krsSubmission->student_id !== $student->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $krsItem->load(['classSection.course.cpmks', 'classSection.lecturer.user', 'gradeAppeals']);

        $course = $krsItem->classSection->course;

        // Weights
        $wPresence = 10.0;
        $wTask = 20.0;
        $wQuiz = 10.0;
        $wUts = 25.0;
        $wUas = 25.0;
        $wProject = 10.0;

        $rawPresence = (float)($krsItem->score_presence ?? 0.0);
        $rawTask = (float)($krsItem->score_task ?? 0.0);
        $rawQuiz = (float)($krsItem->score_quiz ?? 0.0);
        $rawUts = (float)($krsItem->score_uts ?? 0.0);
        $rawUas = (float)($krsItem->score_uas ?? 0.0);
        $rawProject = (float)($krsItem->score_project ?? 0.0);

        $weightedPresence = round(($rawPresence * $wPresence) / 100, 2);
        $weightedTask = round(($rawTask * $wTask) / 100, 2);
        $weightedQuiz = round(($rawQuiz * $wQuiz) / 100, 2);
        $weightedUts = round(($rawUts * $wUts) / 100, 2);
        $weightedUas = round(($rawUas * $wUas) / 100, 2);
        $weightedProject = round(($rawProject * $wProject) / 100, 2);

        $calculatedFinal = round(
            $weightedPresence + $weightedTask + $weightedQuiz + $weightedUts + $weightedUas + $weightedProject,
            2
        );

        // CPMK Attainment
        $cpmks = $course->cpmks ?? collect();
        $cpmkAttainments = $cpmks->map(function ($cpmk, $index) use ($rawTask, $rawUts, $rawUas) {
            // Map CPMK weights to composite task/exam performance
            $targetWeight = (float)($cpmk->weight_percentage ?? 25.0);
            $compositeScore = round(($rawTask * 0.3) + ($rawUts * 0.35) + ($rawUas * 0.35), 1);
            $isMet = $compositeScore >= 70.0;

            return [
                'code' => $cpmk->code ?? ('CPMK-0' . ($index + 1)),
                'description' => $cpmk->description ?? 'Kompetensi Capaian Pembelajaran Mata Kuliah',
                'target_weight' => $targetWeight,
                'attainment_percentage' => $compositeScore,
                'status' => $isMet ? 'Tercapai (Passed)' : 'Perlu Remediasi',
            ];
        });

        $latestAppeal = $krsItem->gradeAppeals()->latest()->first();

        return response()->json([
            'success' => true,
            'course' => [
                'code' => $course->code,
                'name' => $course->name,
                'sks' => $course->sks,
                'lecturer' => $krsItem->classSection->lecturer->user->full_name ?? 'Dosen Pengampu',
            ],
            'components' => [
                'presence' => ['weight' => 10, 'raw' => $rawPresence, 'weighted' => $weightedPresence],
                'task' => ['weight' => 20, 'raw' => $rawTask, 'weighted' => $weightedTask],
                'quiz' => ['weight' => 10, 'raw' => $rawQuiz, 'weighted' => $weightedQuiz],
                'uts' => ['weight' => 25, 'raw' => $rawUts, 'weighted' => $weightedUts],
                'uas' => ['weight' => 25, 'raw' => $rawUas, 'weighted' => $weightedUas],
                'project' => ['weight' => 10, 'raw' => $rawProject, 'weighted' => $weightedProject],
            ],
            'summary' => [
                'final_numeric_score' => $krsItem->final_numeric_score ?? $calculatedFinal,
                'letter_grade' => $krsItem->letter_grade ?? 'E',
                'grade_point' => $krsItem->grade_point ?? 0.00,
                'is_locked' => (bool)$krsItem->is_locked,
            ],
            'cpmk_attainments' => $cpmkAttainments,
            'appeal' => $latestAppeal ? [
                'id' => $latestAppeal->id,
                'status' => $latestAppeal->status,
                'reason' => $latestAppeal->reason,
                'lecturer_response' => $latestAppeal->lecturer_response,
                'created_at' => $latestAppeal->created_at->format('d M Y H:i'),
            ] : null,
        ]);
    }

    /**
     * Student submits Grade Appeal (Sanggah Nilai).
     */
    public function submitAppeal(Request $request, KrsItem $krsItem): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student || $krsItem->krsSubmission->student_id !== $student->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $validated = $request->validate([
            'reason' => 'required|string|min:15|max:1000',
            'evidence_file' => 'nullable|file|mimes:pdf,jpg,png,zip|max:10240',
        ]);

        $evidencePath = null;
        if ($request->hasFile('evidence_file')) {
            $evidencePath = $request->file('evidence_file')->store('private/grade_appeals', 'local');
        }

        $appeal = GradeAppeal::create([
            'id' => (string) Str::uuid(),
            'krs_item_id' => $krsItem->id,
            'student_id' => $student->id,
            'reason' => $validated['reason'],
            'evidence_file_path' => $evidencePath,
            'status' => 'submitted',
        ]);

        // Notify Lecturer
        $lecturer = $krsItem->classSection->lecturer;
        if ($lecturer && $lecturer->user) {
            NotificationService::notify(
                $lecturer->user,
                'grade_appeal_submitted',
                'Sanggah Nilai Masuk: ' . $krsItem->classSection->course->name,
                "Mahasiswa {$student->user->full_name} ({$student->nim}) mengajukan sanggah nilai untuk mata kuliah {$krsItem->classSection->course->name}.",
                '/portal/lecturer/appeals'
            );
        }

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'GRADE_APPEAL_SUBMITTED',
            'description' => "Student {$student->nim} submitted grade appeal for course {$krsItem->classSection->course->code}.",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pengajuan sanggah nilai berhasil dikirimkan ke dosen pengampu.',
                'appeal' => $appeal,
            ]);
        }

        return redirect()->back()->with('success', 'Pengajuan sanggah nilai berhasil dikirimkan ke dosen pengampu.');
    }

    /**
     * Lecturer appeals workspace.
     */
    public function lecturerIndex(Request $request): View
    {
        $user = Auth::user();
        $lecturer = $user->lecturer;

        if (!$lecturer) {
            abort(403, 'Akses khusus Dosen.');
        }

        $appeals = GradeAppeal::whereHas('krsItem.classSection', function ($q) use ($lecturer) {
            $q->where('lecturer_id', $lecturer->id);
        })
        ->with(['krsItem.classSection.course', 'student.user'])
        ->orderByDesc('created_at')
        ->paginate(15);

        return view('portal.lecturer.appeals', [
            'appeals' => $appeals,
            'lecturer' => $lecturer,
        ]);
    }

    /**
     * Lecturer responds to Grade Appeal (Request unlock or reject).
     */
    public function lecturerRespond(Request $request, GradeAppeal $gradeAppeal): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $lecturer = $user->lecturer;

        $validated = $request->validate([
            'action_type' => 'required|in:request_unlock,reject',
            'response_note' => 'required|string|max:1000',
        ]);

        $krsItem = $gradeAppeal->krsItem;

        if ($validated['action_type'] === 'request_unlock') {
            $gradeAppeal->update([
                'status' => 'unlock_requested',
                'lecturer_response' => $validated['response_note'],
                'lecturer_reviewed_at' => now(),
            ]);

            // Notify Kaprodi for grade unlock
            $kaprodiUser = \App\Models\User::whereHas('lecturer', function ($q) use ($lecturer) {
                $q->where('study_program_id', $lecturer->study_program_id);
            })->where('role', 'kaprodi')->first();

            if ($kaprodiUser) {
                NotificationService::notify(
                    $kaprodiUser,
                    'grade_unlock_requested',
                    'Permohonan Buka Kunci Nilai: ' . $krsItem->classSection->course->name,
                    "Dosen {$lecturer->user->full_name} mengajukan pembukaan kunci nilai pasca-sanggah nilai untuk NIM {$gradeAppeal->student->nim}.",
                    '/portal/kaprodi/grade-locks'
                );
            }

            $message = 'Permohonan buka kunci nilai telah diteruskan ke Kaprodi.';
        } else {
            $gradeAppeal->update([
                'status' => 'rejected',
                'lecturer_response' => $validated['response_note'],
                'lecturer_reviewed_at' => now(),
            ]);

            $message = 'Sanggah nilai telah ditolak dengan catatan.';
        }

        // Notify student
        if ($gradeAppeal->student && $gradeAppeal->student->user) {
            NotificationService::notify(
                $gradeAppeal->student->user,
                'grade_appeal_resolved',
                'Tanggapan Sanggah Nilai Dosen',
                "Dosen telah menanggapi sanggah nilai Anda: {$validated['response_note']}",
                '/portal/student/khs'
            );
        }

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'GRADE_APPEAL_RESPONDED',
            'description' => "Lecturer responded to grade appeal of {$gradeAppeal->student->nim}. Status: {$gradeAppeal->status}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => $gradeAppeal->status,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }
}
