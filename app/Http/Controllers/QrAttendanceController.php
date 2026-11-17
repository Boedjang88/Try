<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassSection;
use App\Models\KrsItem;
use App\Models\LectureSession;
use App\Models\Room;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class QrAttendanceController extends Controller
{
    /**
     * Lecturer Lecture Session & Rotating QR Command Center.
     */
    public function sessionDashboard(LectureSession $lectureSession): View
    {
        $session = $lectureSession;
        $session->load([
            'classSection.course',
            'classSection.room',
            'room',
            'classSection.krsItems.krsSubmission.student.user',
            'attendances.student.user'
        ]);

        $enrolledStudents = $session->classSection->krsItems->map(fn($item) => $item->krsSubmission->student);
        $attendanceMap = $session->attendances->keyBy('student_id');

        return view('attendance.lecturer-session', compact('session', 'enrolledStudents', 'attendanceMap'));
    }

    /**
     * Generate 15-Second Rotating Signed QR Token (Polling Endpoint for Lecturer).
     */
    public function generateQrToken(LectureSession $lectureSession): JsonResponse
    {
        $session = $lectureSession;
        $appKey = config('app.key') ?: 'UPH-ENTERPRISE-KEY-2026';
        $timestamp = microtime(true);
        $salt = Str::random(12);

        // Cryptographically signed dynamic payload
        $token = hash_hmac('sha256', "{$session->id}|{$timestamp}|{$salt}", $appKey);

        $session->qr_code_token = $token;
        // 15 seconds validity + 3 seconds network leeway
        $session->qr_expires_at = now()->addSeconds(18);
        $session->save();

        return response()->json([
            'success' => true,
            'token' => $token,
            'expires_in' => 15,
            'expires_at' => $session->qr_expires_at->toIso8601String(),
        ]);
    }

    /**
     * Real-time Roster Polling for Lecturer Screen.
     */
    public function liveRoster(LectureSession $lectureSession): JsonResponse
    {
        $session = $lectureSession;
        $attendances = Attendance::where('lecture_session_id', $session->id)
            ->with('student.user')
            ->orderBy('scanned_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'present_count' => $attendances->where('attendance_status', 'present')->count(),
            'attendances' => $attendances->map(fn($a) => [
                'student_id' => $a->student_id,
                'nim' => $a->student->nim,
                'name' => $a->student->user->full_name,
                'status' => $a->attendance_status,
                'method' => $a->verified_method,
                'scanned_at' => $a->scanned_at ? $a->scanned_at->format('H:i:s') : '-',
            ]),
        ]);
    }

    /**
     * Submit Digital BAP (Berita Acara Perkuliahan) and Sign Attendance Ledger.
     */
    public function submitBap(Request $request, LectureSession $lectureSession): JsonResponse|RedirectResponse
    {
        $session = $lectureSession;
        $validated = $request->validate([
            'bap_summary' => 'required|string|min:10',
            'actual_date' => 'required|date',
        ]);

        return DB::transaction(function () use ($validated, $session, $request) {
            $session->bap_summary = $validated['bap_summary'];
            $session->actual_date = $validated['actual_date'];
            $session->lecturer_signed_at = now();
            $session->save();

            // Auto-mark unenrolled students as absent
            $enrolledStudentIds = KrsItem::where('class_section_id', $session->class_section_id)
                ->whereHas('krsSubmission', fn($q) => $q->where('status', 'approved'))
                ->with('krsSubmission')
                ->get()
                ->pluck('krsSubmission.student_id');

            $existingAttendanceStudentIds = Attendance::where('lecture_session_id', $session->id)->pluck('student_id')->toArray();

            foreach ($enrolledStudentIds as $stdId) {
                if (!in_array($stdId, $existingAttendanceStudentIds, true)) {
                    Attendance::create([
                        'lecture_session_id' => $session->id,
                        'student_id' => $stdId,
                        'attendance_status' => 'absent',
                        'verified_method' => 'lecturer_manual',
                        'notes' => 'Otomatis tidak hadir saat BAP disahkan.',
                    ]);
                }
            }

            $session->recordAudit('BAP_SIGNED', "Berita Acara Perkuliahan Session #{$session->session_number} signed by Lecturer");

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'BAP Perkuliahan berhasil disahkan dan presensi dikunci.',
                ]);
            }

            return back()->with('success', 'Berita Acara Perkuliahan berhasil ditandatangani dan presensi ditutup.');
        });
    }

    /**
     * Lecturer Manual Attendance Override.
     */
    public function manualVerify(Request $request, LectureSession $lectureSession): JsonResponse
    {
        $session = $lectureSession;
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'attendance_status' => 'required|in:present,absent,late,excused',
            'notes' => 'nullable|string|max:255',
        ]);

        $attendance = Attendance::updateOrCreate(
            ['lecture_session_id' => $session->id, 'student_id' => $validated['student_id']],
            [
                'attendance_status' => $validated['attendance_status'],
                'verified_method' => 'lecturer_manual',
                'notes' => $validated['notes'] ?? 'Divalidasi manual oleh dosen.',
                'scanned_at' => now(),
            ]
        );

        $attendance->recordAudit('ATTENDANCE_MANUAL_OVERRIDE', "Status updated to {$validated['attendance_status']}");

        return response()->json([
            'success' => true,
            'message' => 'Presensi mahasiswa berhasil diperbarui manual.',
            'attendance' => $attendance,
        ]);
    }

    /**
     * Student Mobile-First Attendance Scan Page.
     */
    public function scanPage(): View
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            abort(403, 'Akses terbatas untuk mahasiswa.');
        }

        // Today's scheduled sessions for this student
        $todayDayOfWeek = (int)date('N'); // 1 = Monday, 7 = Sunday
        $activeSessions = LectureSession::whereHas('classSection', function ($q) use ($student, $todayDayOfWeek) {
            $q->where('day_of_week', $todayDayOfWeek)
              ->whereHas('krsItems.krsSubmission', fn($sub) => $sub->where('student_id', $student->id)->where('status', 'approved'));
        })
        ->whereDate('planned_date', now()->toDateString())
        ->with(['classSection.course', 'classSection.room', 'room'])
        ->get();

        return view('attendance.student-scan', compact('student', 'activeSessions'));
    }

    /**
     * Verify QR Scan with 15-Second Token & Haversine Geolocation Boundary Check.
     */
    public function verifyScan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'qr_token' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Akun bukan mahasiswa.'], 403);
        }

        // 1. Find Lecture Session matching the token
        $session = LectureSession::where('qr_code_token', $validated['qr_token'])
            ->with(['classSection.course', 'classSection.room', 'room'])
            ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'code' => 'INVALID_TOKEN',
                'message' => 'QR Code tidak valid atau telah diperbarui. Silakan scan ulang QR Code terbaru dosen.',
            ], 422);
        }

        // 2. Validate 15-second expiration
        if (!$session->qr_expires_at || now()->greaterThan($session->qr_expires_at)) {
            return response()->json([
                'success' => false,
                'code' => 'QR_EXPIRED',
                'message' => 'QR Code telah kedaluwarsa (Masa berlaku 15 detik habis). Mohon scan QR Code yang sedang tampil di layar dosen.',
            ], 422);
        }

        // 3. Verify Student is Enrolled in this Class Section
        $isEnrolled = KrsItem::where('class_section_id', $session->class_section_id)
            ->whereHas('krsSubmission', fn($q) => $q->where('student_id', $student->id)->where('status', 'approved'))
            ->exists();

        if (!$isEnrolled) {
            return response()->json([
                'success' => false,
                'code' => 'NOT_ENROLLED',
                'message' => 'Presensi ditolak: Anda tidak terdaftar sebagai peserta aktif di kelas kuliah ini.',
            ], 403);
        }

        // 4. Geolocation Haversine Geofence Boundary Check
        $targetRoom = $session->room ?: $session->classSection->room;
        $distanceMeters = null;

        if ($targetRoom && $targetRoom->latitude && $targetRoom->longitude) {
            $distanceMeters = round($targetRoom->distanceTo((float)$validated['latitude'], (float)$validated['longitude']), 1);
            $maxAllowedRadius = $targetRoom->geofence_radius_meters ?: 50;

            if ($distanceMeters > $maxAllowedRadius) {
                return response()->json([
                    'success' => false,
                    'code' => 'GEOFENCE_VIOLATION',
                    'distance' => $distanceMeters,
                    'max_radius' => $maxAllowedRadius,
                    'message' => "Presensi ditolak: Posisi GPS Anda terdeteksi berada di luar jangkauan ruang kelas ({$distanceMeters}m dari ruang {$targetRoom->code}, maksimal radius {$maxAllowedRadius}m).",
                ], 422);
            }
        }

        // 5. Persist Verified Attendance
        $attendance = Attendance::updateOrCreate(
            ['lecture_session_id' => $session->id, 'student_id' => $student->id],
            [
                'attendance_status' => 'present',
                'verified_method' => 'qr_scan',
                'scanned_latitude' => $validated['latitude'],
                'scanned_longitude' => $validated['longitude'],
                'scanned_at' => now(),
                'notes' => $distanceMeters !== null ? "Presensi QR Valid (Jarak GPS: {$distanceMeters}m)" : "Presensi QR Valid",
            ]
        );

        $student->recordAudit(
            'ATTENDANCE_CHECKED_IN',
            "QR Attendance verified for session #{$session->session_number} ({$session->classSection->course->code})"
        );

        return response()->json([
            'success' => true,
            'message' => "Presensi berhasil dicatat! Anda terverifikasi hadir di kelas {$session->classSection->course->name_id} (Ruang: " . ($targetRoom->code ?? 'UPH') . ").",
            'distance' => $distanceMeters,
            'scanned_at' => $attendance->scanned_at->format('H:i:s'),
            'session_title' => $session->title,
        ]);
    }
}
