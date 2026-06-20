<?php

namespace App\Services;

use App\Enums\GuardType;
use App\Enums\RoleStatus;
use App\Exceptions\BusinessException;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class RoleService
{
    public function getRoleList(array $filters = [], int $perPage = 15): array
    {
        $query = Role::with(['permissions:id,name,display_name,group,guard'])
            ->withCount(['permissions as permission_count'])
            ->ordered();

        $this->applyFilters($query, $filters);

        $paginator = $query->paginate($perPage);
        $stats = $this->getStats($filters);

        return [
            'paginator' => $paginator,
            'stats' => $stats,
        ];
    }

    public function getAllRoles(?string $guard = null): array
    {
        $query = Role::active()->ordered();

        if ($guard) {
            $query->byGuard($guard);
        }

        return $query->get(['id', 'name', 'guard', 'display_name'])->all();
    }

    public function getRoleById(int $id): Role
    {
        $role = Role::with(['permissions:id,name,display_name,group,guard'])
            ->find($id);

        if (! $role) {
            throw BusinessException::notFound('角色不存在');
        }

        return $role;
    }

    public function createRole(array $data): Role
    {
        $this->validateGuard($data['guard'] ?? '');
        $this->checkRoleNameUnique($data['guard'], $data['name']);

        return DB::transaction(function () use ($data) {
            $role = Role::create([
                'name' => $data['name'],
                'guard' => $data['guard'],
                'display_name' => $data['display_name'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? true,
                'sort_order' => $data['sort_order'] ?? 0,
                'is_system' => false,
            ]);

            if (! empty($data['permissions'])) {
                $validPermissionIds = $this->getValidPermissionIds(
                    $data['guard'],
                    $data['permissions']
                );
                $role->syncPermissions($validPermissionIds);
            }

            $role->load(['permissions:id,name,display_name,group,guard']);

            return $role;
        });
    }

    public function updateRole(int $id, array $data): Role
    {
        $role = $this->getRoleById($id);

        $this->ensureNotSystemRole($role, '修改');

        $guard = $data['guard'] ?? $role->guard;
        $name = $data['name'] ?? $role->name;

        if ($guard !== $role->guard || $name !== $role->name) {
            $this->validateGuard($guard);
            $this->checkRoleNameUnique($guard, $name, $id);
        }

        return DB::transaction(function () use ($role, $data, $guard) {
            $updateData = array_filter([
                'name' => $data['name'] ?? null,
                'guard' => $data['guard'] ?? null,
                'display_name' => $data['display_name'] ?? null,
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? null,
                'sort_order' => $data['sort_order'] ?? null,
            ], fn ($value) => $value !== null);

            $role->update($updateData);

            if (isset($data['permissions'])) {
                $validPermissionIds = $this->getValidPermissionIds(
                    $role->guard,
                    $data['permissions']
                );
                $role->syncPermissions($validPermissionIds);
            }

            $role->load(['permissions:id,name,display_name,group,guard']);

            return $role;
        });
    }

    public function deleteRole(int $id): void
    {
        $role = $this->getRoleById($id);

        $this->ensureNotSystemRole($role, '删除');

        DB::transaction(function () use ($role) {
            $role->permissions()->detach();
            $role->delete();
        });
    }

    public function toggleRoleStatus(int $id): Role
    {
        $role = $this->getRoleById($id);

        $this->ensureNotSystemRole($role, '修改状态');

        $role->status = ! $role->status;
        $role->save();

        return $role;
    }

    public function getStats(array $filters = []): array
    {
        $baseQuery = Role::query();
        $this->applyFilters($baseQuery, $filters);

        $statsQuery = DB::table(DB::raw("({$baseQuery->toSql()}) as filtered"))
            ->mergeBindings($baseQuery->getQuery())
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as active')
            ->selectRaw('SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as inactive')
            ->selectRaw('SUM(CASE WHEN is_system = 1 THEN 1 ELSE 0 END) as system')
            ->first();

        return [
            'total' => (int) ($statsQuery->total ?? 0),
            'active' => (int) ($statsQuery->active ?? 0),
            'inactive' => (int) ($statsQuery->inactive ?? 0),
            'system' => (int) ($statsQuery->system ?? 0),
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        if (! empty($filters['guard'])) {
            $query->byGuard($filters['guard']);
        }

        if (! empty($filters['keyword'])) {
            $keyword = $filters['keyword'];
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('display_name', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $status = $filters['status'] === '1' || $filters['status'] === true ? 1 : 0;
            $query->where('status', $status);
        }
    }

    protected function validateGuard(string $guard): void
    {
        if (! GuardType::isValid($guard)) {
            throw BusinessException::validationFailed('守卫端值不正确');
        }
    }

    protected function checkRoleNameUnique(string $guard, string $name, ?int $excludeId = null): void
    {
        $query = Role::where('guard', $guard)->where('name', $name);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw BusinessException::validationFailed('该守卫端下角色标识已存在');
        }
    }

    protected function getValidPermissionIds(string $guard, array $permissionIds): array
    {
        $guardPermissions = Permission::byGuard($guard)->pluck('id')->toArray();

        return array_values(array_intersect($permissionIds, $guardPermissions));
    }

    protected function ensureNotSystemRole(Role $role, string $action): void
    {
        if ($role->is_system) {
            throw BusinessException::forbidden("系统内置角色不允许{$action}");
        }
    }
}
