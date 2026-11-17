<?php

namespace App\Backend\KRS;

use Exception;

class KRSEngineService
{
    /**
     * Determine maximum allowed SKS based on previous semester IPS (Indeks Prestasi Semester).
     * Standard Indonesian Directorate General of Higher Education (Ditjen Dikti).
     */
    public static function calculateMaxSksAllowed(float $previousIps): int
    {
        if ($previousIps >= 3.00) {
            return 24;
        } elseif ($previousIps >= 2.50) {
            return 21;
        } elseif ($previousIps >= 2.00) {
            return 18;
        } elseif ($previousIps >= 1.50) {
            return 15;
        } else {
            return 12;
        }
    }

    /**
     * Validate potential timetable schedule collision.
     * Returns true if there is an overlapping collision.
     */
    public static function checkScheduleCollision(array $existingSchedules, array $newSchedule): bool
    {
        foreach ($existingSchedules as $schedule) {
            if ($schedule['day_of_week'] === $newSchedule['day_of_week']) {
                $existingStart = strtotime($schedule['start_time']);
                $existingEnd = strtotime($schedule['end_time']);
                $newStart = strtotime($newSchedule['start_time']);
                $newEnd = strtotime($newSchedule['end_time']);

                if (max($existingStart, $newStart) < min($existingEnd, $newEnd)) {
                    return true; // Collision detected
                }
            }
        }
        return false;
    }

    /**
     * Validate course prerequisites.
     */
    public static function validatePrerequisites(array $passedCourses, array $prerequisites): array
    {
        $unmet = [];
        foreach ($prerequisites as $prereq) {
            $courseId = $prereq['prerequisite_course_id'];
            $minGrade = $prereq['minimum_grade'] ?? 'D';
            
            if (!isset($passedCourses[$courseId])) {
                $unmet[] = "Mata kuliah prasyarat {$prereq['course_code']} belum pernah diambil.";
                continue;
            }

            $studentGrade = $passedCourses[$courseId];
            if (self::gradePoints($studentGrade) < self::gradePoints($minGrade)) {
                $unmet[] = "Nilai {$studentGrade} pada {$prereq['course_code']} tidak memenuhi syarat minimal {$minGrade}.";
            }
        }
        return $unmet;
    }

    private static function gradePoints(string $grade): float
    {
        return match (strtoupper(trim($grade))) {
            'A' => 4.00,
            'A-' => 3.75,
            'B+' => 3.50,
            'B' => 3.00,
            'B-' => 2.75,
            'C+' => 2.50,
            'C' => 2.00,
            'D' => 1.00,
            default => 0.00
        };
    }
}
