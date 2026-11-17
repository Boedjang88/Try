<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

/**
 * Enterprise Auditable Trait
 * Automatically records model state changes and provides explicit transactional audit logging.
 */
trait Auditable
{
    /**
     * Boot the Auditable trait for the model.
     */
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            $model->logAuditAction('CREATED', null, $model->getAuditableAttributes());
        });

        static::updated(function ($model) {
            $oldValues = array_intersect_key($model->getOriginal(), $model->getDirty());
            $newValues = $model->getDirty();

            // Exclude non-meaningful or sensitive changes
            $ignored = $model->getAuditIgnoredColumns();
            foreach ($ignored as $key) {
                unset($oldValues[$key], $newValues[$key]);
            }

            if (!empty($newValues)) {
                $model->logAuditAction('UPDATED', $oldValues, $newValues);
            }
        });

        static::deleted(function ($model) {
            $model->logAuditAction('DELETED', $model->getAuditableAttributes(), null);
        });
    }

    /**
     * Explicitly record a business domain audit log (e.g., KRS_APPROVED, GRADE_LOCKED, INVOICE_PAID).
     */
    public function recordAudit(
        string $action,
        ?string $reason = null,
        ?array $customOld = null,
        ?array $customNew = null
    ): void {
        $this->logAuditAction($action, $customOld, $customNew, $reason);
    }

    /**
     * Core logging worker that persists state changes into the audit_logs table.
     */
    protected function logAuditAction(
        string $action,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $reason = null
    ): void {
        try {
            $userId = Auth::id() ?? null;
            $ipAddress = Request::ip() ?? '127.0.0.1';
            $userAgent = Request::userAgent() ?? 'CLI/System';

            DB::table('audit_logs')->insert([
                'user_id' => $userId,
                'action' => strtoupper($action),
                'auditable_type' => static::class,
                'auditable_id' => (string)$this->getKey(),
                'old_values' => $oldValues ? json_encode($this->sanitizeAttributes($oldValues)) : null,
                'new_values' => $newValues ? json_encode($this->sanitizeAttributes($newValues)) : null,
                'reason' => $reason,
                'ip_address' => $ipAddress,
                'user_agent' => substr($userAgent, 0, 500),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Fail gracefully to prevent interrupting primary transaction, but log error to stderr
            report($e);
        }
    }

    /**
     * Extract attributes to include in audit logs.
     */
    protected function getAuditableAttributes(): array
    {
        return $this->sanitizeAttributes($this->attributesToArray());
    }

    /**
     * Columns that should be ignored during audit logging.
     */
    protected function getAuditIgnoredColumns(): array
    {
        return [
            'password',
            'remember_token',
            'updated_at',
            'created_at',
            'deleted_at'
        ];
    }

    /**
     * Remove sensitive attributes before persisting audit payloads.
     */
    protected function sanitizeAttributes(array $attributes): array
    {
        $sensitiveKeys = ['password', 'remember_token', 'token', 'secret'];
        foreach ($sensitiveKeys as $key) {
            if (array_key_exists($key, $attributes)) {
                $attributes[$key] = '***REDACTED***';
            }
        }
        return $attributes;
    }
}
