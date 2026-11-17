<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TracerStudy extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'id',
        'student_id',
        'graduation_year',
        'employment_status',
        'company_name',
        'job_title',
        'waiting_time_months',
        'monthly_income_range',
        'job_major_alignment',
        'submitted_at',
    ];

    protected $casts = [
        'graduation_year' => 'integer',
        'waiting_time_months' => 'integer',
        'submitted_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
