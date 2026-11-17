<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lecturer extends Model
{
    use HasUuids, SoftDeletes, Auditable;

    protected $fillable = [
        'id',
        'user_id',
        'nidn',
        'nip',
        'title_prefix',
        'title_suffix',
        'study_program_id',
        'academic_rank',
        'employment_status',
        'max_supervision_quota',
        'is_active',
    ];

    protected $casts = [
        'max_supervision_quota' => 'integer',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function studyProgram(): BelongsTo
    {
        return $this->belongsTo(StudyProgram::class);
    }

    public function advisees(): HasMany
    {
        return $this->hasMany(Student::class, 'academic_advisor_id');
    }

    public function classSections(): HasMany
    {
        return $this->hasMany(ClassSection::class);
    }

    public function thesesAsSupervisor1(): HasMany
    {
        return $this->hasMany(Thesis::class, 'supervisor1_lecturer_id');
    }

    public function thesesAsSupervisor2(): HasMany
    {
        return $this->hasMany(Thesis::class, 'supervisor2_lecturer_id');
    }

    public function guidanceLogs(): HasMany
    {
        return $this->hasMany(ThesisGuidanceLog::class);
    }

    public function getFormattedNameAttribute(): string
    {
        $prefix = $this->title_prefix ? $this->title_prefix . ' ' : '';
        $suffix = $this->title_suffix ? ', ' . $this->title_suffix : '';
        return $prefix . ($this->user->full_name ?? '') . $suffix;
    }
}
