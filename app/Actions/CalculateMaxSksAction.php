<?php

namespace App\Actions;

use App\Models\Student;

/**
 * Enterprise Action: Calculate Maximum Permissible SKS Load
 * Based on preceding semester IPS (Indeks Prestasi Semester) and Academic Standing.
 */
class CalculateMaxSksAction
{
    /**
     * Determine maximum SKS allowed for student.
     *
     * Academic Regulation Scale:
     * - IPS >= 3.00           -> Max 24 SKS
     * - 2.50 <= IPS <= 2.99   -> Max 21 SKS
     * - 2.00 <= IPS <= 2.49   -> Max 18 SKS
     * - 1.50 <= IPS <= 1.99   -> Max 15 SKS
     * - IPS < 1.50            -> Max 12 SKS
     * - Freshmen (Semester 1) -> Default 24 SKS (Paket Kurikulum Awal)
     */
    public function execute(Student $student): int
    {
        // Freshman cohort rule
        if ($student->current_semester <= 1) {
            $maxSks = 24;
        } else {
            $ips = (float)$student->last_semester_gpa_ips;

            if ($ips >= 3.00) {
                $maxSks = 24;
            } elseif ($ips >= 2.50) {
                $maxSks = 21;
            } elseif ($ips >= 2.00) {
                $maxSks = 18;
            } elseif ($ips >= 1.50) {
                $maxSks = 15;
            } else {
                $maxSks = 12;
            }
        }

        // Academic probation cap
        if ($student->academic_status === 'probation' && $maxSks > 18) {
            $maxSks = 18;
        }

        if ($student->max_sks_allowed !== $maxSks) {
            $old = $student->max_sks_allowed;
            $student->max_sks_allowed = $maxSks;
            $student->save();

            $student->recordAudit(
                'MAX_SKS_RECALCULATED',
                "Max SKS recalculated from {$old} to {$maxSks} based on IPS [{$student->last_semester_gpa_ips}]"
            );
        }

        return $maxSks;
    }
}
