<?php

namespace App\Backend\Curriculum;

class OBEGradingService
{
    /**
     * Compute final numerical grade from components.
     */
    public static function calculateFinalNumeric(
        float $attendance,
        float $assignments,
        float $quizzes,
        float $uts,
        float $uas,
        float $project,
        array $weights = [
            'attendance' => 0.10,
            'assignments' => 0.20,
            'quizzes' => 0.15,
            'uts' => 0.25,
            'uas' => 0.30,
            'project' => 0.00
        ]
    ): float {
        $total = ($attendance * ($weights['attendance'] ?? 0.10))
               + ($assignments * ($weights['assignments'] ?? 0.20))
               + ($quizzes * ($weights['quizzes'] ?? 0.15))
               + ($uts * ($weights['uts'] ?? 0.25))
               + ($uas * ($weights['uas'] ?? 0.30))
               + ($project * ($weights['project'] ?? 0.00));

        return round(min(100.00, max(0.00, $total)), 2);
    }

    /**
     * Convert numeric score to Letter Grade and Grade Point (Indonesian Standard).
     */
    public static function convertToLetterAndPoint(float $numericScore): array
    {
        if ($numericScore >= 85.00) {
            return ['grade' => 'A', 'point' => 4.00, 'passed' => true];
        } elseif ($numericScore >= 80.00) {
            return ['grade' => 'A-', 'point' => 3.75, 'passed' => true];
        } elseif ($numericScore >= 75.00) {
            return ['grade' => 'B+', 'point' => 3.50, 'passed' => true];
        } elseif ($numericScore >= 70.00) {
            return ['grade' => 'B', 'point' => 3.00, 'passed' => true];
        } elseif ($numericScore >= 65.00) {
            return ['grade' => 'B-', 'point' => 2.75, 'passed' => true];
        } elseif ($numericScore >= 60.00) {
            return ['grade' => 'C+', 'point' => 2.50, 'passed' => true];
        } elseif ($numericScore >= 55.00) {
            return ['grade' => 'C', 'point' => 2.00, 'passed' => true];
        } elseif ($numericScore >= 40.00) {
            return ['grade' => 'D', 'point' => 1.00, 'passed' => false];
        } else {
            return ['grade' => 'E', 'point' => 0.00, 'passed' => false];
        }
    }

    /**
     * Calculate CPL Attainment Percentage from CPMK scores.
     */
    public static function calculateCPLAttainment(array $cpmkScores, array $cplMatrix): array
    {
        $cplResults = [];
        foreach ($cplMatrix as $cplId => $cpmkWeights) {
            $sumWeight = 0;
            $weightedScore = 0;
            foreach ($cpmkWeights as $cpmkId => $weight) {
                if (isset($cpmkScores[$cpmkId])) {
                    $weightedScore += ($cpmkScores[$cpmkId] * $weight);
                    $sumWeight += $weight;
                }
            }
            $cplResults[$cplId] = $sumWeight > 0 ? round($weightedScore / $sumWeight, 2) : 0.00;
        }
        return $cplResults;
    }
}
