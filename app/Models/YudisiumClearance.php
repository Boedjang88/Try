<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YudisiumClearance extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'id',
        'student_id',
        'academic_period_id',
        'finance_cleared',
        'library_cleared',
        'lab_cleared',
        'repository_cleared',
        'faculty_cleared',
        'national_pin_number',
        'skpi_document_path',
        'transcript_document_path',
        'graduation_date',
        'status', // pending, approved, rejected
        'approved_by_user_id',
    ];

    protected $casts = [
        'finance_cleared' => 'boolean',
        'library_cleared' => 'boolean',
        'lab_cleared' => 'boolean',
        'repository_cleared' => 'boolean',
        'faculty_cleared' => 'boolean',
        'graduation_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class);
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function isFullyCleared(): bool
    {
        return $this->finance_cleared &&
               $this->library_cleared &&
               $this->repository_cleared &&
               $this->faculty_cleared;
    }
}
