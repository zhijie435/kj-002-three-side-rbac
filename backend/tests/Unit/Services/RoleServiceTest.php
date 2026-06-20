<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\RoleService;
use App\Models\Role;
use App\Models\Permission;
use App\Enums\GuardType;
use App\Exceptions\BusinessException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleServiceTest extends TestCase
{
    use RefreshDatabase;

    protected RoleService $roleService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->roleService = app(RoleService::class);
    }

    public function test_get_role_list_with_pagination()
    {
        Role::factory()->count(20)->platform()->create();

        $result = $this->roleService->getRoleList([], 10);

        $this->assertArrayHasKey('paginator', $result);
        $this->assertArrayHasKey('stats', $result);
        $this->assertEquals(10, $result['paginator']->perPage());
        $this->assertEquals(20, $result['paginator']->total());
        $this->assertEquals(20, $result['stats']['total']);
    }

    public function test_get_role_list_filter_by_guard()
    {
        Role::factory()->count(5)->platform()->create();
        Role::factory()->count(3)->merchant()->create();
        Role::factory()->count(2)->warehouse()->create();

        $result = $this->roleService->getRoleList(['guard' => GuardType::PLATFORM->value]);

        $this->assertEquals(5, $result['paginator']->total());
        $this->assertEquals(5, $result['stats']['total']);
    }

    public function test_get_role_list_filter_by_keyword()
    {
        Role::factory()->platform()->create(['name' => 'admin', 'display_name' => '管理员']);
        Role::factory()->platform()->create(['name' => 'editor', 'display_name' => '编辑']);
        Role::factory()->platform()->create(['name' => 'viewer', 'display_name' => '访客']);

        $result = $this->roleService->getRoleList(['keyword' => 'admin']);

        $this->assertEquals(1, $result['paginator']->total());
    }

    public function test_get_role_list_filter_by_status()
    {
        Role::factory()->count(3)->platform()->create(['status' => true]);
        Role::factory()->count(2)->platform()->inactive()->create();

        $resultActive = $this->roleService->getRoleList(['status' => '1']);
        $resultInactive = $this->roleService->getRoleList(['status' => '0']);

        $this->assertEquals(3, $resultActive['paginator']->total());
        $this->assertEquals(2, $resultInactive['paginator']->total());
    }

    public function test_get_all_roles()
    {
        Role::factory()->count(3)->platform()->create();
        Role::factory()->count(2)->merchant()->create();

        $allRoles = $this->roleService->getAllRoles();
        $platformRoles = $this->roleService->getAllRoles(GuardType::PLATFORM->value);

        $this->assertCount(5, $allRoles);
        $this->assertCount(3, $platformRoles);
    }

    public function test_get_role_by_id_success()
    {
        $role = Role::factory()->platform()->create();

        $foundRole = $this->roleService->getRoleById($role->id);

        $this->assertEquals($role->id, $foundRole->id);
        $this->assertEquals($role->name, $foundRole->name);
    }

    public function test_get_role_by_id_not_found()
    {
        $this->expectException(BusinessException::class);
        $this->expectExceptionMessage('角色不存在');
        $this->expectExceptionCode(404);

        $this->roleService->getRoleById(999);
    }

    public function test_create_role_success()
    {
        $data = [
            'name' => 'test_role',
            'guard' => GuardType::PLATFORM->value,
            'display_name' => '测试角色',
            'description' => '这是一个测试角色',
            'status' => true,
            'sort_order' => 10,
        ];

        $role = $this->roleService->createRole($data);

        $this->assertInstanceOf(Role::class, $role);
        $this->assertEquals('test_role', $role->name);
        $this->assertEquals(GuardType::PLATFORM->value, $role->guard);
        $this->assertEquals('测试角色', $role->display_name);
        $this->assertFalse($role->is_system);
    }

    public function test_create_role_with_permissions()
    {
        $permissions = Permission::factory()->count(3)->platform()->create();

        $data = [
            'name' => 'test_role_with_perms',
            'guard' => GuardType::PLATFORM->value,
            'display_name' => '带权限测试角色',
            'permissions' => $permissions->pluck('id')->toArray(),
        ];

        $role = $this->roleService->createRole($data);

        $this->assertCount(3, $role->permissions);
        $this->assertEqualsCanonicalizing(
            $permissions->pluck('id')->toArray(),
            $role->permissions->pluck('id')->toArray()
        );
    }

    public function test_create_role_with_cross_guard_permissions()
    {
        $platformPerms = Permission::factory()->count(2)->platform()->create();
        $merchantPerms = Permission::factory()->count(2)->merchant()->create();

        $data = [
            'name' => 'test_role_cross_guard',
            'guard' => GuardType::PLATFORM->value,
            'display_name' => '测试角色',
            'permissions' => $platformPerms->pluck('id')->merge($merchantPerms->pluck('id'))->toArray(),
        ];

        $role = $this->roleService->createRole($data);

        $this->assertCount(2, $role->permissions);
        $this->assertEqualsCanonicalizing(
            $platformPerms->pluck('id')->toArray(),
            $role->permissions->pluck('id')->toArray()
        );
    }

    public function test_create_role_invalid_guard()
    {
        $this->expectException(BusinessException::class);
        $this->expectExceptionMessage('守卫端值不正确');
        $this->expectExceptionCode(422);

        $this->roleService->createRole([
            'name' => 'test_role',
            'guard' => 'invalid_guard',
            'display_name' => '测试角色',
        ]);
    }

    public function test_create_role_duplicate_name()
    {
        Role::factory()->platform()->create(['name' => 'existing_role']);

        $this->expectException(BusinessException::class);
        $this->expectExceptionMessage('该守卫端下角色标识已存在');
        $this->expectExceptionCode(422);

        $this->roleService->createRole([
            'name' => 'existing_role',
            'guard' => GuardType::PLATFORM->value,
            'display_name' => '重复角色',
        ]);
    }

    public function test_create_role_duplicate_name_different_guard()
    {
        Role::factory()->platform()->create(['name' => 'same_name']);

        $role = $this->roleService->createRole([
            'name' => 'same_name',
            'guard' => GuardType::MERCHANT->value,
            'display_name' => '同名称不同守卫',
        ]);

        $this->assertInstanceOf(Role::class, $role);
        $this->assertEquals(GuardType::MERCHANT->value, $role->guard);
    }

    public function test_update_role_success()
    {
        $role = Role::factory()->platform()->create([
            'name' => 'old_name',
            'display_name' => '旧名称',
        ]);

        $updatedRole = $this->roleService->updateRole($role->id, [
            'display_name' => '新名称',
            'description' => '新描述',
        ]);

        $this->assertEquals('新名称', $updatedRole->display_name);
        $this->assertEquals('新描述', $updatedRole->description);
        $this->assertEquals('old_name', $updatedRole->name);
    }

    public function test_update_role_name_and_guard()
    {
        $role = Role::factory()->platform()->create();

        $updatedRole = $this->roleService->updateRole($role->id, [
            'name' => 'new_name',
            'guard' => GuardType::MERCHANT->value,
        ]);

        $this->assertEquals('new_name', $updatedRole->name);
        $this->assertEquals(GuardType::MERCHANT->value, $updatedRole->guard);
    }

    public function test_update_role_permissions()
    {
        $role = Role::factory()->platform()->create();
        $oldPerms = Permission::factory()->count(2)->platform()->create();
        $newPerms = Permission::factory()->count(3)->platform()->create();

        $role->syncPermissions($oldPerms->pluck('id')->toArray());

        $updatedRole = $this->roleService->updateRole($role->id, [
            'permissions' => $newPerms->pluck('id')->toArray(),
        ]);

        $this->assertCount(3, $updatedRole->permissions);
        $this->assertEqualsCanonicalizing(
            $newPerms->pluck('id')->toArray(),
            $updatedRole->permissions->pluck('id')->toArray()
        );
    }

    public function test_update_system_role_forbidden()
    {
        $role = Role::factory()->platform()->system()->create();

        $this->expectException(BusinessException::class);
        $this->expectExceptionMessage('系统内置角色不允许修改');
        $this->expectExceptionCode(403);

        $this->roleService->updateRole($role->id, ['display_name' => '修改系统角色']);
    }

    public function test_update_role_duplicate_name()
    {
        Role::factory()->platform()->create(['name' => 'existing_name']);
        $role = Role::factory()->platform()->create(['name' => 'my_name']);

        $this->expectException(BusinessException::class);
        $this->expectExceptionMessage('该守卫端下角色标识已存在');
        $this->expectExceptionCode(422);

        $this->roleService->updateRole($role->id, ['name' => 'existing_name']);
    }

    public function test_update_role_same_name_no_error()
    {
        $role = Role::factory()->platform()->create(['name' => 'my_name']);

        $updatedRole = $this->roleService->updateRole($role->id, [
            'name' => 'my_name',
            'display_name' => '更新名称',
        ]);

        $this->assertEquals('my_name', $updatedRole->name);
        $this->assertEquals('更新名称', $updatedRole->display_name);
    }

    public function test_delete_role_success()
    {
        $role = Role::factory()->platform()->create();
        $permissions = Permission::factory()->count(3)->platform()->create();
        $role->syncPermissions($permissions->pluck('id')->toArray());

        $this->roleService->deleteRole($role->id);

        $this->assertSoftDeleted($role);
        $this->assertDatabaseCount('role_permission', 0);
    }

    public function test_delete_system_role_forbidden()
    {
        $role = Role::factory()->platform()->system()->create();

        $this->expectException(BusinessException::class);
        $this->expectExceptionMessage('系统内置角色不允许删除');
        $this->expectExceptionCode(403);

        $this->roleService->deleteRole($role->id);
    }

    public function test_delete_nonexistent_role()
    {
        $this->expectException(BusinessException::class);
        $this->expectExceptionMessage('角色不存在');
        $this->expectExceptionCode(404);

        $this->roleService->deleteRole(999);
    }

    public function test_toggle_role_status()
    {
        $role = Role::factory()->platform()->create(['status' => true]);

        $toggledRole = $this->roleService->toggleRoleStatus($role->id);

        $this->assertFalse($toggledRole->status);

        $toggledRole = $this->roleService->toggleRoleStatus($role->id);

        $this->assertTrue($toggledRole->status);
    }

    public function test_toggle_system_role_status_forbidden()
    {
        $role = Role::factory()->platform()->system()->create();

        $this->expectException(BusinessException::class);
        $this->expectExceptionMessage('系统内置角色不允许修改状态');
        $this->expectExceptionCode(403);

        $this->roleService->toggleRoleStatus($role->id);
    }

    public function test_get_stats()
    {
        Role::factory()->count(3)->platform()->create(['status' => true]);
        Role::factory()->count(2)->platform()->inactive()->create();
        Role::factory()->count(1)->platform()->system()->create();

        $stats = $this->roleService->getStats(['guard' => GuardType::PLATFORM->value]);

        $this->assertEquals(6, $stats['total']);
        $this->assertEquals(4, $stats['active']);
        $this->assertEquals(2, $stats['inactive']);
        $this->assertEquals(1, $stats['system']);
    }

    public function test_get_stats_empty()
    {
        $stats = $this->roleService->getStats();

        $this->assertEquals(0, $stats['total']);
        $this->assertEquals(0, $stats['active']);
        $this->assertEquals(0, $stats['inactive']);
        $this->assertEquals(0, $stats['system']);
    }

    public function test_create_role_default_values()
    {
        $data = [
            'name' => 'test_defaults',
            'guard' => GuardType::PLATFORM->value,
            'display_name' => '测试默认值',
        ];

        $role = $this->roleService->createRole($data);

        $this->assertTrue($role->status);
        $this->assertEquals(0, $role->sort_order);
        $this->assertNull($role->description);
        $this->assertFalse($role->is_system);
    }

    public function test_update_role_only_specified_fields()
    {
        $role = Role::factory()->platform()->create([
            'name' => 'original_name',
            'display_name' => 'Original',
            'description' => 'Original desc',
            'sort_order' => 5,
            'status' => true,
        ]);

        $updatedRole = $this->roleService->updateRole($role->id, [
            'display_name' => 'Updated',
        ]);

        $this->assertEquals('original_name', $updatedRole->name);
        $this->assertEquals('Updated', $updatedRole->display_name);
        $this->assertEquals('Original desc', $updatedRole->description);
        $this->assertEquals(5, $updatedRole->sort_order);
        $this->assertTrue($updatedRole->status);
    }

    public function test_get_role_list_includes_permissions()
    {
        $role = Role::factory()->platform()->create();
        $permissions = Permission::factory()->count(3)->platform()->create();
        $role->syncPermissions($permissions->pluck('id')->toArray());

        $result = $this->roleService->getRoleList();
        $roleFromList = $result['paginator']->first();

        $this->assertTrue($roleFromList->relationLoaded('permissions'));
        $this->assertEquals(3, $roleFromList->permission_count);
    }
}
