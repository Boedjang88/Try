<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicLeaveRequest extends Model
{
    use HasUuids, Auditable;

    protected $table = 'academic_leave_requests';

    protected $fillable = [
        'id',
        'student_id',
        'academic_period_id',
        'reason',
        'document_path',
        'status',
        'approved_by_advisor_id',
        'approved_by_kaprodi_id',
        'rejection_reason',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class);
    }

    public function advisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_advisor_id');
    }

    public function kaprodi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_kaprodi_id');
    }
}
