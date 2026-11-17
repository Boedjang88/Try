<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\KrsItem;
use App\Models\KrsSubmission;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SmartAdvisorController extends Controller
{
    public const GRADE_POINTS = [
        'A' => 4.00,
        'A-' => 3.75,
        'B+' => 3.50,
        'B' => 3.00,
        'B-' => 2.75,
        'C+' => 2.50,
        'C' => 2.00,
        'D' => 1.00,
        'E' => 0.00,
    ];

    /**
     * Show Smart Academic Advisor Workspace.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            abort(403, 'Akses terbatas hanya untuk mahasiswa aktif.');
        }

        $activePeriod = AcademicPeriod::where('is_active', true)->first() 
            ?? AcademicPeriod::orderByDesc('year_start')->first();

        // Get active KRS submission
        $activeKrs = KrsSubmission::where('student_id', $student->id)
            ->where('academic_period_id', $activePeriod->id)
            ->with(['items.classSection.course', 'items.classSection.lecturer.user'])
            ->first();

        $enrolledItems = $activeKrs ? $activeKrs->items : collect();

        // Completed courses
        $completedKrsItems = KrsItem::whereHas('krsSubmission', function ($q) use ($student) {
            $q->where('student_id', $student->id);
        })
        ->whereNotNull('grade_point')
        ->with('classSection.course')
        ->get();

        $completedCourseIds = $completedKrsItems->map(function ($item) {
            return $item->classSection?->course_id;
        })->filter()->unique()->toArray();

        // Passed course IDs (grade_point >= 2.0 / C)
        $passedCourseIds = $completedKrsItems->filter(function ($item) {
            return (float)$item->grade_point >= 2.0;
        })->map(function ($item) {
            return $item->classSection?->course_id;
        })->filter()->unique()->toArray();

        // Recommend study plan for next semester
        $recommendations = $this->generateStudyPlanRecommendations($student, $passedCourseIds, $completedCourseIds);

        // Pre-calculate current baseline simulation data
        $baseline = $this->calculateGpaSimulation($student, []);

        return view('portal.student.smart-advisor', compact(
            'student',
            'activePeriod',
            'activeKrs',
            'enrolledItems',
            'recommendations',
            'baseline'
        ));
    }

    /**
     * AJAX/API endpoint for instant GPA simulation.
     */
    public function simulate(Request $request): JsonResponse
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Mahasiswa tidak ditemukan.'], 403);
        }

        $simulatedGrades = $request->input('simulated_grades', []);
        $result = $this->calculateGpaSimulation($student, $simulatedGrades);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Core Algorithm: Calculate projected IPK, IPS, SKS, and Graduation Honors Predicate.
     */
    public function calculateGpaSimulation(Student $student, array $simulatedGrades): array
    {
        $activePeriod = AcademicPeriod::where('is_active', true)->first() 
            ?? AcademicPeriod::orderByDesc('year_start')->first();

        // Get all graded historical KRS items
        $historicalItems = KrsItem::whereHas('krsSubmission', function ($q) use ($student, $activePeriod) {
            $q->where('student_id', $student->id)
              ->where('academic_period_id', '!=', $activePeriod->id);
        })
        ->whereNotNull('grade_point')
        ->with('classSection.course')
        ->get();

        // Active KRS items
        $activeItems = KrsItem::whereHas('krsSubmission', function ($q) use ($student, $activePeriod) {
            $q->where('student_id', $student->id)
              ->where('academic_period_id', $activePeriod->id);
        })
        ->with('classSection.course')
        ->get();

        // Map course best grades
        $coursesGraded = [];
        $hasHistoricalDOrE = false;

        foreach ($historicalItems as $item) {
            if (!$item->classSection || !$item->classSection->course) continue;
            $courseId = $item->classSection->course_id;
            $gradePt = (float)$item->grade_point;
            $letter = $item->letter_grade;
            $sks = (int)$item->classSection->course->sks;

            if (in_array($letter, ['D', 'E'])) {
                $hasHistoricalDOrE = true;
            }

            if (!isset($coursesGraded[$courseId]) || $coursesGraded[$courseId]['point'] < $gradePt) {
                $coursesGraded[$courseId] = [
                    'point' => $gradePt,
                    'sks' => $sks,
                    'letter' => $letter,
                ];
            }
        }

        // Apply active semester simulated grades
        $semesterSks = 0;
        $semesterPoints = 0.0;
        $hasSimulatedDOrE = false;

        $activeCoursesSimulated = [];

        foreach ($activeItems as $item) {
            if (!$item->classSection || !$item->classSection->course) continue;
            $courseId = $item->classSection->course_id;
            $sks = (int)$item->classSection->course->sks;

            // Pick simulated grade or current grade or default 'A'
            $letter = $simulatedGrades[$item->id] ?? $item->letter_grade ?? 'A';
            $letter = strtoupper(trim($letter));
            $point = self::GRADE_POINTS[$letter] ?? 4.00;

            if (in_array($letter, ['D', 'E'])) {
                $hasSimulatedDOrE = true;
            }

            $semesterSks += $sks;
            $semesterPoints += ($point * $sks);

            $activeCoursesSimulated[] = [
                'krs_item_id' => $item->id,
                'course_code' => $item->classSection->course->code,
                'course_name' => $item->classSection->course->name,
                'sks' => $sks,
                'simulated_letter' => $letter,
                'simulated_point' => $point,
            ];

            // Retaken course logic: highest grade wins in cumulative IPK
            if (!isset($coursesGraded[$courseId]) || $coursesGraded[$courseId]['point'] < $point) {
                $coursesGraded[$courseId] = [
                    'point' => $point,
                    'sks' => $sks,
                    'letter' => $letter,
                ];
            }
        }

        // Projected Semester IPS
        $projectedIps = $semesterSks > 0 ? round($semesterPoints / $semesterSks, 2) : 0.00;

        // Cumulative calculation
        $cumulativeSks = 0;
        $cumulativePoints = 0.0;
        $passedSksCount = 0;

        foreach ($coursesGraded as $cg) {
            $cumulativeSks += $cg['sks'];
            $cumulativePoints += ($cg['point'] * $cg['sks']);
            if ($cg['point'] >= 2.00) { // C or higher counts as passed
                $passedSksCount += $cg['sks'];
            }
        }

        $projectedIpk = $cumulativeSks > 0 ? round($cumulativePoints / $cumulativeSks, 2) : 0.00;

        // Target 144 SKS completion
        $targetGraduationSks = 144;
        $sksRemaining = max(0, $targetGraduationSks - $passedSksCount);
        $completionPercentage = min(100.0, round(($passedSksCount / $targetGraduationSks) * 100, 1));

        // Graduation Predicate Check
        // Rule: Summa Cum Laude (>= 3.90), Magna Cum Laude (>= 3.75), Cum Laude (>= 3.51)
        // Requires: Zero D/E grades, within max 8 semesters
        $hasDOrE = $hasHistoricalDOrE || $hasSimulatedDOrE;
        $isWithin8Semesters = ($student->current_semester <= 8);

        $honorsEligible = (!$hasDOrE && $isWithin8Semesters);
        $predicate = 'Memuaskan';
        $predicateBadgeColor = 'slate';

        if ($projectedIpk >= 3.90 && $honorsEligible) {
            $predicate = 'Summa Cum Laude';
            $predicateBadgeColor = 'gold';
        } elseif ($projectedIpk >= 3.75 && $honorsEligible) {
            $predicate = 'Magna Cum Laude';
            $predicateBadgeColor = 'emerald';
        } elseif ($projectedIpk >= 3.51 && $honorsEligible) {
            $predicate = 'Cum Laude';
            $predicateBadgeColor = 'blue';
        } elseif ($projectedIpk >= 3.00) {
            $predicate = 'Sangat Memuaskan';
            $predicateBadgeColor = 'indigo';
        } elseif ($projectedIpk >= 2.76) {
            $predicate = 'Memuaskan';
            $predicateBadgeColor = 'slate';
        } else {
            $predicate = 'Cukup';
            $predicateBadgeColor = 'amber';
        }

        return [
            'current_ipk' => (float)$student->current_gpa_ipk,
            'current_sks' => (int)$student->total_sks_completed,
            'current_semester' => (int)$student->current_semester,
            'semester_sks' => $semesterSks,
            'projected_ips' => $projectedIps,
            'projected_ipk' => $projectedIpk,
            'projected_sks_completed' => $passedSksCount,
            'sks_remaining' => $sksRemaining,
            'completion_percentage' => $completionPercentage,
            'graduation_predicate' => $predicate,
            'predicate_badge_color' => $predicateBadgeColor,
            'honors_eligible' => $honorsEligible,
            'honors_disqualification_reasons' => array_filter([
                $hasDOrE ? 'Terdapat mata kuliah dengan nilai D atau E' : null,
                !$isWithin8Semesters ? 'Masa studi melampaui batas 8 semester reguler' : null,
            ]),
            'active_courses' => $activeCoursesSimulated,
        ];
    }

    /**
     * Study Plan Recommendation Engine: analyzes uncompleted mandatory courses and prerequisites.
     */
    protected function generateStudyPlanRecommendations(Student $student, array $passedCourseIds, array $completedCourseIds): array
    {
        $studyProgramId = $student->study_program_id;

        // Query all mandatory courses for student's study program
        $allCourses = Course::where('study_program_id', $studyProgramId)
            ->with(['prerequisites'])
            ->orderBy('semester_level')
            ->get();

        $recommended = [];
        $lockedByPrereq = [];
        $totalRecommendedSks = 0;
        $maxRecommendSks = $student->max_sks_allowed ?? 24;

        foreach ($allCourses as $course) {
            // Skip already completed & passed courses
            if (in_array($course->id, $passedCourseIds)) {
                continue;
            }

            // Check prerequisites
            $prerequisites = $course->prerequisites;
            $canTake = true;
            $missingPrereqs = [];

            foreach ($prerequisites as $prereq) {
                if (!in_array($prereq->id, $passedCourseIds)) {
                    $canTake = false;
                    $missingPrereqs[] = $prereq->code . ' - ' . $prereq->name;
                }
            }

            if ($canTake) {
                if (($totalRecommendedSks + $course->sks) <= $maxRecommendSks) {
                    $recommended[] = [
                        'id' => $course->id,
                        'code' => $course->code,
                        'name' => $course->name,
                        'sks' => $course->sks,
                        'semester_level' => $course->semester_level,
                        'is_mandatory' => (bool)$course->is_mandatory,
                        'priority' => $course->is_mandatory ? 'Tinggi (Wajib Prodi)' : 'Pilihan Peminatan',
                    ];
                    $totalRecommendedSks += $course->sks;
                }
            } else {
                $lockedByPrereq[] = [
                    'id' => $course->id,
                    'code' => $course->code,
                    'name' => $course->name,
                    'sks' => $course->sks,
                    'missing_prerequisites' => $missingPrereqs,
                ];
            }
        }

        return [
            'recommended_courses' => $recommended,
            'total_recommended_sks' => $totalRecommendedSks,
            'max_allowed_sks' => $maxRecommendSks,
            'locked_courses' => $lockedByPrereq,
        ];
    }
}
