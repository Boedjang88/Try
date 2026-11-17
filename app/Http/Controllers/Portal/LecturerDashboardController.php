<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\ClassSection;
use App\Models\KrsSubmission;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\ThesisGuidanceLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * LecturerDashboardController
 *
 * Strictly scoped to the authenticated lecturer account. Shows only assigned classes,
 * today's sessions with instant QR attendance generator, grading sheet completion,
 * advisees awaiting KRS approvals, and pending thesis guidance logs.
 */
class LecturerDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Strict Account Scoping: Query only the lecturer's own record with eager loading
        $lecturer = Lecturer::with([
            'studyProgram.faculty',
            'classSections' => fn($q) => $q->with(['course', 'room', 'krsItems']),
            'advisees' => fn($q) => $q->with(['user', 'krsSubmissions' => fn($sub) => $sub->latest()]),
            'thesesAsSupervisor1' => fn($q) => $q->with(['student.user', 'guidanceLogs']),
        ])->where('user_id', $user->id)->first();

        // Fallback only for superadmin administrative review
        if (!$lecturer && $user->role === 'superadmin') {
            $lecturer = Lecturer::first();
        } elseif (!$lecturer) {
            abort(403, 'Profil dosen tidak ditemukan untuk akun ini.');
        }

        // Active Academic Period (Cached)
        $activePeriod = Cache::remember('active_academic_period', 3600, function () {
            return AcademicPeriod::where('is_active', true)->first();
        });

        $classSections = $lecturer ? $lecturer->classSections : collect();

        // Current day in Indonesian
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

        // Classes taught today
        $todayTeaching = $classSections->filter(function ($section) use ($currentDayName) {
            return $section->day_of_week === $currentDayName;
        });

        // Advisees awaiting KRS approval
        $pendingKrsAdvisees = KrsSubmission::with(['student.user'])
            ->whereHas('student', function ($q) use ($lecturer) {
                $q->where('academic_advisor_id', $lecturer?->id)
                  ->orWhere('academic_advisor_lecturer_id', $lecturer?->id);
            })
            ->where('status', 'submitted')
            ->get();

        // Thesis guidance logs pending verification
        $pendingGuidanceLogs = ThesisGuidanceLog::with(['thesis.student.user'])
            ->where('lecturer_id', $lecturer?->id)
            ->where('is_verified', false)
            ->get();

        // Grading statistics across assigned sections
        $totalClasses = $classSections->count();
        $totalStudentsGraded = 0;
        $totalStudentsEnrolled = 0;

        foreach ($classSections as $section) {
            $totalStudentsEnrolled += $section->krsItems->count();
            $totalStudentsGraded += $section->krsItems->whereNotNull('letter_grade')->count();
        }

        $gradingProgressPercent = $totalStudentsEnrolled > 0 
            ? round(($totalStudentsGraded / $totalStudentsEnrolled) * 100) 
            : 100;

        return view('portal.lecturer.dashboard', [
            'user' => $user,
            'lecturer' => $lecturer,
            'activePeriod' => $activePeriod,
            'classSections' => $classSections,
            'assignedClasses' => $classSections,
            'todayTeaching' => $todayTeaching,
            'currentDayName' => $currentDayName,
            'pendingKrsAdvisees' => $pendingKrsAdvisees,
            'pendingGuidanceLogs' => $pendingGuidanceLogs,
            'totalClasses' => $totalClasses,
            'totalStudentsEnrolled' => $totalStudentsEnrolled,
            'totalStudentsGraded' => $totalStudentsGraded,
            'gradingProgressPercent' => $gradingProgressPercent,
        ]);
    }
}
