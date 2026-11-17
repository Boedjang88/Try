<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasUuids, SoftDeletes, Auditable;

    protected $fillable = [
        'id',
        'curriculum_id',
        'study_program_id',
        'code',
        'name_id',
        'name_en',
        'sks',
        'sks_theory',
        'sks_practice',
        'semester_level',
        'is_mandatory',
        'syllabus_path',
        'passing_grade_letter',
        'weight_presence',
        'weight_task',
        'weight_uts',
        'weight_uas',
    ];

    protected $casts = [
        'sks' => 'integer',
        'sks_theory' => 'integer',
        'sks_practice' => 'integer',
        'semester_level' => 'integer',
        'is_mandatory' => 'boolean',
        'weight_presence' => 'decimal:2',
        'weight_task' => 'decimal:2',
        'weight_uts' => 'decimal:2',
        'weight_uas' => 'decimal:2',
    ];

    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(Curriculum::class);
    }

    public function studyProgram(): BelongsTo
    {
        return $this->belongsTo(StudyProgram::class);
    }

    public function prerequisites(): BelongsToMany
    {
        return $this->belongsToMany(
            Course::class,
            'course_prerequisites',
            'course_id',
            'prerequisite_course_id'
        )->withPivot('minimum_grade');
    }

    public function prerequisiteFor(): BelongsToMany
    {
        return $this->belongsToMany(
            Course::class,
            'course_prerequisites',
            'prerequisite_course_id',
            'course_id'
        )->withPivot('minimum_grade');
    }

    public function cpmks(): HasMany
    {
        return $this->hasMany(ObeCpmk::class);
    }

    public function obeCpmks(): HasMany
    {
        return $this->cpmks();
    }

    public function classSections(): HasMany
    {
        return $this->hasMany(ClassSection::class);
    }
}
