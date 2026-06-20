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
            'platform' => [
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
                ['name' => 'merchant.view', 'display_name' => '查看商家', 'group' => 'merchant'],
                ['name' => 'merchant.create', 'display_name' => '创建商家', 'group' => 'merchant'],
                ['name' => 'merchant.edit', 'display_name' => '编辑商家', 'group' => 'merchant'],
                ['name' => 'merchant.delete', 'display_name' => '删除商家', 'group' => 'merchant'],
            ],
            'merchant' => [
                ['name' => 'dashboard.view', 'display_name' => '查看数据面板', 'group' => 'dashboard'],
                ['name' => 'order.view', 'display_name' => '查看订单', 'group' => 'order'],
                ['name' => 'order.edit', 'display_name' => '编辑订单', 'group' => 'order'],
                ['name' => 'product.view', 'display_name' => '查看商品', 'group' => 'product'],
                ['name' => 'product.create', 'display_name' => '创建商品', 'group' => 'product'],
                ['name' => 'product.edit', 'display_name' => '编辑商品', 'group' => 'product'],
                ['name' => 'product.delete', 'display_name' => '删除商品', 'group' => 'product'],
                ['name' => 'inventory.view', 'display_name' => '查看库存', 'group' => 'inventory'],
                ['name' => 'inventory.edit', 'display_name' => '编辑库存', 'group' => 'inventory'],
                ['name' => 'merchant.profile.view', 'display_name' => '查看店铺信息', 'group' => 'merchant'],
                ['name' => 'merchant.profile.edit', 'display_name' => '编辑店铺信息', 'group' => 'merchant'],
                ['name' => 'staff.view', 'display_name' => '查看员工', 'group' => 'staff'],
                ['name' => 'staff.create', 'display_name' => '创建员工', 'group' => 'staff'],
                ['name' => 'staff.edit', 'display_name' => '编辑员工', 'group' => 'staff'],
                ['name' => 'staff.delete', 'display_name' => '删除员工', 'group' => 'staff'],
            ],
            'warehouse' => [
                ['name' => 'dashboard.view', 'display_name' => '查看数据面板', 'group' => 'dashboard'],
                ['name' => 'order.view', 'display_name' => '查看订单', 'group' => 'order'],
                ['name' => 'order.ship', 'display_name' => '订单发货', 'group' => 'order'],
                ['name' => 'product.view', 'display_name' => '查看商品', 'group' => 'product'],
                ['name' => 'inventory.view', 'display_name' => '查看库存', 'group' => 'inventory'],
                ['name' => 'inventory.in', 'display_name' => '入库操作', 'group' => 'inventory'],
                ['name' => 'inventory.out', 'display_name' => '出库操作', 'group' => 'inventory'],
                ['name' => 'inventory.check', 'display_name' => '库存盘点', 'group' => 'inventory'],
                ['name' => 'warehouse.view', 'display_name' => '查看仓库信息', 'group' => 'warehouse'],
                ['name' => 'warehouse.edit', 'display_name' => '编辑仓库信息', 'group' => 'warehouse'],
                ['name' => 'staff.view', 'display_name' => '查看员工', 'group' => 'staff'],
            ],
        ];

        foreach ($permissions as $guard => $guardPermissions) {
            foreach ($guardPermissions as $permission) {
                Permission::firstOrCreate(
                    ['guard' => $guard, 'name' => $permission['name']],
                    array_merge($permission, ['guard' => $guard])
                );
            }
        }

        $roles = [
            'platform' => [
                [
                    'name' => 'super_admin',
                    'display_name' => '超级管理员',
                    'description' => '系统最高权限，拥有所有操作权限',
                    'is_system' => true,
                    'permission_groups' => ['dashboard', 'user', 'role', 'permission', 'order', 'product', 'inventory', 'system', 'merchant'],
                ],
                [
                    'name' => 'admin',
                    'display_name' => '管理员',
                    'description' => '系统管理员，拥有大部分管理权限',
                    'is_system' => true,
                    'permission_groups' => ['dashboard', 'user', 'role', 'permission', 'order', 'product', 'inventory', 'merchant'],
                ],
                [
                    'name' => 'operation',
                    'display_name' => '运营专员',
                    'description' => '运营人员，负责订单和商品管理',
                    'is_system' => false,
                    'permission_groups' => ['dashboard', 'order', 'product', 'inventory'],
                ],
                [
                    'name' => 'customer_service',
                    'display_name' => '客服人员',
                    'description' => '客服人员，仅可查看订单和用户信息',
                    'is_system' => false,
                    'permission_groups' => ['dashboard', 'user', 'order', 'product'],
                ],
            ],
            'merchant' => [
                [
                    'name' => 'merchant_owner',
                    'display_name' => '店主',
                    'description' => '商家最高权限，拥有店铺所有操作权限',
                    'is_system' => true,
                    'permission_groups' => ['dashboard', 'order', 'product', 'inventory', 'merchant', 'staff'],
                ],
                [
                    'name' => 'merchant_manager',
                    'display_name' => '店铺管理员',
                    'description' => '店铺管理员，管理日常运营',
                    'is_system' => false,
                    'permission_groups' => ['dashboard', 'order', 'product', 'inventory', 'staff'],
                ],
                [
                    'name' => 'merchant_operator',
                    'display_name' => '运营人员',
                    'description' => '店铺运营人员，负责商品和订单',
                    'is_system' => false,
                    'permission_groups' => ['dashboard', 'order', 'product'],
                ],
            ],
            'warehouse' => [
                [
                    'name' => 'warehouse_manager',
                    'display_name' => '仓库管理员',
                    'description' => '仓库最高权限，管理仓库所有操作',
                    'is_system' => true,
                    'permission_groups' => ['dashboard', 'order', 'product', 'inventory', 'warehouse', 'staff'],
                ],
                [
                    'name' => 'warehouse_operator',
                    'display_name' => '仓库操作员',
                    'description' => '仓库操作员，负责出入库',
                    'is_system' => false,
                    'permission_groups' => ['dashboard', 'order', 'product', 'inventory'],
                ],
                [
                    'name' => 'warehouse_picker',
                    'display_name' => '拣货员',
                    'description' => '拣货员，负责订单拣货发货',
                    'is_system' => false,
                    'permission_groups' => ['dashboard', 'order', 'inventory'],
                ],
            ],
        ];

        foreach ($roles as $guard => $guardRoles) {
            foreach ($guardRoles as $roleData) {
                $permissionGroups = $roleData['permission_groups'];
                unset($roleData['permission_groups']);

                $role = Role::firstOrCreate(
                    ['guard' => $guard, 'name' => $roleData['name']],
                    array_merge($roleData, ['guard' => $guard])
                );

                $permissionIds = Permission::where('guard', $guard)
                    ->whereIn('group', $permissionGroups)
                    ->pluck('id')
                    ->toArray();

                $role->syncPermissions($permissionIds);
            }
        }
    }
}
