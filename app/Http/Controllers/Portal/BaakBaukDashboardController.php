<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\ClassSection;
use App\Models\PddiktiSyncLog;
use App\Models\Room;
use App\Models\Student;
use App\Models\StudentInvoice;
use App\Models\YudisiumClearance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * BaakBaukDashboardController
 *
 * Dedicated isolated operational dashboards for BAAK (Academic Administration)
 * and BAUK (Financial Administration).
 */
class BaakBaukDashboardController extends Controller
{
    /**
     * BAAK Dashboard: Academic Calendar, Timetable Clash Detection, PDDIKTI Sync & Yudisium.
     */
    public function baakDashboard(Request $request): View
    {
        $user = Auth::user();

        $activePeriod = Cache::remember('active_academic_period', 3600, function () {
            return AcademicPeriod::where('is_active', true)->first();
        });

        // Total active students university-wide
        $totalActiveStudents = Student::where('academic_status', 'active')->count();

        // Class sections in active period
        $totalSections = ClassSection::where('academic_period_id', $activePeriod?->id)->count();

        // Total rooms managed
        $totalRooms = Room::count();

        // PDDIKTI Sync Status
        $pddiktiLogs = PddiktiSyncLog::with('syncedByUser')->latest()->limit(5)->get();

        // Pending National PIN / Yudisium verifications
        $pendingClearances = YudisiumClearance::with(['student.user', 'student.studyProgram'])
            ->where('status', 'pending')
            ->limit(10)
            ->get();

        return view('portal.baak.dashboard', [
            'user' => $user,
            'activePeriod' => $activePeriod,
            'totalActiveStudents' => $totalActiveStudents,
            'totalSections' => $totalSections,
            'totalRooms' => $totalRooms,
            'pddiktiLogs' => $pddiktiLogs,
            'pendingClearances' => $pendingClearances,
        ]);
    }

    /**
     * BAUK Dashboard: Tuition Billing, Virtual Account Reconciliation & Financial Holds.
     */
    public function baukDashboard(Request $request): View
    {
        $user = Auth::user();

        $activePeriod = Cache::remember('active_academic_period', 3600, function () {
            return AcademicPeriod::where('is_active', true)->first();
        });

        // Invoice metrics
        $invoicesQuery = StudentInvoice::where('academic_period_id', $activePeriod?->id);
        
        $totalBilled = (clone $invoicesQuery)->sum('amount') ?? 0;
        $totalCollected = (clone $invoicesQuery)->where('payment_status', 'paid')->sum('paid_amount') ?? 0;
        $totalPending = (clone $invoicesQuery)->where('payment_status', 'pending')->sum('amount') ?? 0;

        $collectionRate = $totalBilled > 0 ? round(($totalCollected / $totalBilled) * 100, 1) : 0;

        // Students with Financial Holds (Overdue/Unpaid)
        $blockedStudents = Student::with(['user', 'studyProgram', 'invoices' => fn($q) => $q->where('payment_status', 'pending')])
            ->where(function ($q) {
                $q->where('is_financially_cleared', false)
                  ->orWhere('has_academic_hold', true);
            })
            ->limit(15)
            ->get();

        // Recent VA Transactions
        $recentTransactions = StudentInvoice::with(['student.user'])
            ->where('payment_status', 'paid')
            ->latest('paid_at')
            ->limit(8)
            ->get();

        return view('portal.bauk.dashboard', [
            'user' => $user,
            'activePeriod' => $activePeriod,
            'totalBilled' => $totalBilled,
            'totalCollected' => $totalCollected,
            'totalPending' => $totalPending,
            'collectionRate' => $collectionRate,
            'blockedStudents' => $blockedStudents,
            'recentTransactions' => $recentTransactions,
        ]);
    }

    /**
     * BAAK Master Academic Period Calendar & Room Timetable Management.
     */
    public function calendar(Request $request): View
    {
        $user = Auth::user();

        $activePeriod = Cache::remember('active_academic_period', 3600, function () {
            return AcademicPeriod::where('is_active', true)->first();
        });

        $allPeriods = AcademicPeriod::orderByDesc('start_date')->get();

        $rooms = Room::orderBy('code')->get();

        // Class sections in active period
        $classSections = ClassSection::with(['course', 'room', 'lecturer.user'])
            ->where('academic_period_id', $activePeriod?->id)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        // Group by day of week
        $sectionsByDay = $classSections->groupBy('day_of_week');

        return view('portal.baak.calendar', [
            'user' => $user,
            'activePeriod' => $activePeriod,
            'allPeriods' => $allPeriods,
            'rooms' => $rooms,
            'classSections' => $classSections,
            'sectionsByDay' => $sectionsByDay,
        ]);
    }

    /**
     * Update Academic Period Date Windows (KRS, Kuliah, Grading).
     */
    public function updatePeriodWindow(Request $request, AcademicPeriod $period)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'krs_start_date' => 'nullable|date',
            'krs_end_date' => 'nullable|date',
            'is_krs_active' => 'nullable|boolean',
            'is_grading_active' => 'nullable|boolean',
        ]);

        $period->update([
            'name' => $validated['name'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'krs_start_date' => $validated['krs_start_date'] ?? $period->krs_start_date,
            'krs_end_date' => $validated['krs_end_date'] ?? $period->krs_end_date,
            'is_krs_active' => $request->has('is_krs_active') ? (bool)$request->is_krs_active : $period->is_krs_active,
            'is_grading_active' => $request->has('is_grading_active') ? (bool)$request->is_grading_active : $period->is_grading_active,
        ]);

        Cache::forget('active_academic_period');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Kalender akademik berhasil diperbarui secara menyeluruh.',
                'period' => $period,
            ]);
        }

        return back()->with('success', 'Kalender akademik berhasil diperbarui.');
    }

    /**
     * Automated Semester Rollover & Mass Billing Wizard (BAAK & BAUK).
     */
    public function rollover(Request $request, \App\Services\SemesterRolloverService $rolloverService)
    {
        $validated = $request->validate([
            'from_period_id' => 'required|uuid|exists:academic_periods,id',
            'to_period_id' => 'required|uuid|exists:academic_periods,id|different:from_period_id',
        ]);

        $fromPeriod = AcademicPeriod::findOrFail($validated['from_period_id']);
        $toPeriod = AcademicPeriod::findOrFail($validated['to_period_id']);

        $summary = $rolloverService->rollover($fromPeriod, $toPeriod, Auth::user());

        Cache::forget('active_academic_period');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Transisi semester berhasil! {$summary['students_processed']} mahasiswa diproses, {$summary['invoices_generated']} tagihan diterbitkan.",
                'summary' => $summary,
            ]);
        }

        return redirect()->route('portal.baak.calendar')
            ->with('success', "Transisi semester ke {$summary['to_period']} berhasil! {$summary['students_processed']} mahasiswa diproses dan arsip AKM disimpan.");
    }
}
