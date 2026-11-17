<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KrsItem extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'id',
        'krs_submission_id',
        'class_section_id',
        'score_presence',
        'score_task',
        'score_quiz',
        'score_uts',
        'score_uas',
        'score_project',
        'final_numeric_score',
        'letter_grade',
        'grade_point',
        'is_locked',
        'edom_completed',
        'is_retake',
        'is_exam_blocked',
        'exam_unblocked_reason',
        'attendance_percentage',
        'exam_seat_number',
    ];

    protected $casts = [
        'score_presence' => 'decimal:2',
        'score_task' => 'decimal:2',
        'score_quiz' => 'decimal:2',
        'score_uts' => 'decimal:2',
        'score_uas' => 'decimal:2',
        'score_project' => 'decimal:2',
        'final_numeric_score' => 'decimal:2',
        'grade_point' => 'decimal:2',
        'attendance_percentage' => 'decimal:2',
        'is_locked' => 'boolean',
        'edom_completed' => 'boolean',
        'is_retake' => 'boolean',
        'is_exam_blocked' => 'boolean',
    ];

    public function krsSubmission(): BelongsTo
    {
        return $this->belongsTo(KrsSubmission::class);
    }

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class);
    }

    public function gradeAppeals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GradeAppeal::class);
    }

    /**
     * Compute Final Numeric Score based on Course grading weights.
     */
    public function computeFinalScore(): float
    {
        $course = $this->classSection->course;

        $wPresence = (float)($course->weight_presence ?? 10.00);
        $wTask = (float)($course->weight_task ?? 20.00);
        $wUts = (float)($course->weight_uts ?? 30.00);
        $wUas = (float)($course->weight_uas ?? 40.00);

        $totalWeight = $wPresence + $wTask + $wUts + $wUas;
        if ($totalWeight <= 0) $totalWeight = 100.0;

        $final = (
            ((float)$this->score_presence * $wPresence) +
            ((float)$this->score_task * $wTask) +
            ((float)$this->score_uts * $wUts) +
            ((float)$this->score_uas * $wUas)
        ) / $totalWeight;

        return round($final, 2);
    }

    /**
     * Convert final numeric score to Indonesian standard Letter Grade and Grade Point (0.00 - 4.00).
     */
    public static function convertToGradePoint(float $numericScore): array
    {
        if ($numericScore >= 85.00) {
            return ['letter' => 'A', 'point' => 4.00];
        }
        if ($numericScore >= 80.00) {
            return ['letter' => 'A-', 'point' => 3.70];
        }
        if ($numericScore >= 75.00) {
            return ['letter' => 'B+', 'point' => 3.30];
        }
        if ($numericScore >= 70.00) {
            return ['letter' => 'B', 'point' => 3.00];
        }
        if ($numericScore >= 65.00) {
            return ['letter' => 'B-', 'point' => 2.70];
        }
        if ($numericScore >= 60.00) {
            return ['letter' => 'C+', 'point' => 2.30];
        }
        if ($numericScore >= 55.00) {
            return ['letter' => 'C', 'point' => 2.00];
        }
        if ($numericScore >= 40.00) {
            return ['letter' => 'D', 'point' => 1.00];
        }

        return ['letter' => 'E', 'point' => 0.00];
    }
}
