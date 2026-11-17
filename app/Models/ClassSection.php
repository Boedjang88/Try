<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassSection extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'id',
        'academic_period_id',
        'course_id',
        'room_id',
        'lecturer_id',
        'section_code',
        'day_of_week', // 1=Senin ... 6=Sabtu
        'start_time',
        'end_time',
        'max_quota',
        'enrolled_count',
        'is_closed',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'max_quota' => 'integer',
        'enrolled_count' => 'integer',
        'is_closed' => 'boolean',
    ];

    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class);
    }

    public function krsItems(): HasMany
    {
        return $this->hasMany(KrsItem::class);
    }

    public function lectureSessions(): HasMany
    {
        return $this->hasMany(LectureSession::class)->orderBy('session_number');
    }

    public function getRemainingSeatsAttribute(): int
    {
        return max(0, $this->max_quota - $this->enrolled_count);
    }

    public function getDayNameAttribute(): string
    {
        $days = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];

        return $days[$this->day_of_week] ?? 'Unknown';
    }
}
