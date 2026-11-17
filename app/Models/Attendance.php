<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'id',
        'lecture_session_id',
        'student_id',
        'attendance_status', // present, absent, late, excused
        'verified_method', // qr_scan, lecturer_manual, geolocation
        'scanned_latitude',
        'scanned_longitude',
        'scanned_at',
        'notes',
    ];

    protected $casts = [
        'scanned_latitude' => 'decimal:8',
        'scanned_longitude' => 'decimal:8',
        'scanned_at' => 'datetime',
    ];

    public function lectureSession(): BelongsTo
    {
        return $this->belongsTo(LectureSession::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
