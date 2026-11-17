<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * StudentDashboardController
 *
 * Strictly scoped to the authenticated student account. Displays personal
 * academic standing, KRS cart, today's schedule, invoices, and financial holds.
 */
class StudentDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Strict Account Scoping: Query only the student's own record with eager loading
        $student = Student::with([
            'studyProgram.faculty',
            'academicAdvisor.user',
            'invoices' => fn($q) => $q->latest()->limit(5),
            'krsSubmissions' => fn($q) => $q->with([
                'academicPeriod',
                'items.classSection.course',
                'items.classSection.room',
                'items.classSection.lecturer.user'
            ])->latest(),
            'thesis.supervisor1.user',
            'thesis.guidanceLogs' => fn($q) => $q->latest()->limit(5),
        ])->where('user_id', $user->id)->first();

        // Fallback only for superadmin administrative review
        if (!$student && $user->role === 'superadmin') {
            $student = Student::first();
        } elseif (!$student) {
            abort(403, 'Profil mahasiswa tidak ditemukan untuk akun ini.');
        }

        // Active academic period (Cached)
        $activePeriod = Cache::remember('active_academic_period', 3600, function () {
            return AcademicPeriod::where('is_active', true)->first();
        });

        // Current KRS submission for this active period
        $activeKrs = $student ? $student->krsSubmissions->firstWhere('academic_period_id', $activePeriod?->id) : null;
        if (!$activeKrs && $student) {
            $activeKrs = $student->krsSubmissions->first();
        }

        // Extract class schedule items
        $enrolledClasses = $activeKrs ? $activeKrs->items : collect();

        // Identify day of week in Indonesian
        $daysIndo = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];
        $currentDayName = $daysIndo[now()->format('l')] ?? 'Senin';

        // Filter schedule for today
        $todayClasses = $enrolledClasses->filter(function ($item) use ($currentDayName) {
            return $item->classSection && $item->classSection->day_of_week === $currentDayName;
        });

        // Financial status & pending invoices
        $pendingInvoice = $student ? $student->invoices->first(fn($inv) => in_array($inv->payment_status, ['pending', 'unpaid'])) : null;
        $isFinanciallyBlocked = $student ? (!$student->is_financially_cleared || $student->has_academic_hold) : false;

        // EWS status
        $ews = $student ? $student->ews_status : ['status' => 'NORMAL', 'warnings' => []];

        return view('portal.student.dashboard', [
            'user' => $user,
            'student' => $student,
            'activePeriod' => $activePeriod,
            'activeKrs' => $activeKrs,
            'enrolledClasses' => $enrolledClasses,
            'todayClasses' => $todayClasses,
            'pendingInvoice' => $pendingInvoice,
            'isFinanciallyBlocked' => $isFinanciallyBlocked,
            'ews' => $ews,
            'currentDayName' => $currentDayName,
        ]);
    }
}
