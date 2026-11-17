<?php

namespace App\Http\Controllers;

use App\Actions\CalculateMaxSksAction;
use App\Models\AcademicPeriod;
use App\Models\KrsItem;
use App\Models\KrsSubmission;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class KhsController extends Controller
{
    /**
     * Display Student's Kartu Hasil Studi (KHS) with EDOM survey gate & Early Warning System (EWS).
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Resolve target student with strict Anti-IDOR check
        if ($user->role === 'student') {
            if ($request->filled('student_id') && $request->get('student_id') !== $user->student?->id) {
                abort(403, 'Akses ditolak: Parameter student_id tidak sah.');
            }
            if ($request->filled('nim') && $request->get('nim') !== $user->student?->nim) {
                abort(403, 'Akses ditolak: Parameter NIM tidak sah.');
            }
            $student = $user->student;
        } else {
            // Staff/Lecturer/Kaprodi view mode
            $studentId = $request->get('student_id');
            $nim = $request->get('nim');
            if ($studentId) {
                $student = Student::with(['user', 'studyProgram.faculty'])->find($studentId);
            } elseif ($nim) {
                $student = Student::with(['user', 'studyProgram.faculty'])->where('nim', $nim)->first();
            } else {
                $student = Student::with(['user', 'studyProgram.faculty'])->first();
            }
        }

        if (!$student) {
            abort(404, 'Data mahasiswa tidak ditemukan.');
        }

        $student->load(['studyProgram.faculty', 'user']);

        // Academic Periods List
        $academicPeriods = AcademicPeriod::orderByDesc('year_start')
            ->orderByDesc('semester')
            ->get();

        $selectedPeriodId = $request->get('academic_period_id')
            ?? AcademicPeriod::where('is_active', true)->value('id')
            ?? $academicPeriods->first()?->id;

        $selectedPeriod = AcademicPeriod::find($selectedPeriodId);

        // Fetch KRS Submission for the active/selected period
        $krsSubmission = KrsSubmission::with([
            'academicPeriod',
            'krsItems' => fn($q) => $q->orderBy('id'),
            'krsItems.classSection.course',
            'krsItems.classSection.lecturer.user',
            'krsItems.classSection.room',
        ])
        ->where('student_id', $student->id)
        ->where('academic_period_id', $selectedPeriodId)
        ->first();

        $krsItems = $krsSubmission ? $krsSubmission->krsItems : collect();

        // Calculate Semester IPS & Unlocked Grades
        $totalSemesterSks = 0;
        $totalGradedSks = 0;
        $totalQualityPoints = 0.0;
        $allEdomCompleted = true;
        $pendingEdomCount = 0;

        foreach ($krsItems as $item) {
            $sks = $item->classSection->course->sks ?? 0;
            $totalSemesterSks += $sks;

            if (!$item->edom_completed) {
                $allEdomCompleted = false;
                $pendingEdomCount++;
            }

            // Only count towards IPS if EDOM completed and grade exists
            if ($item->edom_completed && $item->grade_point !== null && $item->final_numeric_score !== null) {
                $totalGradedSks += $sks;
                $totalQualityPoints += ((float)$item->grade_point * $sks);
            }
        }

        $ips = $totalGradedSks > 0 ? round($totalQualityPoints / $totalGradedSks, 2) : 0.00;

        // Calculate Cumulative IPK across all completed semesters
        $allApprovedItems = KrsItem::whereHas('krsSubmission', function ($q) use ($student) {
            $q->where('student_id', $student->id)->where('status', 'approved');
        })
        ->with('classSection.course')
        ->whereNotNull('final_numeric_score')
        ->where('edom_completed', true)
        ->get();

        $cumulativeSks = 0;
        $cumulativePoints = 0.0;

        // Group by course_id to pick highest grade for repeated courses
        $coursesGraded = [];
        foreach ($allApprovedItems as $item) {
            $courseId = $item->classSection->course_id;
            $gradePt = (float)$item->grade_point;
            $sks = (int)$item->classSection->course->sks;

            if (!isset($coursesGraded[$courseId]) || $coursesGraded[$courseId]['point'] < $gradePt) {
                $coursesGraded[$courseId] = [
                    'point' => $gradePt,
                    'sks' => $sks,
                ];
            }
        }

        foreach ($coursesGraded as $cg) {
            $cumulativeSks += $cg['sks'];
            $cumulativePoints += ($cg['point'] * $cg['sks']);
        }

        $ipk = $cumulativeSks > 0 ? round($cumulativePoints / $cumulativeSks, 2) : (float)($student->current_gpa_ipk ?? 0);

        // Update student cached GPA
        if ($totalGradedSks > 0 && ($student->last_semester_gpa_ips != $ips || $student->current_gpa_ipk != $ipk)) {
            $student->last_semester_gpa_ips = $ips;
            $student->current_gpa_ipk = $ipk;
            $student->total_sks_completed = $cumulativeSks;
            $student->save();

            // Recalculate Next Max SKS
            app(CalculateMaxSksAction::class)->execute($student);
        }

        // Early Warning System (EWS) Evaluation
        $ewsStatus = self::evaluateEwsStatus($ips, $ipk, $student);

        // Next Max SKS Permitted
        $maxSksNext = $student->max_sks_allowed ?? 24;

        return view('khs.index', compact(
            'student',
            'academicPeriods',
            'selectedPeriod',
            'krsSubmission',
            'krsItems',
            'totalSemesterSks',
            'totalGradedSks',
            'totalQualityPoints',
            'ips',
            'ipk',
            'cumulativeSks',
            'allEdomCompleted',
            'pendingEdomCount',
            'ewsStatus',
            'maxSksNext'
        ));
    }

    /**
     * Display Complete Official Academic Transcript (Transkrip Akademik Kumulatif).
     */
    public function transcript(Request $request): View
    {
        $user = Auth::user();

        if ($user->role === 'student') {
            $student = $user->student;
        } else {
            $studentId = $request->get('student_id');
            $nim = $request->get('nim');
            if ($studentId) {
                $student = Student::with(['user', 'studyProgram.faculty'])->find($studentId);
            } elseif ($nim) {
                $student = Student::with(['user', 'studyProgram.faculty'])->where('nim', $nim)->first();
            } else {
                $student = Student::with(['user', 'studyProgram.faculty'])->first();
            }
        }

        if (!$student) {
            abort(404, 'Data mahasiswa tidak ditemukan.');
        }

        $student->load(['studyProgram.faculty', 'user']);

        // Fetch all graded items from approved KRS
        $items = KrsItem::whereHas('krsSubmission', function ($q) use ($student) {
            $q->where('student_id', $student->id)->where('status', 'approved');
        })
        ->with([
            'classSection.course',
            'classSection.academicPeriod'
        ])
        ->whereNotNull('final_numeric_score')
        ->get();

        // Group by course_id to pick highest grade for repeated courses
        $transcriptCourses = [];
        $totalSksLulus = 0;
        $totalMutuPoints = 0.0;

        foreach ($items as $item) {
            $c = $item->classSection->course;
            $pt = (float)$item->grade_point;
            $sks = (int)$c->sks;

            if (!isset($transcriptCourses[$c->id]) || $transcriptCourses[$c->id]['grade_point'] < $pt) {
                $transcriptCourses[$c->id] = [
                    'code' => $c->code,
                    'name_id' => $c->name_id,
                    'name_en' => $c->name_en,
                    'sks' => $sks,
                    'letter_grade' => $item->letter_grade,
                    'grade_point' => $pt,
                    'quality_point' => round($sks * $pt, 2),
                    'academic_period' => $item->classSection->academicPeriod->name ?? '-',
                ];
            }
        }

        // Sort by course code
        usort($transcriptCourses, fn($a, $b) => strcmp($a['code'], $b['code']));

        foreach ($transcriptCourses as $tc) {
            $totalSksLulus += $tc['sks'];
            $totalMutuPoints += $tc['quality_point'];
        }

        $ipk = $totalSksLulus > 0 ? round($totalMutuPoints / $totalSksLulus, 2) : 0.00;

        // S1 Graduation target
        $targetSks = 144;
        $sksProgressPercent = min(100, round(($totalSksLulus / $targetSks) * 100, 1));

        // Predicate Determination (Standar BAN-PT / UPH)
        if ($ipk >= 3.51) {
            $predicate = 'Dengan Pujian (Cum Laude)';
        } elseif ($ipk >= 3.01) {
            $predicate = 'Sangat Memuaskan';
        } elseif ($ipk >= 2.76) {
            $predicate = 'Memuaskan';
        } else {
            $predicate = 'Cukup';
        }

        return view('khs.transcript', compact(
            'student',
            'transcriptCourses',
            'totalSksLulus',
            'targetSks',
            'sksProgressPercent',
            'totalMutuPoints',
            'ipk',
            'predicate'
        ));
    }

    /**
     * Evaluate Early Warning System (EWS) Status & Advisory Guidance.
     */
    private static function evaluateEwsStatus(float $ips, float $ipk, Student $student): array
    {
        if ($ips < 2.00 || $ipk < 2.00) {
            return [
                'level' => 'danger',
                'title' => 'Peringatan Akademik / Academic Probation (EWS Tingkat 1)',
                'badge' => 'PROBATION',
                'message' => 'Indeks Prestasi Semester (IPS) atau IPK Anda berada di bawah standar minimum kelulusan (< 2.00). Anda wajib menjalani konseling akademik terjadwal bersama Dosen Pembimbing Akademik (PA) sebelum pengisian KRS semester depan dibuka.',
                'action_required' => true,
            ];
        }

        if ($ips < 2.50) {
            return [
                'level' => 'warning',
                'title' => 'Pemantauan Akademik / Academic Advisory',
                'badge' => 'EWS WASPADA',
                'message' => 'Indeks prestasi Anda memerlukan peningkatan. Konsultasikan strategi pemilihan matakuliah dengan Dosen PA untuk menghindari risiko penurunan batas maksimum SKS.',
                'action_required' => false,
            ];
        }

        if ($ips >= 3.50 && $ipk >= 3.50) {
            return [
                'level' => 'success',
                'title' => 'Prestasi Akademik Unggul / Dean\'s List Candidate',
                'badge' => 'DEAN\'S LIST',
                'message' => 'Selamat atas capaian prestasi akademik istimewa Anda! Anda berhak mengambil beban studi maksimal hingga 24 SKS pada semester berikutnya.',
                'action_required' => false,
            ];
        }

        return [
            'level' => 'normal',
            'title' => 'Status Akademik Baik & Memenuhi Syarat',
            'badge' => 'GOOD STANDING',
            'message' => 'Kemajuan studi Anda berjalan sesuai kurikulum dan rencana pembelajaran.',
            'action_required' => false,
        ];
    }
}
