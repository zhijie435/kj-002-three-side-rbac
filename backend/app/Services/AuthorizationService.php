<?php

namespace App\Services;

use App\Enums\GuardType;
use App\Exceptions\BusinessException;
use App\Models\Role;
use App\Models\Permission;

class AuthorizationService
{
    public function checkPermission(string $permissionName, ?string $guard = null): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->is_super_admin ?? false) {
            return true;
        }

        $role = $user->role;

        if (! $role) {
            return false;
        }

        if ($guard && $role->guard !== $guard) {
            return false;
        }

        return $role->hasPermission($permissionName);
    }

    public function checkAnyPermission(array $permissionNames, ?string $guard = null): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->is_super_admin ?? false) {
            return true;
        }

        $role = $user->role;

        if (! $role) {
            return false;
        }

        if ($guard && $role->guard !== $guard) {
            return false;
        }

        return $role->hasAnyPermission($permissionNames);
    }

    public function ensurePermission(string $permissionName, ?string $guard = null): void
    {
        if (! $this->checkPermission($permissionName, $guard)) {
            throw BusinessException::forbidden('没有权限执行此操作');
        }
    }

    public function ensureAnyPermission(array $permissionNames, ?string $guard = null): void
    {
        if (! $this->checkAnyPermission($permissionNames, $guard)) {
            throw BusinessException::forbidden('没有权限执行此操作');
        }
    }

    public function getUserPermissions(?string $guard = null): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        if ($user->is_super_admin ?? false) {
            $query = Permission::query();
            if ($guard) {
                $query->byGuard($guard);
            }

            return $query->pluck('name')->toArray();
        }

        $role = $user->role;

        if (! $role) {
            return [];
        }

        if ($guard && $role->guard !== $guard) {
            return [];
        }

        return $role->permissions()->pluck('name')->toArray();
    }

    public function getUserRole(?string $guard = null): ?Role
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        $role = $user->role;

        if (! $role) {
            return null;
        }

        if ($guard && $role->guard !== $guard) {
            return null;
        }

        return $role;
    }

    public function isSuperAdmin(): bool
    {
        $user = auth()->user();

        return $user ? ($user->is_super_admin ?? false) : false;
    }
}
