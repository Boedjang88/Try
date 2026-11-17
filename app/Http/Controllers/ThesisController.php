<?php

namespace App\Http\Controllers;

use App\Models\Lecturer;
use App\Models\Student;
use App\Models\Thesis;
use App\Models\ThesisGuidanceLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ThesisController extends Controller
{
    /**
     * Display Thesis Lifecycle Dashboard (Proposal, Guidance Logbook, Defense & Rubrics).
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $student = $user->student;
        $lecturer = $user->lecturer;

        $myThesis = null;
        $supervisingTheses = collect();
        $allTheses = collect();
        $lecturers = Lecturer::with('user')->get();

        if ($user->role === 'student' && $student) {
            $myThesis = Thesis::with([
                'student.user',
                'supervisor1.user',
                'supervisor2.user',
                'examiner1.user',
                'examiner2.user',
                'guidanceLogs.lecturer.user'
            ])->where('student_id', $student->id)->first();
        } elseif ($user->role === 'lecturer' && $lecturer) {
            $supervisingTheses = Thesis::with([
                'student.user',
                'supervisor1.user',
                'supervisor2.user',
                'guidanceLogs'
            ])
            ->where(function ($q) use ($lecturer) {
                $q->where('supervisor1_lecturer_id', $lecturer->id)
                  ->orWhere('supervisor2_lecturer_id', $lecturer->id)
                  ->orWhere('examiner1_lecturer_id', $lecturer->id)
                  ->orWhere('examiner2_lecturer_id', $lecturer->id);
            })
            ->orderByDesc('updated_at')
            ->get();
        } else {
            // Kaprodi / Dean / Superadmin view all
            $query = Thesis::with([
                'student.user',
                'student.studyProgram',
                'supervisor1.user',
                'supervisor2.user',
                'guidanceLogs'
            ]);

            if ($request->filled('status')) {
                $query->where('defense_status', $request->get('status'));
            }

            $allTheses = $query->orderByDesc('created_at')->get();
        }

        return view('thesis.index', compact(
            'myThesis',
            'supervisingTheses',
            'allTheses',
            'lecturers',
            'student',
            'lecturer'
        ));
    }

    /**
     * Student submits Thesis Proposal (Judul & Abstrak Skripsi).
     */
    public function storeProposal(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            abort(403, 'Hanya mahasiswa terdaftar yang dapat mengajukan proposal tugas akhir.');
        }

        // Prerequisite check: Min 110 SKS earned
        if (($student->total_sks_earned ?? 0) < 100 && $student->current_semester < 6) {
            return redirect()->back()->with('error', 'Persyaratan SKS belum terpenuhi. Minimal 100 SKS lulus untuk mengajukan proposal skripsi.');
        }

        // Check if thesis already exists
        if (Thesis::where('student_id', $student->id)->exists()) {
            return redirect()->back()->with('error', 'Anda telah memiliki pengajuan tugas akhir yang sedang berjalan.');
        }

        $validated = $request->validate([
            'title' => 'required|string|min:15|max:255',
            'abstract' => 'required|string|min:50|max:3000',
            'proposed_supervisor1_id' => 'required|exists:lecturers,id',
            'proposed_supervisor2_id' => 'nullable|exists:lecturers,id|different:proposed_supervisor1_id',
        ]);

        $thesis = Thesis::create([
            'id' => (string) Str::uuid(),
            'student_id' => $student->id,
            'title' => $validated['title'],
            'abstract' => $validated['abstract'],
            'supervisor1_lecturer_id' => $validated['proposed_supervisor1_id'],
            'supervisor2_lecturer_id' => $validated['proposed_supervisor2_id'] ?? null,
            'proposal_status' => 'pending',
            'defense_status' => 'not_ready',
        ]);

        $student->recordAudit(
            'THESIS_PROPOSAL_SUBMITTED',
            "Submitted thesis proposal [{$thesis->title}]."
        );

        return redirect()->route('thesis.index')->with('success', 'Proposal Tugas Akhir berhasil diajukan dan sedang menunggu verifikasi Kaprodi.');
    }

    /**
     * Kaprodi Approves Proposal & Officially Assigns Supervisors.
     */
    public function assignSupervisors(Request $request, Thesis $thesis): RedirectResponse
    {
        $validated = $request->validate([
            'supervisor1_lecturer_id' => 'required|exists:lecturers,id',
            'supervisor2_lecturer_id' => 'nullable|exists:lecturers,id|different:supervisor1_lecturer_id',
            'proposal_status' => 'required|in:approved,rejected',
        ]);

        $thesis->supervisor1_lecturer_id = $validated['supervisor1_lecturer_id'];
        $thesis->supervisor2_lecturer_id = $validated['supervisor2_lecturer_id'] ?? null;
        $thesis->proposal_status = $validated['proposal_status'];
        $thesis->save();

        $thesis->recordAudit(
            'THESIS_SUPERVISORS_ASSIGNED',
            "Proposal {$thesis->proposal_status}. Supervisor 1: {$thesis->supervisor1_lecturer_id}"
        );

        return redirect()->route('thesis.index')->with('success', 'Status proposal dan dosen pembimbing resmi diperbarui.');
    }

    /**
     * Student submits digital guidance logbook entry.
     */
    public function storeGuidanceLog(Request $request, Thesis $thesis): RedirectResponse
    {
        $user = Auth::user();
        $student = $user->student;

        if ($user->role === 'student' && (!$student || $thesis->student_id !== $student->id)) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'lecturer_id' => 'required|exists:lecturers,id',
            'guidance_date' => 'required|date|before_or_equal:today',
            'discussion_summary' => 'required|string|min:15|max:2000',
            'student_notes' => 'nullable|string|max:1000',
        ]);

        $log = ThesisGuidanceLog::create([
            'id' => (string) Str::uuid(),
            'thesis_id' => $thesis->id,
            'lecturer_id' => $validated['lecturer_id'],
            'guidance_date' => $validated['guidance_date'],
            'discussion_summary' => $validated['discussion_summary'],
            'student_notes' => $validated['student_notes'],
            'is_verified' => false,
        ]);

        $thesis->recordAudit(
            'THESIS_LOGBOOK_SUBMITTED',
            "Guidance log submitted for date {$log->guidance_date}"
        );

        return redirect()->route('thesis.index')->with('success', 'Catatan bimbingan berhasil dicatat ke logbook dan menunggu verifikasi dosen.');
    }

    /**
     * Lecturer verifies student guidance logbook entry.
     */
    public function verifyGuidanceLog(Request $request, ThesisGuidanceLog $log): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'supervisor_feedback' => 'nullable|string|max:1000',
            'is_verified' => 'required|boolean',
        ]);

        $log->is_verified = $validated['is_verified'];
        $log->verified_at = $validated['is_verified'] ? now() : null;
        $log->supervisor_feedback = $validated['supervisor_feedback'] ?? $log->supervisor_feedback;
        $log->save();

        $log->thesis->recordAudit(
            'THESIS_LOGBOOK_VERIFIED',
            "Logbook session #{$log->id} verified: " . ($log->is_verified ? 'Approved' : 'Rejected')
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Log bimbingan berhasil diverifikasi.',
                'verified_count' => $log->thesis->verifiedGuidanceCount(),
                'eligible_for_defense' => $log->thesis->isEligibleForDefense(),
            ]);
        }

        return redirect()->back()->with('success', 'Log bimbingan berhasil diverifikasi.');
    }

    /**
     * Kaprodi schedules Defense Exam (Sidang Tugas Akhir).
     */
    public function scheduleDefense(Request $request, Thesis $thesis): RedirectResponse
    {
        if (!$thesis->isEligibleForDefense()) {
            return redirect()->back()->with('error', 'Mahasiswa belum memenuhi syarat pendaftaran sidang (Minimal 8 bimbingan terverifikasi).');
        }

        $validated = $request->validate([
            'examiner1_lecturer_id' => 'required|exists:lecturers,id',
            'examiner2_lecturer_id' => 'required|exists:lecturers,id|different:examiner1_lecturer_id',
            'defense_date' => 'required|date|after:now',
        ]);

        $thesis->examiner1_lecturer_id = $validated['examiner1_lecturer_id'];
        $thesis->examiner2_lecturer_id = $validated['examiner2_lecturer_id'];
        $thesis->defense_date = $validated['defense_date'];
        $thesis->defense_status = 'scheduled';
        $thesis->save();

        $thesis->recordAudit(
            'DEFENSE_EXAM_SCHEDULED',
            "Defense exam scheduled for {$thesis->defense_date}"
        );

        return redirect()->route('thesis.index')->with('success', 'Jadwal sidang dan dewan penguji skripsi berhasil ditetapkan.');
    }

    /**
     * Defense Examiners Grade Student using Weighted Rubric.
     */
    public function gradeDefense(Request $request, Thesis $thesis): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'score_presentation' => 'required|numeric|min:0|max:100', // Bobot 25%
            'score_methodology' => 'required|numeric|min:0|max:100',  // Bobot 30%
            'score_mastery' => 'required|numeric|min:0|max:100',      // Bobot 35%
            'score_manuscript' => 'required|numeric|min:0|max:100',   // Bobot 10%
            'defense_result' => 'required|in:passed,revisions_needed,failed',
        ]);

        $finalScore = (
            ((float)$validated['score_presentation'] * 0.25) +
            ((float)$validated['score_methodology'] * 0.30) +
            ((float)$validated['score_mastery'] * 0.35) +
            ((float)$validated['score_manuscript'] * 0.10)
        );
        $finalScore = round($finalScore, 2);

        // Standard letter grade
        if ($finalScore >= 85) $letter = 'A';
        elseif ($finalScore >= 80) $letter = 'A-';
        elseif ($finalScore >= 75) $letter = 'B+';
        elseif ($finalScore >= 70) $letter = 'B';
        elseif ($finalScore >= 65) $letter = 'B-';
        elseif ($finalScore >= 60) $letter = 'C+';
        elseif ($finalScore >= 55) $letter = 'C';
        else $letter = 'E';

        $thesis->rubric_scores = [
            'presentation' => $validated['score_presentation'],
            'methodology' => $validated['score_methodology'],
            'mastery' => $validated['score_mastery'],
            'manuscript' => $validated['score_manuscript'],
        ];
        $thesis->final_score = $finalScore;
        $thesis->letter_grade = $letter;
        $thesis->defense_status = $validated['defense_result'];
        $thesis->save();

        $thesis->recordAudit(
            'DEFENSE_EXAM_GRADED',
            "Defense graded: {$finalScore} ({$letter}) - Status: {$thesis->defense_status}"
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Penilaian rubrik sidang skripsi berhasil disimpan.',
                'final_score' => $finalScore,
                'letter_grade' => $letter,
                'defense_status' => $thesis->defense_status,
            ]);
        }

        return redirect()->route('thesis.index')->with('success', 'Penilaian sidang skripsi berhasil disahkan.');
    }
}
