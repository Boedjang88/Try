<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Curriculum extends Model
{
    use HasUuids, Auditable;

    protected $table = 'curriculums';

    protected $fillable = [
        'id',
        'study_program_id',
        'code',
        'name',
        'start_year',
        'end_year',
        'total_sks',
        'mandatory_sks',
        'elective_sks',
        'is_obe_compliant',
        'is_active',
        'is_locked',
    ];

    protected $casts = [
        'start_year' => 'integer',
        'end_year' => 'integer',
        'total_sks' => 'integer',
        'mandatory_sks' => 'integer',
        'elective_sks' => 'integer',
        'is_obe_compliant' => 'boolean',
        'is_active' => 'boolean',
        'is_locked' => 'boolean',
    ];

    public function studyProgram(): BelongsTo
    {
        return $this->belongsTo(StudyProgram::class);
    }

    public function cpls(): HasMany
    {
        return $this->hasMany(ObeCpl::class)->orderBy('order_sequence');
    }

    public function obeCpls(): HasMany
    {
        return $this->cpls();
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }
}
