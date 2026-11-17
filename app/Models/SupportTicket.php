<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'id',
        'ticket_number',
        'user_id',
        'department',
        'category',
        'subject',
        'priority',
        'status',
        'sla_due_at',
        'resolved_at',
        'assigned_to_user_id',
    ];

    protected $casts = [
        'sla_due_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class, 'ticket_id')->orderBy('created_at', 'asc');
    }

    /**
     * Determine SLA hours according to priority.
     */
    public static function getSlaHours(string $priority): int
    {
        return match (strtolower($priority)) {
            'urgent' => 12,
            'high' => 24,
            'medium' => 48,
            'low' => 72,
            default => 48,
        };
    }

    public function isSlaOverdue(): bool
    {
        if (in_array($this->status, ['resolved', 'closed'])) {
            return false;
        }
        return $this->sla_due_at && now()->isAfter($this->sla_due_at);
    }
}
