<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\AuditLog;
use App\Models\ClassSection;
use App\Models\FacilityBooking;
use App\Models\KrsItem;
use App\Models\KrsSubmission;
use App\Models\Room;
use App\Models\Student;
use App\Services\NotificationService;
use App\Services\OutboundNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CampusRoomOperationsController extends Controller
{
    /**
     * Display Campus Building & Classroom Live Occupancy Grid.
     */
    public function occupancy(Request $request): View
    {
        $user = Auth::user();

        // Determine target day of week and time (supports simulation parameter)
        $dayOfWeek = (int)$request->input('sim_day', now()->dayOfWeekIso); // 1 = Senin ... 7 = Minggu
        $currentTime = $request->input('sim_time', now()->format('H:i'));

        $query = Room::query();

        if ($request->filled('building')) {
            $query->where('building', $request->input('building'));
        }

        if ($request->filled('room_type')) {
            $query->where('room_type', $request->input('room_type'));
        }

        $rooms = $query->orderBy('building')->orderBy('code')->get();

        // Eager load active class sections and bookings
        $activeSections = ClassSection::with(['course', 'lecturer.user'])
            ->where('day_of_week', $dayOfWeek)
            ->where('start_time', '<=', $currentTime)
            ->where('end_time', '>=', $currentTime)
            ->where('is_closed', false)
            ->get()
            ->keyBy('room_id');

        $activeBookings = FacilityBooking::with('user')
            ->where('status', '!=', 'rejected')
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->get();

        $occupancyMatrix = $rooms->map(function ($room) use ($activeSections, $activeBookings) {
            $currentSection = $activeSections->get($room->id);

            if ($currentSection) {
                return [
                    'room' => $room,
                    'status' => 'IN_LECTURE',
                    'badge_class' => 'bg-red-500/20 text-red-300 border-red-500/30',
                    'label' => 'Sedang Kuliah',
                    'course_code' => $currentSection->course->code ?? 'N/A',
                    'course_name' => $currentSection->course->name_id ?? 'N/A',
                    'lecturer_name' => $currentSection->lecturer->user->full_name ?? 'Dosen Pengampu',
                    'enrolled_count' => $currentSection->enrolled_count,
                    'time_window' => substr($currentSection->start_time, 0, 5) . ' - ' . substr($currentSection->end_time, 0, 5),
                ];
            }

            // Check facility booking
            $currentBooking = $activeBookings->first(fn($b) => str_contains(strtolower($b->facility_name), strtolower($room->code)));
            if ($currentBooking) {
                return [
                    'room' => $room,
                    'status' => 'BOOKED_LAB_THESIS',
                    'badge_class' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                    'label' => 'Reservasi Lab/Skripsi',
                    'course_code' => 'RESERVASI',
                    'course_name' => $currentBooking->purpose ?? 'Praktikum / Riset',
                    'lecturer_name' => $currentBooking->user->full_name ?? 'Civitas Akademika',
                    'enrolled_count' => 1,
                    'time_window' => $currentBooking->start_time->format('H:i') . ' - ' . $currentBooking->end_time->format('H:i'),
                ];
            }

            return [
                'room' => $room,
                'status' => 'AVAILABLE',
                'badge_class' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
                'label' => 'Tersedia (Kosong)',
                'course_code' => 'TERSEDIA',
                'course_name' => 'Ruang Siap Digunakan',
                'lecturer_name' => 'Kapasitas: ' . $room->capacity . ' Kursi',
                'enrolled_count' => 0,
                'time_window' => 'Bebas Jadwal',
            ];
        });

        $buildings = Room::select('building')->distinct()->pluck('building');
        $roomTypes = Room::select('room_type')->distinct()->pluck('room_type');

        return view('portal.baak.rooms-occupancy', [
            'occupancyMatrix' => $occupancyMatrix,
            'buildings' => $buildings,
            'roomTypes' => $roomTypes,
            'dayOfWeek' => $dayOfWeek,
            'currentTime' => $currentTime,
            'user' => $user,
        ]);
    }

    /**
     * Anti-Cheating Randomized Exam Seat Generator (UTS / UAS).
     *
     * Assigns unique randomized seat coordinates only to financially cleared
     * and attendance-eligible (>= 75%) students in the active academic period.
     */
    public function generateExamSeats(Request $request): RedirectResponse|JsonResponse
    {
        $user = Auth::user();
        $role = $user->primary_role ?? $user->role;

        if (!in_array($role, ['superadmin', 'baak'])) {
            abort(403, 'Hanya BAAK dan Superadmin yang berwenang men-generate nomor kursi ujian.');
        }

        $activePeriod = AcademicPeriod::where('is_active', true)->first()
            ?? AcademicPeriod::latest()->first();

        $rows = ['A', 'B', 'C', 'D', 'E', 'F'];
        $assignedCount = 0;
        $blockedCount = 0;

        DB::transaction(function () use ($activePeriod, $rows, &$assignedCount, &$blockedCount, $user, $request) {
            // Group krs_items by class section to generate clean classroom seat distribution
            $classSections = ClassSection::where('academic_period_id', $activePeriod?->id)
                ->with(['krsItems.krsSubmission.student'])
                ->get();

            if ($classSections->isEmpty()) {
                $classSections = ClassSection::with(['krsItems.krsSubmission.student'])->get();
            }

            foreach ($classSections as $section) {
                $seatNumberCounter = 1;
                $rowIndex = 0;

                // Deterministically randomize student sequence to prevent adjacent collusion
                $items = $section->krsItems->shuffle();

                foreach ($items as $item) {
                    $student = $item->krsSubmission?->student;

                    $attendancePct = (float)($item->attendance_percentage ?? 100.0);
                    $isAttendanceEligible = ($attendancePct >= 75.0) || !empty($item->exam_unblocked_reason);
                    $isFinanciallyCleared = $student ? $student->is_financially_cleared : true;

                    if ($isAttendanceEligible && $isFinanciallyCleared) {
                        $currentCol = str_pad($seatNumberCounter, 2, '0', STR_PAD_LEFT);
                        $currentRow = $rows[$rowIndex % count($rows)];
                        $seatCode = "ROW-{$currentRow} / SEAT-{$currentCol}";

                        $item->exam_seat_number = $seatCode;
                        $item->is_exam_blocked = false;
                        $item->save();

                        $seatNumberCounter++;
                        if ($seatNumberCounter > 10) {
                            $seatNumberCounter = 1;
                            $rowIndex++;
                        }
                        $assignedCount++;
                    } else {
                        $reason = !$isAttendanceEligible ? 'ATTENDANCE < 75%' : 'FINANCIAL HOLD';
                        $item->exam_seat_number = "BLOCKED ({$reason})";
                        $item->is_exam_blocked = true;
                        $item->save();
                        $blockedCount++;
                    }
                }
            }

            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'EXAM_SEATS_GENERATED',
                'auditable_type' => ClassSection::class,
                'auditable_id' => 'ALL_SECTIONS',
                'new_values' => ['assigned_seats' => $assignedCount, 'blocked_students' => $blockedCount],
                'reason' => "BAAK generated randomized anti-cheating exam seats for active academic period.",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);

            // Outbound notification broadcast
            OutboundNotificationService::dispatchWhatsApp(
                '081122334455',
                'Nomor Kursi Ujian Resmi Diterbitkan',
                "BAAK telah menerbitkan Nomor Kursi Ujian (Anti-Cheating) untuk UTS/UAS Semester Aktif. Silakan cek Kartu Ujian Resmi di portal.",
                'EXAM_SEAT_ASSIGNED'
            );
        });

        $msg = "Nomor kursi ujian acak berhasil diterbitkan: {$assignedCount} mahasiswa dialokasikan, {$blockedCount} mahasiswa tercekal.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'data' => [
                    'assigned_count' => $assignedCount,
                    'blocked_count' => $blockedCount,
                ],
            ]);
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Display Official Student Exam Card (Kartu Ujian Resmi) with allocated seat numbers.
     */
    public function examCard(Request $request): View
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student && $user->role === 'superadmin') {
            $student = Student::first();
        } elseif (!$student) {
            abort(403, 'Akses Ditolak: Profil mahasiswa tidak ditemukan.');
        }

        $activePeriod = AcademicPeriod::where('is_active', true)->first()
            ?? AcademicPeriod::latest()->first();

        $krsSubmission = KrsSubmission::with([
            'academicPeriod',
            'items.classSection.course',
            'items.classSection.room',
            'items.classSection.lecturer.user'
        ])
            ->where('student_id', $student->id)
            ->where('academic_period_id', $activePeriod?->id)
            ->first();

        if (!$krsSubmission) {
            $krsSubmission = KrsSubmission::with([
                'academicPeriod',
                'items.classSection.course',
                'items.classSection.room',
                'items.classSection.lecturer.user'
            ])
                ->where('student_id', $student->id)
                ->latest()
                ->first();
        }

        return view('portal.student.exam-card', [
            'user' => $user,
            'student' => $student,
            'activePeriod' => $activePeriod,
            'krsSubmission' => $krsSubmission,
        ]);
    }
}
