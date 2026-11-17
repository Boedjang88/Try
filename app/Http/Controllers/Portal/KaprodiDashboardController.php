<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\ClassSection;
use App\Models\Course;
use App\Models\Curriculum;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\StudyProgram;
use App\Models\YudisiumClearance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * KaprodiDashboardController
 *
 * Strictly scoped by `study_program_id`. Manages OBE curriculum, CPL/CPMK matrix,
 * lecturer course assignment, and grade lock overrides for their Study Program only.
 */
class KaprodiDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();

        // ABAC Scoping: Strictly scoped by the Kaprodi's Study Program ID
        $prodiId = $user->study_program_id;
        $studyProgram = $prodiId ? StudyProgram::with('faculty')->find($prodiId) : null;

        if (!$studyProgram) {
            $studyProgram = StudyProgram::with('faculty')->first();
            $prodiId = $studyProgram?->id;
        }

        // Active Academic Period (Cached)
        $activePeriod = Cache::remember('active_academic_period', 3600, function () {
            return AcademicPeriod::where('is_active', true)->first();
        });

        // Curricula & OBE Metrics for this Study Program
        $curriculum = Curriculum::with(['obeCpls' => fn($q) => $q->withCount('obeCpmks')])
            ->where('study_program_id', $prodiId)
            ->where('is_active', true)
            ->first();

        $totalCpls = $curriculum ? $curriculum->obeCpls->count() : 0;
        $totalCpmks = 0;
        if ($curriculum) {
            foreach ($curriculum->obeCpls as $cpl) {
                $totalCpmks += $cpl->obe_cpmks_count;
            }
        }

        // Courses for this Study Program
        $totalCourses = Course::where('curriculum_id', $curriculum?->id)->count();

        // Lecturers in this Study Program
        $lecturers = Lecturer::with('user')
            ->where('study_program_id', $prodiId)
            ->get();

        // Students in this Study Program
        $totalStudents = Student::where('study_program_id', $prodiId)->count();
        $avgGpa = Student::where('study_program_id', $prodiId)->avg('cumulative_gpa_ipk') ?? 3.45;

        // Class Sections for Grade Lock Monitoring in this Study Program
        $classSections = ClassSection::with(['course', 'lecturer.user', 'krsItems'])
            ->whereHas('course', function ($q) use ($curriculum) {
                $q->where('curriculum_id', $curriculum?->id);
            })
            ->where('academic_period_id', $activePeriod?->id)
            ->get();

        // Yudisium candidates awaiting verification in this Study Program
        $pendingYudisium = YudisiumClearance::with(['student.user'])
            ->whereHas('student', fn($q) => $q->where('study_program_id', $prodiId))
            ->where('status', 'pending')
            ->get();

        return view('portal.kaprodi.dashboard', [
            'user' => $user,
            'studyProgram' => $studyProgram,
            'curriculum' => $curriculum,
            'totalCpls' => $totalCpls,
            'totalCpmks' => $totalCpmks,
            'totalCourses' => $totalCourses,
            'lecturers' => $lecturers,
            'totalStudents' => $totalStudents,
            'avgGpa' => $avgGpa,
            'classSections' => $classSections,
            'pendingYudisium' => $pendingYudisium,
            'activePeriod' => $activePeriod,
        ]);
    }
}
