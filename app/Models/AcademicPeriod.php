<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicPeriod extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'id',
        'code',
        'name',
        'semester_type',
        'start_date',
        'end_date',
        'krs_start_date',
        'krs_end_date',
        'krs_start',
        'krs_end',
        'grade_input_start_date',
        'grade_input_end_date',
        'is_active',
        'is_krs_active',
        'is_grading_active',
    ];

    public function setKrsStartAttribute($value): void
    {
        $this->attributes['krs_start_date'] = $value;
    }

    public function setKrsEndAttribute($value): void
    {
        $this->attributes['krs_end_date'] = $value;
    }

    public function getKrsStartAttribute()
    {
        return $this->krs_start_date;
    }

    public function getKrsEndAttribute()
    {
        return $this->krs_end_date;
    }

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'krs_start_date' => 'datetime',
        'krs_end_date' => 'datetime',
        'grade_input_start_date' => 'datetime',
        'grade_input_end_date' => 'datetime',
        'is_active' => 'boolean',
        'is_krs_active' => 'boolean',
        'is_grading_active' => 'boolean',
    ];

    public function classSections(): HasMany
    {
        return $this->hasMany(ClassSection::class);
    }

    public function krsSubmissions(): HasMany
    {
        return $this->hasMany(KrsSubmission::class);
    }

    public function studentInvoices(): HasMany
    {
        return $this->hasMany(StudentInvoice::class);
    }

    public function admissionWaves(): HasMany
    {
        return $this->hasMany(AdmissionWave::class);
    }

    /**
     * Scope: Active Academic Period.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: KRS Window Open.
     */
    public function scopeKrsOpen(Builder $query): Builder
    {
        return $query->where('is_krs_active', true)
                     ->where('krs_start_date', '<=', now())
                     ->where('krs_end_date', '>=', now());
    }

    /**
     * Scope: Grading Window Open.
     */
    public function scopeGradingOpen(Builder $query): Builder
    {
        return $query->where('is_grading_active', true)
                     ->where('grade_input_start_date', '<=', now())
                     ->where('grade_input_end_date', '>=', now());
    }

    /**
     * Check if currently within KRS window.
     */
    public function isKrsWindowOpen(): bool
    {
        return $this->is_krs_active && now()->between($this->krs_start_date, $this->krs_end_date);
    }

    /**
     * Check if currently within Grading window.
     */
    public function isGradingWindowOpen(): bool
    {
        return $this->is_grading_active && now()->between($this->grade_input_start_date, $this->grade_input_end_date);
    }
}
