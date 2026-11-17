<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThesisGuidanceLog extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'id',
        'thesis_id',
        'lecturer_id',
        'guidance_date',
        'discussion_summary',
        'student_notes',
        'supervisor_feedback',
        'is_verified',
        'verified_at',
    ];

    protected $casts = [
        'guidance_date' => 'date',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function thesis(): BelongsTo
    {
        return $this->belongsTo(Thesis::class);
    }

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class);
    }
}
