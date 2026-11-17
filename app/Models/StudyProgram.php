<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudyProgram extends Model
{
    use HasUuids, SoftDeletes, Auditable;

    protected $fillable = [
        'id',
        'faculty_id',
        'pddikti_code',
        'code',
        'name_id',
        'name_en',
        'degree_level',
        'title_abbr',
        'head_of_program_id',
        'accreditation_grade',
        'accreditation_sk_number',
        'accreditation_expiry_date',
        'standard_study_period_semesters',
        'minimum_graduation_sks',
        'is_active',
    ];

    protected $casts = [
        'accreditation_expiry_date' => 'date',
        'standard_study_period_semesters' => 'integer',
        'minimum_graduation_sks' => 'integer',
        'is_active' => 'boolean',
    ];

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function headOfProgram(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_of_program_id');
    }

    public function lecturers(): HasMany
    {
        return $this->hasMany(Lecturer::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function curriculums(): HasMany
    {
        return $this->hasMany(Curriculum::class);
    }

    public function curricula(): HasMany
    {
        return $this->curriculums();
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function obeCpls(): HasManyThrough
    {
        return $this->hasManyThrough(ObeCpl::class, Curriculum::class, 'study_program_id', 'curriculum_id', 'id', 'id')->orderBy('order_sequence');
    }
}
