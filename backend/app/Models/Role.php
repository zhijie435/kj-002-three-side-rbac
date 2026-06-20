<?php

namespace App\Models;

use App\Enums\RoleStatus;
use App\Traits\HasGuardScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory, SoftDeletes, HasGuardScopes;

    protected $fillable = [
        'name',
        'guard',
        'display_name',
        'description',
        'is_system',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'status' => 'boolean',
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function syncPermissions(array $permissionIds): void
    {
        $this->permissions()->sync($permissionIds);
    }

    public function hasPermission(string $permissionName): bool
    {
        return $this->permissions()->where('name', $permissionName)->exists();
    }

    public function hasAnyPermission(array $permissionNames): bool
    {
        return $this->permissions()->whereIn('name', $permissionNames)->exists();
    }

    public function getStatusEnum(): RoleStatus
    {
        return RoleStatus::fromBoolean($this->status);
    }

    public function isActive(): bool
    {
        return $this->getStatusEnum()->isActive();
    }

    public function activate(): void
    {
        $this->status = true;
        $this->save();
    }

    public function deactivate(): void
    {
        $this->status = false;
        $this->save();
    }

    public function toggleStatus(): bool
    {
        $this->status = ! $this->status;
        $this->save();

        return $this->status;
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', false);
    }

    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('id', 'desc');
    }

    public function canBeModified(): bool
    {
        return ! $this->is_system;
    }

    public function canBeDeleted(): bool
    {
        return ! $this->is_system;
    }
}
