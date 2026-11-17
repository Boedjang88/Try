<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Thesis extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'id',
        'student_id',
        'title',
        'abstract',
        'supervisor1_lecturer_id',
        'supervisor2_lecturer_id',
        'examiner1_lecturer_id',
        'examiner2_lecturer_id',
        'proposal_status', // pending, approved, rejected
        'defense_status', // not_ready, scheduled, passed, revisions_needed, failed
        'defense_date',
        'rubric_scores',
        'final_score',
        'letter_grade',
        'repository_url',
    ];

    protected $casts = [
        'defense_date' => 'datetime',
        'rubric_scores' => 'array',
        'final_score' => 'decimal:2',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function supervisor1(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class, 'supervisor1_lecturer_id');
    }

    public function supervisor2(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class, 'supervisor2_lecturer_id');
    }

    public function examiner1(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class, 'examiner1_lecturer_id');
    }

    public function examiner2(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class, 'examiner2_lecturer_id');
    }

    public function guidanceLogs(): HasMany
    {
        return $this->hasMany(ThesisGuidanceLog::class)->orderBy('guidance_date', 'desc');
    }

    public function verifiedGuidanceCount(): int
    {
        return $this->guidanceLogs()->where('is_verified', true)->count();
    }

    public function isEligibleForDefense(): bool
    {
        return $this->proposal_status === 'approved' && $this->verifiedGuidanceCount() >= 8;
    }
}
