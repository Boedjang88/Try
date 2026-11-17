<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Applicant extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'id',
        'user_id',
        'admission_wave_id',
        'target_study_program_id',
        'registration_number',
        'va_number',
        'payment_status', // unpaid, paid, expired
        'payment_amount',
        'paid_at',
        'cbt_score',
        'scholarship_tier', // Tier 1 (100%), Tier 2 (75%), Tier 3 (50%), Tier 4 (25%), Regular (0%)
        'admission_status', // registered, paid, exam_taken, passed, rejected, enrolled
        'generated_nim',
        'document_identity_path',
        'document_certificate_path',
        'enrolled_at',
    ];

    protected $casts = [
        'payment_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'cbt_score' => 'decimal:2',
        'enrolled_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admissionWave(): BelongsTo
    {
        return $this->belongsTo(AdmissionWave::class);
    }

    public function targetStudyProgram(): BelongsTo
    {
        return $this->belongsTo(StudyProgram::class, 'target_study_program_id');
    }

    public function studyProgram(): BelongsTo
    {
        return $this->targetStudyProgram();
    }

    /**
     * Compute scholarship tier based on CBT exam score.
     */
    public function computeScholarshipTier(): string
    {
        $score = (float)$this->cbt_score;

        if ($score >= 90.0) {
            return 'Tier 1 (100%)';
        }
        if ($score >= 80.0) {
            return 'Tier 2 (75%)';
        }
        if ($score >= 70.0) {
            return 'Tier 3 (50%)';
        }
        if ($score >= 60.0) {
            return 'Tier 4 (25%)';
        }

        return 'Regular (0%)';
    }
}
