<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasUuids, SoftDeletes, Auditable;

    protected $fillable = [
        'id',
        'user_id',
        'study_program_id',
        'academic_advisor_id',
        'nim',
        'entry_year',
        'entry_semester',
        'current_semester',
        'max_sks_allowed',
        'current_gpa_ipk',
        'last_semester_gpa_ips',
        'total_sks_completed',
        'academic_status', // active, on_leave, probation, graduated, dropped_out
        'is_financially_cleared',
        'has_academic_hold',
    ];

    protected $casts = [
        'entry_year' => 'integer',
        'current_semester' => 'integer',
        'max_sks_allowed' => 'integer',
        'current_gpa_ipk' => 'decimal:2',
        'last_semester_gpa_ips' => 'decimal:2',
        'total_sks_completed' => 'integer',
        'is_financially_cleared' => 'boolean',
        'has_academic_hold' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function studyProgram(): BelongsTo
    {
        return $this->belongsTo(StudyProgram::class);
    }

    public function academicAdvisor(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class, 'academic_advisor_id');
    }

    public function krsSubmissions(): HasMany
    {
        return $this->hasMany(KrsSubmission::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function lmsSubmissions(): HasMany
    {
        return $this->hasMany(LmsSubmission::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(StudentInvoice::class);
    }

    public function thesis(): HasOne
    {
        return $this->hasOne(Thesis::class);
    }

    public function yudisiumClearance(): HasOne
    {
        return $this->hasOne(YudisiumClearance::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(AcademicLeaveRequest::class);
    }

    public function akmHistories(): HasMany
    {
        return $this->hasMany(StudentAkmHistory::class);
    }

    public function mbkmPrograms(): HasMany
    {
        return $this->hasMany(MbkmProgram::class);
    }

    public function gradeAppeals(): HasMany
    {
        return $this->hasMany(GradeAppeal::class);
    }

    public function libraryLoans(): HasMany
    {
        return $this->hasMany(LibraryLoan::class);
    }

    public function facilityBookings(): HasMany
    {
        return $this->hasMany(FacilityBooking::class);
    }

    public function tracerStudies(): HasMany
    {
        return $this->hasMany(TracerStudy::class);
    }

    /**
     * Recalculate Cumulative GPA (IPK) using highest grade (Nilai Terbaik) for retaken courses.
     */
    public function recalculateCumulativeGpa(): float
    {
        $allApprovedItems = KrsItem::whereHas('krsSubmission', function ($q) {
            $q->where('student_id', $this->id);
        })
        ->whereNotNull('grade_point')
        ->with('classSection.course')
        ->get();

        $coursesGraded = [];
        foreach ($allApprovedItems as $item) {
            if (!$item->classSection || !$item->classSection->course) continue;
            $courseId = $item->classSection->course_id;
            $gradePt = (float)$item->grade_point;
            $sks = (int)$item->classSection->course->sks;

            if (!isset($coursesGraded[$courseId]) || $coursesGraded[$courseId]['point'] < $gradePt) {
                $coursesGraded[$courseId] = [
                    'point' => $gradePt,
                    'sks' => $sks,
                ];
            }
        }

        $cumulativeSks = 0;
        $cumulativePoints = 0.0;
        foreach ($coursesGraded as $cg) {
            $cumulativeSks += $cg['sks'];
            $cumulativePoints += ($cg['point'] * $cg['sks']);
        }

        $ipk = $cumulativeSks > 0 ? round($cumulativePoints / $cumulativeSks, 2) : 0.00;
        $this->current_gpa_ipk = $ipk;
        $this->total_sks_completed = $cumulativeSks;
        $this->save();

        return $ipk;
    }

    /**
     * Early Warning System (EWS) Evaluation.
     */
    public function getEwsStatusAttribute(): array
    {
        $status = 'NORMAL';
        $warnings = [];

        if ($this->current_gpa_ipk < 2.00) {
            $status = 'CRITICAL';
            $warnings[] = 'IPK di bawah standar kelulusan minimum (< 2.00)';
        } elseif ($this->current_gpa_ipk < 2.50) {
            $status = 'WARNING';
            $warnings[] = 'IPK dalam kategori pengawasan akademik (< 2.50)';
        }

        if ($this->current_semester >= 12 && $this->total_sks_completed < 144) {
            $status = 'CRITICAL';
            $warnings[] = 'Mendekati batas masa studi maksimum (Semester ' . $this->current_semester . ')';
        }

        return [
            'status' => $status,
            'warnings' => $warnings,
            'is_probation' => $this->academic_status === 'probation' || $status === 'CRITICAL',
        ];
    }
}
