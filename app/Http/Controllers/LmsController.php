<?php

namespace App\Http\Controllers;

use App\Models\ClassSection;
use App\Models\LectureSession;
use App\Models\LmsAssignment;
use App\Models\LmsSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class LmsController extends Controller
{
    /**
     * Show 16-Week Course LMS Session Timeline & RPS Syllabus.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $classSectionId = $request->query('class_section_id');

        $classSection = null;
        if ($classSectionId) {
            $classSection = ClassSection::with(['course.curriculum', 'lecturer.user', 'room', 'lectureSessions.assignments', 'lectureSessions.room'])
                ->findOrFail($classSectionId);
        } else {
            // Find first section relevant to user
            if ($user->role === 'student' && $user->student) {
                $classSection = ClassSection::whereHas('krsItems.krsSubmission', function ($q) use ($user) {
                    $q->where('student_id', $user->student->id)->where('status', 'approved');
                })->with(['course.curriculum', 'lecturer.user', 'room', 'lectureSessions.assignments', 'lectureSessions.room'])->first();
            } else {
                $classSection = ClassSection::with(['course.curriculum', 'lecturer.user', 'room', 'lectureSessions.assignments', 'lectureSessions.room'])->first();
            }
        }

        $allSections = ClassSection::with('course')->get();

        return view('lms.index', compact('classSection', 'allSections'));
    }

    /**
     * Show Specific Lecture Session Details & Assignment Hub.
     */
    public function showSession(LectureSession $session): View
    {
        $session->load([
            'classSection.course',
            'classSection.lecturer.user',
            'room',
            'assignments.submissions.student.user'
        ]);

        $user = Auth::user();
        $studentSubmission = null;
        if ($user->role === 'student' && $user->student) {
            $assignmentIds = $session->assignments->pluck('id');
            $studentSubmission = LmsSubmission::whereIn('lms_assignment_id', $assignmentIds)
                ->where('student_id', $user->student->id)
                ->first();
        }

        return view('lms.session', compact('session', 'studentSubmission'));
    }

    /**
     * Lecturer Creates New Assignment for a Session.
     */
    public function storeAssignment(Request $request, LectureSession $session): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'required|string',
            'deadline' => 'required|date',
            'max_score' => 'required|integer|min:10|max:100',
            'attachment' => 'nullable|file|mimes:pdf,ppt,pptx,doc,docx,zip|max:10240',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('assignments', 'public');
        }

        $assignment = $session->assignments()->create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'deadline' => $validated['deadline'],
            'max_score' => $validated['max_score'],
            'attachment_path' => $attachmentPath,
        ]);

        $assignment->recordAudit('LMS_ASSIGNMENT_CREATED', "Assignment [{$assignment->title}] created for session #{$session->session_number}");

        // Notify all enrolled students in this class section
        $studentUsers = \App\Models\User::whereHas('student.krsSubmissions', function ($q) use ($session) {
            $q->whereHas('items', function ($itemQ) use ($session) {
                $itemQ->where('class_section_id', $session->class_section_id);
            });
        })->get();

        \App\Services\NotificationService::notifyMany(
            $studentUsers,
            'assignment_published',
            "Tugas Kuliah Baru: {$assignment->title}",
            "Dosen telah menerbitkan tugas perkuliahan baru pada sesi #{$session->session_number}. Silakan unduh lampiran dan kumpulkan sebelum deadline.",
            "/portal/student/lms/session/{$session->id}"
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Tugas kuliah berhasil dipublikasikan.', 'assignment' => $assignment]);
        }

        return back()->with('success', 'Tugas kuliah berhasil diterbitkan.');
    }

    /**
     * Student Submits Assignment File.
     */
    public function submitAssignment(Request $request, LmsAssignment $assignment): RedirectResponse|JsonResponse
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            abort(403, 'Akses terbatas untuk mahasiswa.');
        }

        if ($assignment->isPastDeadline()) {
            $msg = 'Batas waktu pengumpulan tugas telah berakhir (Deadline terlewati).';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        $validated = $request->validate([
            'submission_file' => 'required|file|mimes:pdf,zip,rar,doc,docx|max:10240', // 10 MB max
        ]);

        $file = $request->file('submission_file');
        $fileName = 'submission_' . $student->nim . '_' . time() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('submissions', $fileName, 'public');

        $submission = LmsSubmission::updateOrCreate(
            ['lms_assignment_id' => $assignment->id, 'student_id' => $student->id],
            [
                'submission_file_path' => $filePath,
                'submitted_at' => now(),
            ]
        );

        $student->recordAudit('LMS_ASSIGNMENT_SUBMITTED', "Assignment [{$assignment->title}] submitted");

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tugas berhasil diunggah tepat waktu.',
                'submission' => $submission
            ]);
        }

        return back()->with('success', 'Tugas berhasil dikumpulkan tepat waktu.');
    }

    /**
     * Lecturer Grades Student Assignment Submission.
     */
    public function gradeSubmission(Request $request, LmsSubmission $submission): JsonResponse
    {
        $validated = $request->validate([
            'score' => 'required|numeric|min:0|max:100',
            'feedback' => 'nullable|string',
        ]);

        $user = Auth::user();
        $lecturer = $user->lecturer;

        $submission->score = $validated['score'];
        $submission->feedback = $validated['feedback'] ?? null;
        $submission->graded_by_lecturer_id = $lecturer ? $lecturer->id : null;
        $submission->graded_at = now();
        $submission->save();

        $submission->recordAudit('LMS_SUBMISSION_GRADED', "Submission graded: {$validated['score']}/100");

        return response()->json([
            'success' => true,
            'message' => 'Nilai dan feedback tugas berhasil disimpan.',
            'score' => $submission->score,
        ]);
    }

    /**
     * Alias for showSession to match route name sessionDetail.
     */
    public function sessionDetail(LectureSession $lectureSession): View
    {
        return $this->showSession($lectureSession);
    }

    /**
     * Store or update lecture material.
     */
    public function storeMaterial(Request $request, LectureSession $lectureSession): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'material_type' => 'nullable|string',
            'file_url' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $lectureSession->update([
            'notes' => $validated['notes'] ?? $lectureSession->notes,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Materi perkuliahan berhasil disimpan.']);
        }

        return back()->with('success', 'Materi perkuliahan berhasil diperbarui.');
    }
}
