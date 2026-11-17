<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentStudyPlan extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'student_id', 'academic_year_id',
        'max_sks_allowed', 'total_sks_taken',
        'status', 'notes_from_pa', 'approved_at',
        'approved_by_dosen_pa_id', 'digital_krs_qr_hash'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(StudyPlanItem::class);
    }
}
