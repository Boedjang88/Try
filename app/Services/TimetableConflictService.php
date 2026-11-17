<?php

namespace App\Services;

use App\Models\ClassSection;
use App\Models\KrsItem;
use App\Models\KrsSubmission;
use Illuminate\Support\Collection;

/**
 * Enterprise Timetable Conflict Resolver Service
 * High-precision detection of Room, Lecturer, and Student Cohort schedule clashes.
 */
class TimetableConflictService
{
    /**
     * Check if two time intervals overlap on the same day.
     * Interval [A, B] and [C, D] overlap if max(A, C) < min(B, D).
     */
    public function isTimeOverlapping(string $startA, string $endA, string $startB, string $endB): bool
    {
        $timeStartA = strtotime($startA);
        $timeEndA = strtotime($endA);
        $timeStartB = strtotime($startB);
        $timeEndB = strtotime($endB);

        return max($timeStartA, $timeStartB) < min($timeEndA, $timeEndB);
    }

    /**
     * Check if a room is already occupied during the requested timeslot.
     */
    public function hasRoomClash(
        string $academicPeriodId,
        string $roomId,
        int $dayOfWeek,
        string $startTime,
        string $endTime,
        ?string $excludeSectionId = null
    ): ?ClassSection {
        $existingSections = ClassSection::where('academic_period_id', $academicPeriodId)
            ->where('room_id', $roomId)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_closed', false)
            ->when($excludeSectionId, fn($q) => $q->where('id', '!=', $excludeSectionId))
            ->with(['course', 'lecturer.user', 'room'])
            ->get();

        foreach ($existingSections as $section) {
            if ($this->isTimeOverlapping($startTime, $endTime, $section->start_time, $section->end_time)) {
                return $section;
            }
        }

        return null;
    }

    /**
     * Check if a lecturer is already assigned to teach during the requested timeslot.
     */
    public function hasLecturerClash(
        string $academicPeriodId,
        string $lecturerId,
        int $dayOfWeek,
        string $startTime,
        string $endTime,
        ?string $excludeSectionId = null
    ): ?ClassSection {
        $existingSections = ClassSection::where('academic_period_id', $academicPeriodId)
            ->where('lecturer_id', $lecturerId)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_closed', false)
            ->when($excludeSectionId, fn($q) => $q->where('id', '!=', $excludeSectionId))
            ->with(['course', 'room'])
            ->get();

        foreach ($existingSections as $section) {
            if ($this->isTimeOverlapping($startTime, $endTime, $section->start_time, $section->end_time)) {
                return $section;
            }
        }

        return null;
    }

    /**
     * Check if a student's existing KRS selections clash with a candidate class section.
     */
    public function hasStudentClash(string $studentId, string $targetClassSectionId, string $academicPeriodId): ?ClassSection
    {
        $targetSection = ClassSection::with('course')->findOrFail($targetClassSectionId);

        // Fetch student's currently selected class sections in the current academic period
        $activeSubmission = KrsSubmission::where('student_id', $studentId)
            ->where('academic_period_id', $academicPeriodId)
            ->whereIn('status', ['draft', 'submitted', 'approved'])
            ->first();

        if (!$activeSubmission) {
            return null;
        }

        $enrolledSections = ClassSection::whereIn('id', function ($query) use ($activeSubmission) {
            $query->select('class_section_id')
                ->from('krs_items')
                ->where('krs_submission_id', $activeSubmission->id);
        })
        ->where('day_of_week', $targetSection->day_of_week)
        ->where('id', '!=', $targetClassSectionId)
        ->with('course')
        ->get();

        foreach ($enrolledSections as $section) {
            if ($this->isTimeOverlapping($targetSection->start_time, $targetSection->end_time, $section->start_time, $section->end_time)) {
                return $section;
            }
        }

        return null;
    }

    /**
     * Comprehensive validation of a class section timetable allocation.
     */
    public function validateSectionSchedule(
        string $academicPeriodId,
        string $roomId,
        string $lecturerId,
        int $dayOfWeek,
        string $startTime,
        string $endTime,
        ?string $excludeSectionId = null
    ): array {
        $conflicts = [];

        $roomClash = $this->hasRoomClash($academicPeriodId, $roomId, $dayOfWeek, $startTime, $endTime, $excludeSectionId);
        if ($roomClash) {
            $conflicts[] = [
                'type' => 'ROOM_CLASH',
                'message' => "Ruang {$roomClash->room->code} telah terisi oleh kelas {$roomClash->course->code} ({$roomClash->section_code}) pada pukul {$roomClash->start_time} - {$roomClash->end_time}.",
                'conflicting_section' => $roomClash,
            ];
        }

        $lecturerClash = $this->hasLecturerClash($academicPeriodId, $lecturerId, $dayOfWeek, $startTime, $endTime, $excludeSectionId);
        if ($lecturerClash) {
            $conflicts[] = [
                'type' => 'LECTURER_CLASH',
                'message' => "Dosen pengampu memiliki jadwal mengajar paralel di kelas {$lecturerClash->course->code} ({$lecturerClash->section_code}) pada jam yang sama.",
                'conflicting_section' => $lecturerClash,
            ];
        }

        return [
            'has_conflict' => !empty($conflicts),
            'conflicts' => $conflicts,
        ];
    }
}
