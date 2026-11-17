<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LectureSession extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'id',
        'class_section_id',
        'session_number',
        'title',
        'session_type', // lecture, uts, uas
        'planned_date',
        'actual_date',
        'room_id',
        'qr_code_token',
        'qr_expires_at',
        'bap_summary',
        'lecturer_signed_at',
    ];

    protected $casts = [
        'session_number' => 'integer',
        'planned_date' => 'date',
        'actual_date' => 'date',
        'qr_expires_at' => 'datetime',
        'lecturer_signed_at' => 'datetime',
    ];

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(LmsAssignment::class);
    }

    public function isQrValid(string $token): bool
    {
        return $this->qr_code_token === $token &&
               $this->qr_expires_at &&
               now()->lessThanOrEqualTo($this->qr_expires_at);
    }
}
