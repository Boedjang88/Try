<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ObeCpl extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'id',
        'curriculum_id',
        'code',
        'category', // Sikap, Keterampilan Umum, Pengetahuan, Keterampilan Khusus
        'description_id',
        'description_en',
        'order_sequence',
    ];

    protected $casts = [
        'order_sequence' => 'integer',
    ];

    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(Curriculum::class);
    }

    public function cpmks(): HasMany
    {
        return $this->hasMany(ObeCpmk::class);
    }

    public function obeCpmks(): HasMany
    {
        return $this->cpmks();
    }
}
