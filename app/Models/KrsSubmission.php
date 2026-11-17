<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KrsSubmission extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'id',
        'student_id',
        'academic_period_id',
        'total_sks',
        'status', // draft, submitted, approved, rejected
        'rejection_note',
        'approved_by_lecturer_id',
        'approved_at',
        'qr_token',
    ];

    protected $casts = [
        'total_sks' => 'integer',
        'approved_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class);
    }

    public function approvedByLecturer(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class, 'approved_by_lecturer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(KrsItem::class);
    }

    public function krsItems(): HasMany
    {
        return $this->hasMany(KrsItem::class);
    }
}
