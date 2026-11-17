<?php

namespace App\Backend\SPMI;

use Exception;

/**
 * Enterprise Quality Assurance (SPMI) & Lecturer HR (BKD) Service
 * Tracks Tri Dharma BKD compliance (12-16 SKS) and aggregates BAN-PT / LAM 9 Criteria.
 */
class SPMIService
{
    public const BKD_MINIMUM_SKS = 12.0;
    public const BKD_MAXIMUM_SKS = 16.0;

    /**
     * Calculate Lecturer Workload (BKD - Beban Kerja Dosen) for Semester Reporting.
     * Dikti regulation: Total load must be between 12 and 16 SKS across Tri Dharma.
     */
    public static function evaluateBKD(
        float $teachingSks,      // Bidang A: Pendidikan (Min 6 SKS)
        float $researchSks,      // Bidang B: Penelitian (Min 2 SKS)
        float $communitySks,     // Bidang C: Pengabdian Masyarakat (Min 1 SKS)
        float $supportingSks = 0 // Bidang D: Penunjang
    ): array {
        $totalSks = $teachingSks + $researchSks + $communitySks + $supportingSks;

        $teachingValid = $teachingSks >= 6.0;
        $researchValid = $researchSks >= 2.0;
        $communityValid = $communitySks >= 1.0;
        $totalValid = ($totalSks >= self::BKD_MINIMUM_SKS && $totalSks <= self::BKD_MAXIMUM_SKS);

        $isCompliant = $teachingValid && $researchValid && $communityValid && $totalValid;

        $feedback = [];
        if (!$teachingValid) $feedback[] = 'Beban Pendidikan kurang dari syarat minimal 6 SKS.';
        if (!$researchValid) $feedback[] = 'Beban Penelitian kurang dari syarat minimal 2 SKS.';
        if (!$communityValid) $feedback[] = 'Beban Pengabdian kurang dari syarat minimal 1 SKS.';
        if ($totalSks < self::BKD_MINIMUM_SKS) $feedback[] = "Total beban ({$totalSks} SKS) kurang dari ambang minimal " . self::BKD_MINIMUM_SKS . " SKS.";
        if ($totalSks > self::BKD_MAXIMUM_SKS) $feedback[] = "Total beban ({$totalSks} SKS) melebihi batas maksimal " . self::BKD_MAXIMUM_SKS . " SKS (Kelebihan Beban).";

        return [
            'total_bkd_sks'    => round($totalSks, 2),
            'teaching_sks'     => round($teachingSks, 2),
            'research_sks'     => round($researchSks, 2),
            'community_sks'    => round($communitySks, 2),
            'supporting_sks'   => round($supportingSks, 2),
            'is_compliant'     => $isCompliant,
            'compliance_status'=> $isCompliant ? 'MEMENUHI_SYARAT_BKD' : 'BELUM_MEMENUHI_SYARAT',
            'feedback_notes'   => $feedback
        ];
    }

    /**
     * Evaluate BAN-PT / LAM 9 Criteria Accreditation Readiness.
     */
    public static function evaluateAccreditationReadiness(array $criteriaScores): array
    {
        // 9 Criteria BAN-PT standard weights
        $weights = [
            'kriteria_1' => ['name' => 'Visi, Misi, Tujuan, dan Strategi', 'weight' => 0.05],
            'kriteria_2' => ['name' => 'Tata Pamong, Tata Kelola, dan Kerjasama', 'weight' => 0.10],
            'kriteria_3' => ['name' => 'Mahasiswa', 'weight' => 0.10],
            'kriteria_4' => ['name' => 'Sumber Daya Manusia', 'weight' => 0.15],
            'kriteria_5' => ['name' => 'Keuangan, Sarana, dan Prasarana', 'weight' => 0.10],
            'kriteria_6' => ['name' => 'Pendidikan', 'weight' => 0.25],
            'kriteria_7' => ['name' => 'Penelitian', 'weight' => 0.10],
            'kriteria_8' => ['name' => 'Pengabdian kepada Masyarakat', 'weight' => 0.05],
            'kriteria_9' => ['name' => 'Luaran dan Capaian Tri Dharma', 'weight' => 0.10],
        ];

        $totalScore = 0.0;
        $details = [];

        foreach ($weights as $key => $meta) {
            $rawScore = $criteriaScores[$key] ?? 3.5; // Scale 1.00 to 4.00
            $weighted = $rawScore * $meta['weight'];
            $totalScore += $weighted;

            $details[$key] = [
                'name'           => $meta['name'],
                'raw_score'      => $rawScore,
                'weight'         => $meta['weight'],
                'weighted_score' => round($weighted, 3)
            ];
        }

        // Convert 4.00 scale to 400 scale BAN-PT
        $score400 = round($totalScore * 100, 1);

        $grade = 'Baik';
        if ($score400 >= 361) {
            $grade = 'Unggul';
        } elseif ($score400 >= 301) {
            $grade = 'Baik Sekali';
        }

        return [
            'score_scale_4'     => round($totalScore, 2),
            'score_scale_400'   => $score400,
            'predicted_ranking' => $grade,
            'is_unggul'         => $grade === 'Unggul',
            'details'           => $details,
            'assessed_at'       => date('Y-m-d H:i:s')
        ];
    }
}
