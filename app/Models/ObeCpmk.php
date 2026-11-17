<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObeCpmk extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'id',
        'course_id',
        'obe_cpl_id',
        'code',
        'description',
        'weight_percentage',
        'assessment_metric',
    ];

    protected $casts = [
        'weight_percentage' => 'decimal:2',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function cpl(): BelongsTo
    {
        return $this->belongsTo(ObeCpl::class, 'obe_cpl_id');
    }
}
