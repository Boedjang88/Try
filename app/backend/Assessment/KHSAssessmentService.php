<?php

namespace App\Backend\Assessment;

use App\Backend\Curriculum\OBEGradingService;
use Exception;

/**
 * Enterprise Assessment, KHS, IPK, and Academic Standing Evaluation Service
 * Handles grade locking, IPS/IPK calculation, Early Warning System (EWS / DO risk),
 * and EDOM (Evaluasi Dosen oleh Mahasiswa) evaluation gatekeeper.
 */
class KHSAssessmentService
{
    /**
     * Calculate Semester GPA (IPS) from an array of course results.
     */
    public static function calculateIPS(array $courseGrades): array
    {
        $totalSks = 0;
        $totalQualityPoints = 0; // SKS * GradePoint

        foreach ($courseGrades as $course) {
            $sks = $course['sks'];
            $gradeLetter = $course['grade_letter'];
            $gradePoint = OBEGradingService::convertToLetterAndPoint(85)['point']; // helper fallback

            // calculate point accurately
            $point = match (strtoupper(trim($gradeLetter))) {
                'A'  => 4.00,
                'A-' => 3.75,
                'B+' => 3.50,
                'B'  => 3.00,
                'B-' => 2.75,
                'C+' => 2.50,
                'C'  => 2.00,
                'D'  => 1.00,
                default => 0.00
            };

            $totalSks += $sks;
            $totalQualityPoints += ($sks * $point);
        }

        $ips = $totalSks > 0 ? round($totalQualityPoints / $totalSks, 2) : 0.00;

        return [
            'total_sks'            => $totalSks,
            'total_quality_points' => $totalQualityPoints,
            'ips'                  => $ips,
            'max_next_sks'         => $ips >= 3.00 ? 24 : ($ips >= 2.50 ? 21 : ($ips >= 2.00 ? 18 : 15))
        ];
    }

    /**
     * Calculate Cumulative GPA (IPK) across all completed semesters.
     */
    public static function calculateCumulativeIPK(array $allPassedCourses): array
    {
        $totalCumulativeSks = 0;
        $totalCumulativePoints = 0;

        foreach ($allPassedCourses as $course) {
            $sks = $course['sks'];
            $point = $course['point'];
            $totalCumulativeSks += $sks;
            $totalCumulativePoints += ($sks * $point);
        }

        $ipk = $totalCumulativeSks > 0 ? round($totalCumulativePoints / $totalCumulativeSks, 2) : 0.00;

        $predicate = 'Memuaskan';
        if ($ipk >= 3.51) {
            $predicate = 'Dengan Pujian (Cum Laude)';
        } elseif ($ipk >= 3.01) {
            $predicate = 'Sangat Memuaskan';
        }

        return [
            'cumulative_sks'    => $totalCumulativeSks,
            'cumulative_points' => round($totalCumulativePoints, 2),
            'ipk'               => $ipk,
            'predicate'         => $predicate
        ];
    }

    /**
     * Early Warning System (EWS) for Probation & Drop Out (DO) Risk Evaluation.
     * Evaluates national Dikti standards (minimum SKS per year and IPK thresholds).
     */
    public static function evaluateAcademicStanding(
        int $semesterNumber,
        int $totalPassedSks,
        float $ipk,
        int $consecutiveLowIps = 0
    ): array {
        $standing = 'NORMAL';
        $warningMessage = null;
        $isDropOutRisk = false;

        // Stage 1: Semester 4 Milestone (Minimal 30 SKS lulus dan IPK >= 2.00)
        if ($semesterNumber >= 4 && ($totalPassedSks < 30 || $ipk < 2.00)) {
            $standing = 'PROBATION_STAGE_1';
            $warningMessage = 'Peringatan Akademik Tahap 1: Kurang dari 30 SKS atau IPK < 2.00 pada Semester 4.';
            $isDropOutRisk = true;
        }

        // Stage 2: Semester 8 Milestone (Minimal 75 SKS lulus dan IPK >= 2.00)
        if ($semesterNumber >= 8 && ($totalPassedSks < 75 || $ipk < 2.00)) {
            $standing = 'PROBATION_STAGE_2';
            $warningMessage = 'Peringatan Akademik Tahap 2: Mahasiswa berisiko mendekati batas studi maksimal.';
            $isDropOutRisk = true;
        }

        // Stage 3: Maximum 14 Semesters limit (for S1)
        if ($semesterNumber >= 14) {
            $standing = 'CRITICAL_MAX_SEMESTER';
            $warningMessage = 'Peringatan Kritis: Mahasiswa telah mencapai batas masa studi maksimal 14 semester.';
            $isDropOutRisk = true;
        }

        // Consecutive poor IPS
        if ($consecutiveLowIps >= 2) {
            $standing = 'ACADEMIC_PROBATION';
            $warningMessage = 'Mahasiswa dalam Masa Percobaan Akademik akibat IPS < 2.00 selama 2 semester berturut-turut.';
        }

        return [
            'standing'          => $standing,
            'is_drop_out_risk'  => $isDropOutRisk,
            'warning_message'   => $warningMessage,
            'requires_advisor_counseling' => $isDropOutRisk || $standing !== 'NORMAL'
        ];
    }

    /**
     * Mandatory EDOM (Evaluasi Dosen oleh Mahasiswa) Survey Gate.
     * Blocks student from viewing final semester KHS if any enrolled class's survey is incomplete.
     */
    public static function checkEDOMAccessGate(array $enrolledClasses, array $submittedEdomClassIds): array
    {
        $pendingClasses = [];
        foreach ($enrolledClasses as $cls) {
            if (!in_array($cls['id'], $submittedEdomClassIds, true)) {
                $pendingClasses[] = $cls;
            }
        }

        $isGateUnlocked = empty($pendingClasses);

        return [
            'is_unlocked'       => $isGateUnlocked,
            'pending_classes'   => $pendingClasses,
            'total_classes'     => count($enrolledClasses),
            'completed_surveys' => count($submittedEdomClassIds),
            'gate_message'      => $isGateUnlocked
                ? 'Seluruh evaluasi dosen telah lengkap. Nilai KHS dapat diakses.'
                : 'Akses KHS ditutup sementara. Harap lengkapi kuesioner EDOM untuk mata kuliah yang belum dinilai.'
        ];
    }
}
