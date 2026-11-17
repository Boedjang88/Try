<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LmsAssignment extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'id',
        'lecture_session_id',
        'title',
        'description',
        'deadline',
        'max_score',
        'attachment_path',
        'is_published',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'max_score' => 'integer',
        'is_published' => 'boolean',
    ];

    public function lectureSession(): BelongsTo
    {
        return $this->belongsTo(LectureSession::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(LmsSubmission::class);
    }

    public function isPastDeadline(): bool
    {
        return now()->greaterThan($this->deadline);
    }
}
