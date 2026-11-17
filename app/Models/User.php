<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasUuids, SoftDeletes, Notifiable, Auditable;

    protected $fillable = [
        'id',
        'username',
        'email',
        'password',
        'full_name',
        'role', // superadmin, applicant, student, lecturer, kaprodi, dean, baak, bauk, rector
        'role_id',
        'faculty_id', // ABAC Scope
        'study_program_id', // ABAC Scope
        'phone_number',
        'gender',
        'birth_date',
        'birth_place',
        'avatar_path',
        'is_active',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'birth_date' => 'date',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function studyProgram(): BelongsTo
    {
        return $this->belongsTo(StudyProgram::class);
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function lecturer(): HasOne
    {
        return $this->hasOne(Lecturer::class);
    }

    public function inAppNotifications(): HasMany
    {
        return $this->hasMany(InAppNotification::class)->latest();
    }

    public function applicant(): HasOne
    {
        return $this->hasOne(Applicant::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Primary Role Attribute Accessor.
     */
    public function getPrimaryRoleAttribute(): string
    {
        return $this->role ?? 'student';
    }

    /**
     * RBAC Role Helper.
     */
    public function hasRole(string|array $roles): bool
    {
        if ($this->role === 'superadmin') {
            return true;
        }

        if (is_array($roles)) {
            return in_array($this->role, $roles, true);
        }

        return $this->role === $roles;
    }

    public function roleModel(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function customPermissions(): HasMany
    {
        return $this->hasMany(UserCustomPermission::class);
    }

    public function getScopedFacultyIdAttribute(): ?string
    {
        return $this->faculty_id;
    }

    public function getScopedStudyProgramIdAttribute(): ?string
    {
        return $this->study_program_id;
    }

    /**
     * Stage 15 Granular Privilege Resolution Engine.
     * Evaluates Superadmin bypass -> User Custom Overrides -> Role-Permission Matrix.
     */
    public function hasPrivilege(string $slug): bool
    {
        // 1. Superadmin has absolute IAM bypass (Level 100)
        if ($this->role === 'superadmin') {
            return true;
        }

        if ($this->roleModel && $this->roleModel->level >= 100) {
            return true;
        }

        // 2. Per-User Custom Privilege Override check (Explicit Grant or Revoke)
        $custom = $this->customPermissions()
            ->whereHas('permission', fn($q) => $q->where('slug', $slug))
            ->first();

        if ($custom !== null) {
            return (bool) $custom->is_granted;
        }

        // 3. Fallback to Role-Permission Matrix
        $roleObj = $this->roleModel ?? Role::where('name', $this->role)->first();
        if ($roleObj) {
            return $roleObj->hasPermission($slug);
        }

        return false;
    }

    /**
     * Grant a custom privilege override to this user.
     */
    public function grantPrivilege(string $slug, ?string $grantedBy = null, ?string $reason = null): UserCustomPermission
    {
        $permission = Permission::where('slug', $slug)->firstOrFail();

        return UserCustomPermission::updateOrCreate([
            'user_id' => $this->id,
            'permission_id' => $permission->id,
        ], [
            'is_granted' => true,
            'granted_by' => $grantedBy,
            'reason' => $reason,
        ]);
    }

    /**
     * Explicitly revoke a privilege from this user.
     */
    public function revokePrivilege(string $slug, ?string $grantedBy = null, ?string $reason = null): UserCustomPermission
    {
        $permission = Permission::where('slug', $slug)->firstOrFail();

        return UserCustomPermission::updateOrCreate([
            'user_id' => $this->id,
            'permission_id' => $permission->id,
        ], [
            'is_granted' => false,
            'granted_by' => $grantedBy,
            'reason' => $reason,
        ]);
    }

    /**
     * ABAC Faculty Scope Verification.
     */
    public function canAccessFaculty(?string $targetFacultyId): bool
    {
        if (in_array($this->role, ['superadmin', 'rector', 'baak', 'bauk'], true)) {
            return true;
        }

        if ($this->role === 'dean') {
            return $this->faculty_id === $targetFacultyId;
        }

        return $this->faculty_id === $targetFacultyId;
    }

    /**
     * ABAC Study Program Scope Verification.
     */
    public function canAccessStudyProgram(?string $targetProdiId): bool
    {
        if (in_array($this->role, ['superadmin', 'rector', 'baak', 'bauk'], true)) {
            return true;
        }

        if ($this->role === 'dean' && $targetProdiId) {
            $prodi = StudyProgram::find($targetProdiId);
            return $prodi && $prodi->faculty_id === $this->faculty_id;
        }

        return $this->study_program_id === $targetProdiId;
    }
}
