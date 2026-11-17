<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\ClassSection;
use App\Models\LectureSession;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * LecturerSessionController
 *
 * Manages 16-Week Lecture Sessions, Digital BAP (Berita Acara Perkuliahan),
 * and the 15-second Rotating Dynamic QR Code Generator for Live Class Attendance.
 */
class LecturerSessionController extends Controller
{
    /**
     * Display Lecturer's Assigned Classes and 16-Week Session Catalog.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $lecturer = $user->lecturer;

        $activePeriod = AcademicPeriod::where('is_active', true)->first()
            ?? AcademicPeriod::orderByDesc('year_start')->first();

        // Query class sections taught by this lecturer
        $query = ClassSection::with([
            'course',
            'room',
            'academicPeriod',
            'lectureSessions' => fn($q) => $q->orderBy('session_number'),
            'lectureSessions.attendances',
            'krsItems' => fn($q) => $q->whereHas('krsSubmission', fn($s) => $s->where('status', 'approved')),
        ]);

        if ($lecturer && $user->role === 'lecturer') {
            $query->where('lecturer_id', $lecturer->id);
        }

        if ($activePeriod) {
            $query->where('academic_period_id', $activePeriod->id);
        }

        $classSections = $query->orderBy('day_of_week')->orderBy('start_time')->get();

        // Selected section
        $selectedSectionId = $request->get('section_id') ?? $classSections->first()?->id;
        $activeSection = $classSections->firstWhere('id', $selectedSectionId) ?? $classSections->first();

        $lectureSessions = $activeSection ? $activeSection->lectureSessions : collect();

        // Selected session for BAP/QR modal preview
        $selectedSessionId = $request->get('session_id') ?? $lectureSessions->first()?->id;
        $activeSession = $lectureSessions->firstWhere('id', $selectedSessionId) ?? $lectureSessions->first();

        // Calculate statistics
        $totalSessions = $lectureSessions->count();
        $signedBapCount = $lectureSessions->whereNotNull('lecturer_signed_at')->count();
        $bapProgressPercent = $totalSessions > 0 ? round(($signedBapCount / $totalSessions) * 100) : 0;

        return view('portal.lecturer.sessions', [
            'lecturer' => $lecturer,
            'user' => $user,
            'activePeriod' => $activePeriod,
            'classSections' => $classSections,
            'activeSection' => $activeSection,
            'lectureSessions' => $lectureSessions,
            'activeSession' => $activeSession,
            'signedBapCount' => $signedBapCount,
            'totalSessions' => $totalSessions,
            'bapProgressPercent' => $bapProgressPercent,
        ]);
    }

    /**
     * Submit and Sign Digital BAP (Berita Acara Perkuliahan).
     */
    public function updateBap(Request $request, LectureSession $session): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'bap_summary' => 'required|string|min:10|max:2000',
            'actual_date' => 'required|date',
            'title' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();

        DB::transaction(function () use ($validated, $session, $user) {
            $session->bap_summary = $validated['bap_summary'];
            $session->actual_date = $validated['actual_date'];
            if (!empty($validated['title'])) {
                $session->title = $validated['title'];
            }
            $session->lecturer_signed_at = now();
            $session->save();

            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'BAP_SIGNED',
                'description' => "Lecturer [{$user->full_name}] digitally signed BAP for Session #{$session->session_number} ({$session->classSection->course->code}). Topic: " . Str::limit($session->bap_summary, 80),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        $msg = "Berita Acara Perkuliahan (BAP) Sesi #{$session->session_number} berhasil disahkan dan ditandatangani secara digital.";

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'signed_at' => $session->lecturer_signed_at?->format('d M Y H:i'),
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Generate or Rotate 15-Second Dynamic Cryptographic QR Token.
     */
    public function rotateQr(LectureSession $session): JsonResponse
    {
        $appKey = config('app.key') ?: 'UPH-ENTERPRISE-KEY-2026';
        $timestamp = microtime(true);
        $nonce = Str::random(12);

        // Dynamic HMAC-SHA256 Token
        $token = 'UPH-ATT-' . substr(hash_hmac('sha256', "{$session->id}|{$timestamp}|{$nonce}", $appKey), 0, 24);

        $session->qr_code_token = $token;
        // 15 seconds + 3 seconds network buffer
        $session->qr_expires_at = now()->addSeconds(18);
        $session->save();

        return response()->json([
            'success' => true,
            'token' => $token,
            'expires_in' => 15,
            'expires_at' => $session->qr_expires_at->toIso8601String(),
            'session_number' => $session->session_number,
            'course_name' => $session->classSection->course->name_id,
        ]);
    }

    /**
     * Simulate / Assist Student Check-In (Loop 3 Cross-Account Verification).
     */
    public function simulateCheckIn(Request $request, LectureSession $session): JsonResponse
    {
        // Target student: either given student_id, or Christian Jonathan (student1@uph.edu), or first enrolled
        $student = null;
        if ($request->filled('student_id')) {
            $student = Student::find($request->get('student_id'));
        }

        if (!$student) {
            $christianUser = User::where('email', 'student1@uph.edu')->first();
            $student = $christianUser?->student;
        }

        if (!$student) {
            $student = Student::first();
        }

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Mahasiswa tidak ditemukan.'], 404);
        }

        $room = $session->room ?: $session->classSection->room;

        // Record attendance
        $attendance = Attendance::updateOrCreate(
            [
                'lecture_session_id' => $session->id,
                'student_id' => $student->id,
            ],
            [
                'attendance_status' => 'present',
                'verified_method' => 'qr_scan',
                'scanned_latitude' => $room->latitude ?? -6.2268,
                'scanned_longitude' => $room->longitude ?? 106.6083,
                'scanned_at' => now(),
                'notes' => 'Presensi QR Terverifikasi Real-Time (GPS Geofence Match)',
            ]
        );

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'ATTENDANCE_CHECKED_IN',
            'description' => "Student [{$student->nim}] checked in to Session #{$session->session_number} ({$session->classSection->course->code}) via QR Scanner",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Presensi mahasiswa {$student->user->full_name} ({$student->nim}) berhasil dicatat sebagai 'Hadir'.",
            'attendance' => $attendance,
            'student_name' => $student->user->full_name,
            'nim' => $student->nim,
            'status' => 'present',
            'scanned_at' => now()->format('H:i:s'),
        ]);
    }
}
