<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MbkmConversion extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'id',
        'mbkm_program_id',
        'course_id',
        'equivalent_sks',
        'converted_numeric_score',
        'converted_letter_grade',
        'grade_point',
        'krs_item_id',
    ];

    protected $casts = [
        'equivalent_sks' => 'integer',
        'converted_numeric_score' => 'decimal:2',
        'grade_point' => 'decimal:2',
    ];

    public function mbkmProgram(): BelongsTo
    {
        return $this->belongsTo(MbkmProgram::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function krsItem(): BelongsTo
    {
        return $this->belongsTo(KrsItem::class);
    }
}
