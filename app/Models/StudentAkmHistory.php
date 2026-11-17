<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAkmHistory extends Model
{
    use HasUuids, Auditable;

    protected $table = 'student_akm_histories';

    protected $fillable = [
        'id',
        'student_id',
        'academic_period_id',
        'semester_number',
        'sks_semester',
        'sks_total',
        'ips',
        'ipk',
        'status',
    ];

    protected $casts = [
        'semester_number' => 'integer',
        'sks_semester' => 'integer',
        'sks_total' => 'integer',
        'ips' => 'float',
        'ipk' => 'float',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class);
    }
}
