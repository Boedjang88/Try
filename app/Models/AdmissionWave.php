<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdmissionWave extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'id',
        'academic_period_id',
        'name',
        'wave_number',
        'start_date',
        'end_date',
        'registration_fee',
        'cbt_start_time',
        'cbt_end_time',
        'is_active',
    ];

    protected $casts = [
        'wave_number' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'registration_fee' => 'decimal:2',
        'cbt_start_time' => 'datetime',
        'cbt_end_time' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class);
    }

    public function applicants(): HasMany
    {
        return $this->hasMany(Applicant::class);
    }

    public function isRegistrationOpen(): bool
    {
        return $this->is_active && now()->between($this->start_date->startOfDay(), $this->end_date->endOfDay());
    }
}
