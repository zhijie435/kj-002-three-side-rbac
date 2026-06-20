<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\PermissionService;
use App\Models\Permission;
use App\Models\Role;
use App\Enums\GuardType;
use App\Exceptions\BusinessException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PermissionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PermissionService $permissionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->permissionService = app(PermissionService::class);
    }

    public function test_get_permission_tree_all_guards()
    {
        Permission::factory()->count(2)->platform()->group('user')->create();
        Permission::factory()->count(3)->platform()->group('role')->create();
        Permission::factory()->count(2)->merchant()->group('order')->create();

        $tree = $this->permissionService->getPermissionTree();

        $this->assertIsArray($tree);
        $this->assertCount(2, $tree);

        $guards = array_column($tree, 'guard');
        $this->assertContains(GuardType::PLATFORM->value, $guards);
        $this->assertContains(GuardType::MERCHANT->value, $guards);
    }

    public function test_get_permission_tree_filter_by_guard()
    {
        Permission::factory()->count(5)->platform()->create();
        Permission::factory()->count(3)->merchant()->create();

        $tree = $this->permissionService->getPermissionTree(GuardType::PLATFORM->value);

        $this->assertCount(1, $tree);
        $this->assertEquals(GuardType::PLATFORM->value, $tree[0]['guard']);
    }

    public function test_get_permission_tree_group_structure()
    {
        Permission::factory()->platform()->group('user')->create(['name' => 'user.view', 'display_name' => '查看用户']);
        Permission::factory()->platform()->group('user')->create(['name' => 'user.create', 'display_name' => '创建用户']);
        Permission::factory()->platform()->group('role')->create(['name' => 'role.view', 'display_name' => '查看角色']);

        $tree = $this->permissionService->getPermissionTree(GuardType::PLATFORM->value);
        $groups = $tree[0]['groups'];

        $this->assertCount(2, $groups);

        $userGroup = collect($groups)->firstWhere('group', 'user');
        $this->assertEquals('用户管理', $userGroup['group_name']);
        $this->assertCount(2, $userGroup['permissions']);
    }

    public function test_get_permission_flat_tree()
    {
        Permission::factory()->platform()->group('user')->count(2)->create();
        Permission::factory()->platform()->group('role')->count(3)->create();

        $flatTree = $this->permissionService->getPermissionFlatTree(GuardType::PLATFORM->value);

        $this->assertIsArray($flatTree);
        $this->assertCount(2, $flatTree);

        $userGroup = collect($flatTree)->firstWhere('group', 'user');
        $this->assertArrayHasKey('children', $userGroup);
        $this->assertCount(2, $userGroup['children']);
    }

    public function test_get_permission_flat_tree_all_guards()
    {
        Permission::factory()->platform()->group('user')->create();
        Permission::factory()->merchant()->group('order')->create();

        $flatTree = $this->permissionService->getPermissionFlatTree();

        $this->assertCount(2, $flatTree);
    }

    public function test_create_permission_success()
    {
        $data = [
            'name' => 'test.permission',
            'guard' => GuardType::PLATFORM->value,
            'display_name' => '测试权限',
            'group' => 'user',
            'description' => '测试权限描述',
        ];

        $permission = $this->permissionService->createPermission($data);

        $this->assertInstanceOf(Permission::class, $permission);
        $this->assertEquals('test.permission', $permission->name);
        $this->assertEquals(GuardType::PLATFORM->value, $permission->guard);
        $this->assertEquals('测试权限', $permission->display_name);
        $this->assertEquals('user', $permission->group);
    }

    public function test_create_permission_invalid_guard()
    {
        $this->expectException(BusinessException::class);
        $this->expectExceptionMessage('守卫端值不正确');
        $this->expectExceptionCode(422);

        $this->permissionService->createPermission([
            'name' => 'test.permission',
            'guard' => 'invalid',
            'display_name' => '测试',
            'group' => 'user',
        ]);
    }

    public function test_create_permission_duplicate_name()
    {
        Permission::factory()->platform()->create(['name' => 'existing.permission']);

        $this->expectException(BusinessException::class);
        $this->expectExceptionMessage('该守卫端下权限标识已存在');
        $this->expectExceptionCode(422);

        $this->permissionService->createPermission([
            'name' => 'existing.permission',
            'guard' => GuardType::PLATFORM->value,
            'display_name' => '重复权限',
            'group' => 'user',
        ]);
    }

    public function test_create_permission_same_name_different_guard()
    {
        Permission::factory()->platform()->create(['name' => 'same.permission']);

        $permission = $this->permissionService->createPermission([
            'name' => 'same.permission',
            'guard' => GuardType::MERCHANT->value,
            'display_name' => '同名称不同守卫',
            'group' => 'user',
        ]);

        $this->assertInstanceOf(Permission::class, $permission);
        $this->assertEquals(GuardType::MERCHANT->value, $permission->guard);
    }

    public function test_get_guard_name()
    {
        $this->assertEquals('平台端', $this->permissionService->getGuardName(GuardType::PLATFORM->value));
        $this->assertEquals('商家端', $this->permissionService->getGuardName(GuardType::MERCHANT->value));
        $this->assertEquals('仓库端', $this->permissionService->getGuardName(GuardType::WAREHOUSE->value));
    }

    public function test_get_guard_name_invalid()
    {
        $this->assertEquals('invalid_guard', $this->permissionService->getGuardName('invalid_guard'));
    }

    public function test_get_group_name()
    {
        $this->assertEquals('用户管理', $this->permissionService->getGroupName('user'));
        $this->assertEquals('角色管理', $this->permissionService->getGroupName('role'));
        $this->assertEquals('订单管理', $this->permissionService->getGroupName('order'));
    }

    public function test_get_group_name_unknown()
    {
        $this->assertEquals('unknown_group', $this->permissionService->getGroupName('unknown_group'));
    }

    public function test_get_permission_tree_empty()
    {
        $tree = $this->permissionService->getPermissionTree();

        $this->assertEmpty($tree);
    }

    public function test_get_permission_flat_tree_empty()
    {
        $flatTree = $this->permissionService->getPermissionFlatTree();

        $this->assertEmpty($flatTree);
    }

    public function test_create_permission_without_description()
    {
        $data = [
            'name' => 'test.no.desc',
            'guard' => GuardType::PLATFORM->value,
            'display_name' => '无描述权限',
            'group' => 'user',
        ];

        $permission = $this->permissionService->createPermission($data);

        $this->assertNull($permission->description);
    }
}
