<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MbkmProgram extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'id',
        'student_id',
        'academic_period_id',
        'program_type',
        'partner_name',
        'partner_address',
        'mentor_name',
        'mentor_contact',
        'start_date',
        'end_date',
        'logbook_document_path',
        'status',
        'approval_notes',
        'approved_by_kaprodi_id',
        'approved_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
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

    public function approvedByKaprodi(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class, 'approved_by_kaprodi_id');
    }

    public function conversions(): HasMany
    {
        return $this->hasMany(MbkmConversion::class);
    }

    public function getTotalConvertedSksAttribute(): int
    {
        return (int) $this->conversions()->sum('equivalent_sks');
    }
}
