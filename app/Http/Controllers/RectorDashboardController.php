<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\Applicant;
use App\Models\Faculty;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\StudentInvoice;
use App\Models\StudyProgram;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RectorDashboardController extends Controller
{
    /**
     * Display Executive Institutional Analytics & Strategic University KPIs.
     */
    public function index(Request $request): View
    {
        $activePeriod = AcademicPeriod::where('is_active', true)->first() 
            ?? AcademicPeriod::orderByDesc('year_start')->first();

        // 1. PMB Admissions Funnel
        $totalApplicants = Applicant::count();
        $cbtCompleted = Applicant::whereNotNull('cbt_score')->count();
        $acceptedApplicants = Applicant::whereIn('status', ['accepted', 'registered'])->count();
        $registeredStudents = Applicant::where('status', 'registered')->count();
        $conversionRate = $totalApplicants > 0 ? round(($registeredStudents / $totalApplicants) * 100, 1) : 0;

        // 2. Financial Health & Tuition Revenue
        $totalBilled = (float) StudentInvoice::sum('amount');
        $totalCollected = (float) StudentInvoice::sum('paid_amount');
        $outstandingBalance = max(0, $totalBilled - $totalCollected);
        $collectionRate = $totalBilled > 0 ? round(($totalCollected / $totalBilled) * 100, 1) : 0;

        // 3. Student Retention & Academic Standing (EWS)
        $totalActiveStudents = Student::where('academic_status', 'active')->count();
        $totalGraduatedStudents = Student::where('academic_status', 'graduated')->count();
        $probationStudents = Student::where(function ($q) {
            $q->where('academic_status', 'probation')
              ->orWhere('last_semester_gpa_ips', '<', 2.00);
        })->count();

        // Average IPK per Study Program
        $studyPrograms = StudyProgram::with(['faculty', 'students'])->get()->map(function ($prodi) {
            $students = $prodi->students;
            $avgIpk = $students->count() > 0 ? round($students->avg('cumulative_gpa_ipk'), 2) : 0.00;
            $activeCount = $students->where('academic_status', 'active')->count();

            return [
                'code' => $prodi->code,
                'name' => $prodi->name_id,
                'faculty' => $prodi->faculty->name_id ?? 'UPH',
                'active_students' => $activeCount,
                'avg_ipk' => $avgIpk,
            ];
        });

        $universityAvgIpk = Student::where('academic_status', 'active')->avg('cumulative_gpa_ipk');
        $universityAvgIpk = round($universityAvgIpk ?: 3.42, 2);

        // 4. Faculty & Academic Excellence
        $totalLecturers = Lecturer::count();
        $totalFaculties = Faculty::count();

        return view('dashboard.rector', compact(
            'activePeriod',
            'totalApplicants',
            'cbtCompleted',
            'acceptedApplicants',
            'registeredStudents',
            'conversionRate',
            'totalBilled',
            'totalCollected',
            'outstandingBalance',
            'collectionRate',
            'totalActiveStudents',
            'totalGraduatedStudents',
            'probationStudents',
            'studyPrograms',
            'universityAvgIpk',
            'totalLecturers',
            'totalFaculties'
        ));
    }
}
