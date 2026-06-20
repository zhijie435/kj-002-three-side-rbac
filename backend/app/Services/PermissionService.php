<?php

namespace App\Services;

use App\Enums\GuardType;
use App\Exceptions\BusinessException;
use App\Models\Permission;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PermissionService
{
    public function getPermissionTree(?string $guard = null): array
    {
        $query = Permission::query()
            ->orderBy('guard')
            ->orderBy('group')
            ->orderBy('id');

        if ($guard) {
            $query->byGuard($guard);
        }

        $permissions = $query->get(['id', 'name', 'display_name', 'group', 'guard']);

        $grouped = $permissions->groupBy('guard')->map(function (Collection $guardItems, string $guard) {
            return [
                'guard' => $guard,
                'guard_name' => $this->getGuardName($guard),
                'groups' => $guardItems->groupBy('group')->map(function (Collection $items, string $group) {
                    return [
                        'group' => $group,
                        'group_name' => $this->getGroupName($group),
                        'permissions' => $items,
                    ];
                })->values(),
            ];
        })->values();

        return $grouped->all();
    }

    public function getPermissionFlatTree(?string $guard = null): array
    {
        $query = Permission::query()
            ->orderBy('guard')
            ->orderBy('group')
            ->orderBy('id');

        if ($guard) {
            $query->byGuard($guard);
        }

        $permissions = $query->get(['id', 'name', 'display_name', 'group', 'guard']);

        $grouped = $permissions->groupBy('guard')->map(function (Collection $guardItems) {
            return $guardItems->groupBy('group')->map(function (Collection $items, string $group) {
                return [
                    'group' => $group,
                    'group_name' => $this->getGroupName($group),
                    'children' => $items->map(fn ($item) => [
                        'id' => $item->id,
                        'name' => $item->name,
                        'display_name' => $item->display_name,
                    ]),
                ];
            })->values();
        })->values();

        if ($guard && $grouped->isNotEmpty()) {
            return $grouped->first()->all();
        }

        return $grouped->all();
    }

    public function createPermission(array $data): Permission
    {
        $this->validateGuard($data['guard'] ?? '');
        $this->checkPermissionNameUnique($data['guard'], $data['name']);

        return Permission::create([
            'name' => $data['name'],
            'guard' => $data['guard'],
            'display_name' => $data['display_name'],
            'group' => $data['group'],
            'description' => $data['description'] ?? null,
        ]);
    }

    public function getGuardName(string $guard): string
    {
        try {
            return GuardType::from($guard)->label();
        } catch (\Throwable $e) {
            return $guard;
        }
    }

    public function getGroupName(string $group): string
    {
        $names = [
            'user' => '用户管理',
            'role' => '角色管理',
            'permission' => '权限管理',
            'order' => '订单管理',
            'product' => '商品管理',
            'inventory' => '库存管理',
            'system' => '系统设置',
            'dashboard' => '数据面板',
            'merchant' => '商家管理',
            'staff' => '员工管理',
            'warehouse' => '仓库管理',
        ];

        return $names[$group] ?? $group;
    }

    protected function validateGuard(string $guard): void
    {
        if (! GuardType::isValid($guard)) {
            throw BusinessException::validationFailed('守卫端值不正确');
        }
    }

    protected function checkPermissionNameUnique(string $guard, string $name): void
    {
        $exists = Permission::where('guard', $guard)
            ->where('name', $name)
            ->exists();

        if ($exists) {
            throw BusinessException::validationFailed('该守卫端下权限标识已存在');
        }
    }
}
