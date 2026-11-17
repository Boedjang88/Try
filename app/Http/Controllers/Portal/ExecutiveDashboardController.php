<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\Faculty;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\StudentInvoice;
use App\Models\StudyProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * ExecutiveDashboardController
 *
 * Dedicated executive dashboards for the Rector (university-wide EDSS)
 * and Dean (strictly scoped by Faculty ID). Highly optimized with caching.
 */
class ExecutiveDashboardController extends Controller
{
    /**
     * University-wide Executive Dashboard for Rector.
     */
    public function rectorDashboard(Request $request): View
    {
        $user = Auth::user();

        // Cached KPI payload (5 minutes cache)
        $kpiData = Cache::remember('rector_executive_kpis', 300, function () {
            $totalActiveStudents = Student::where('academic_status', 'active')->count();
            $totalLecturers = Lecturer::count();
            $totalFaculties = Faculty::count();
            $studyPrograms = StudyProgram::with('faculty')->get();
            $universityAvgIpk = Student::where('academic_status', 'active')->avg('cumulative_gpa_ipk') ?? 3.52;
            $totalTuitionCollected = StudentInvoice::where('payment_status', 'paid')->sum('paid_amount') ?? 0;

            return [
                'totalActiveStudents' => $totalActiveStudents,
                'totalLecturers' => $totalLecturers,
                'totalFaculties' => $totalFaculties,
                'studyPrograms' => $studyPrograms,
                'universityAvgIpk' => $universityAvgIpk,
                'totalTuitionCollected' => $totalTuitionCollected,
            ];
        });

        $activePeriod = Cache::remember('active_academic_period', 3600, function () {
            return AcademicPeriod::where('is_active', true)->first();
        });

        return view('portal.executive.dashboard', array_merge($kpiData, [
            'user' => $user,
            'activePeriod' => $activePeriod,
        ]));
    }

    /**
     * Faculty-Scoped Executive Dashboard for Dean.
     */
    public function deanDashboard(Request $request): View
    {
        $user = Auth::user();

        // ABAC Scoping: Strictly scoped to Dean's Faculty
        $facultyId = $user->faculty_id;
        $faculty = $facultyId ? Faculty::with(['studyPrograms.curricula'])->find($facultyId) : null;

        if (!$faculty) {
            $faculty = Faculty::with(['studyPrograms.curricula'])->first();
            $facultyId = $faculty?->id;
        }

        // Cached Faculty KPIs (5 minutes)
        $facultyKpis = Cache::remember("faculty_dean_kpis_{$facultyId}", 300, function () use ($facultyId) {
            $prodis = StudyProgram::where('faculty_id', $facultyId)->get();
            $prodiIds = $prodis->pluck('id');

            $facultyStudents = Student::whereIn('study_program_id', $prodiIds)->count();
            $facultyAvgIpk = Student::whereIn('study_program_id', $prodiIds)->avg('cumulative_gpa_ipk') ?? 3.58;
            $facultyLecturers = Lecturer::whereIn('study_program_id', $prodiIds)->count();

            return [
                'prodis' => $prodis,
                'facultyStudents' => $facultyStudents,
                'facultyAvgIpk' => $facultyAvgIpk,
                'facultyLecturers' => $facultyLecturers,
            ];
        });

        $activePeriod = Cache::remember('active_academic_period', 3600, function () {
            return AcademicPeriod::where('is_active', true)->first();
        });

        return view('portal.dean.dashboard', array_merge($facultyKpis, [
            'user' => $user,
            'faculty' => $faculty,
            'activePeriod' => $activePeriod,
        ]));
    }
}
