<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Permission extends Model
{
    use HasUuids;

    protected $fillable = [
        'id',
        'slug',
        'name',
        'module',
        'description',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permission', 'permission_id', 'role_id')
            ->withTimestamps();
    }

    public function userCustomPermissions(): HasMany
    {
        return $this->hasMany(UserCustomPermission::class);
    }
}
