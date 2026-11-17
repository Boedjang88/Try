<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeAppeal extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'id',
        'krs_item_id',
        'student_id',
        'reason',
        'evidence_file_path',
        'status', // submitted, reviewed_by_lecturer, unlock_requested, approved, rejected
        'lecturer_response',
        'lecturer_reviewed_at',
        'kaprodi_reviewed_at',
    ];

    protected $casts = [
        'lecturer_reviewed_at' => 'datetime',
        'kaprodi_reviewed_at' => 'datetime',
    ];

    public function krsItem(): BelongsTo
    {
        return $this->belongsTo(KrsItem::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
