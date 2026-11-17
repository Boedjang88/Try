<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsSubmission extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'id',
        'lms_assignment_id',
        'student_id',
        'submission_file_path',
        'submitted_at',
        'score',
        'feedback',
        'graded_by_lecturer_id',
        'graded_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'score' => 'decimal:2',
        'graded_at' => 'datetime',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(LmsAssignment::class, 'lms_assignment_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function gradedBy(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class, 'graded_by_lecturer_id');
    }
}
