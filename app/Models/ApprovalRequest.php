<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalRequest extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'id',
        'request_code',
        'workflow_type',
        'maker_user_id',
        'checker_role_required',
        'target_model_type',
        'target_model_id',
        'payload_diff',
        'maker_reason',
        'status',
        'checker_user_id',
        'checker_notes',
        'resolved_at',
    ];

    protected $casts = [
        'payload_diff' => 'array',
        'resolved_at' => 'datetime',
    ];

    public function maker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'maker_user_id');
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checker_user_id');
    }

    /**
     * Resolve target model instance.
     */
    public function getTargetModel()
    {
        if (class_exists($this->target_model_type)) {
            return $this->target_model_type::find($this->target_model_id);
        }
        return null;
    }

    /**
     * Check if current user is allowed to act as checker for this request.
     */
    public function canBeCheckedBy(User $user): bool
    {
        // Maker cannot approve own request (Zero-Trust Dual-Control)
        if ($this->maker_user_id === $user->id) {
            return false;
        }

        $userRole = $user->primary_role ?? $user->role;
        if ($userRole === 'superadmin') {
            return true;
        }

        return $userRole === $this->checker_role_required;
    }
}
