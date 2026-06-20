<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'dashboard.view', 'display_name' => '查看数据面板', 'group' => 'dashboard'],
            ['name' => 'user.view', 'display_name' => '查看用户', 'group' => 'user'],
            ['name' => 'user.create', 'display_name' => '创建用户', 'group' => 'user'],
            ['name' => 'user.edit', 'display_name' => '编辑用户', 'group' => 'user'],
            ['name' => 'user.delete', 'display_name' => '删除用户', 'group' => 'user'],
            ['name' => 'role.view', 'display_name' => '查看角色', 'group' => 'role'],
            ['name' => 'role.create', 'display_name' => '创建角色', 'group' => 'role'],
            ['name' => 'role.edit', 'display_name' => '编辑角色', 'group' => 'role'],
            ['name' => 'role.delete', 'display_name' => '删除角色', 'group' => 'role'],
            ['name' => 'permission.view', 'display_name' => '查看权限', 'group' => 'permission'],
            ['name' => 'permission.create', 'display_name' => '创建权限', 'group' => 'permission'],
            ['name' => 'order.view', 'display_name' => '查看订单', 'group' => 'order'],
            ['name' => 'order.create', 'display_name' => '创建订单', 'group' => 'order'],
            ['name' => 'order.edit', 'display_name' => '编辑订单', 'group' => 'order'],
            ['name' => 'order.delete', 'display_name' => '删除订单', 'group' => 'order'],
            ['name' => 'product.view', 'display_name' => '查看商品', 'group' => 'product'],
            ['name' => 'product.create', 'display_name' => '创建商品', 'group' => 'product'],
            ['name' => 'product.edit', 'display_name' => '编辑商品', 'group' => 'product'],
            ['name' => 'product.delete', 'display_name' => '删除商品', 'group' => 'product'],
            ['name' => 'inventory.view', 'display_name' => '查看库存', 'group' => 'inventory'],
            ['name' => 'inventory.edit', 'display_name' => '编辑库存', 'group' => 'inventory'],
            ['name' => 'system.view', 'display_name' => '查看系统设置', 'group' => 'system'],
            ['name' => 'system.edit', 'display_name' => '编辑系统设置', 'group' => 'system'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        $allPermissionIds = Permission::pluck('id')->toArray();

        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => '超级管理员',
                'description' => '系统最高权限，拥有所有操作权限',
                'is_system' => true,
                'permissions' => $allPermissionIds,
            ],
            [
                'name' => 'admin',
                'display_name' => '管理员',
                'description' => '系统管理员，拥有大部分管理权限',
                'is_system' => true,
                'permissions' => Permission::whereNotIn('name', [
                    'system.view', 'system.edit',
                ])->pluck('id')->toArray(),
            ],
            [
                'name' => 'operation',
                'display_name' => '运营专员',
                'description' => '运营人员，负责订单和商品管理',
                'is_system' => false,
                'permissions' => Permission::whereIn('group', [
                    'dashboard', 'order', 'product', 'inventory',
                ])->pluck('id')->toArray(),
            ],
            [
                'name' => 'customer_service',
                'display_name' => '客服人员',
                'description' => '客服人员，仅可查看订单和用户信息',
                'is_system' => false,
                'permissions' => Permission::whereIn('name', [
                    'dashboard.view', 'user.view', 'order.view', 'product.view',
                ])->pluck('id')->toArray(),
            ],
            [
                'name' => 'warehouse',
                'display_name' => '仓库管理员',
                'description' => '仓库管理人员，负责库存管理',
                'is_system' => false,
                'permissions' => Permission::whereIn('group', [
                    'inventory', 'product',
                ])->pluck('id')->toArray(),
            ],
        ];

        foreach ($roles as $roleData) {
            $permissionIds = $roleData['permissions'];
            unset($roleData['permissions']);

            $role = Role::firstOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );

            $role->syncPermissions($permissionIds);
        }
    }
}
