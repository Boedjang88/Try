<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'id',
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'reason',
        'description',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($log) {
            if (empty($log->auditable_type)) {
                $log->auditable_type = 'System';
            }
            if (empty($log->auditable_id)) {
                $log->auditable_id = '0';
            }
            if (empty($log->created_at)) {
                $log->created_at = now();
            }
        });
    }

    public function setDescriptionAttribute($value): void
    {
        $this->attributes['reason'] = $value;
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->attributes['reason'] ?? null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
