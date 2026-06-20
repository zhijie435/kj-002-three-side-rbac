<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\AuthorizationService;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Enums\GuardType;
use App\Exceptions\BusinessException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

class AuthorizationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AuthorizationService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = app(AuthorizationService::class);
    }

    public function test_check_permission_guest_user()
    {
        Auth::shouldReceive('user')->andReturn(null);

        $result = $this->authService->checkPermission('some.permission');

        $this->assertFalse($result);
    }

    public function test_check_permission_super_admin()
    {
        $user = new User(['is_super_admin' => true]);
        Auth::shouldReceive('user')->andReturn($user);

        $result = $this->authService->checkPermission('any.permission');

        $this->assertTrue($result);
    }

    public function test_check_permission_user_without_role()
    {
        $user = new User(['is_super_admin' => false]);
        $user->setRelation('role', null);
        Auth::shouldReceive('user')->andReturn($user);

        $result = $this->authService->checkPermission('some.permission');

        $this->assertFalse($result);
    }

    public function test_check_permission_with_correct_guard()
    {
        $permission = Permission::factory()->platform()->create(['name' => 'user.view']);
        $role = Role::factory()->platform()->create();
        $role->syncPermissions([$permission->id]);

        $user = new User(['is_super_admin' => false]);
        $user->setRelation('role', $role);
        Auth::shouldReceive('user')->andReturn($user);

        $result = $this->authService->checkPermission('user.view', GuardType::PLATFORM->value);

        $this->assertTrue($result);
    }

    public function test_check_permission_with_wrong_guard()
    {
        $permission = Permission::factory()->platform()->create(['name' => 'user.view']);
        $role = Role::factory()->platform()->create();
        $role->syncPermissions([$permission->id]);

        $user = new User(['is_super_admin' => false]);
        $user->setRelation('role', $role);
        Auth::shouldReceive('user')->andReturn($user);

        $result = $this->authService->checkPermission('user.view', GuardType::MERCHANT->value);

        $this->assertFalse($result);
    }

    public function test_check_permission_has_permission()
    {
        $permission = Permission::factory()->platform()->create(['name' => 'user.create']);
        $role = Role::factory()->platform()->create();
        $role->syncPermissions([$permission->id]);

        $user = new User(['is_super_admin' => false]);
        $user->setRelation('role', $role);
        Auth::shouldReceive('user')->andReturn($user);

        $result = $this->authService->checkPermission('user.create');

        $this->assertTrue($result);
    }

    public function test_check_permission_no_permission()
    {
        $permission = Permission::factory()->platform()->create(['name' => 'user.view']);
        $role = Role::factory()->platform()->create();
        $role->syncPermissions([$permission->id]);

        $user = new User(['is_super_admin' => false]);
        $user->setRelation('role', $role);
        Auth::shouldReceive('user')->andReturn($user);

        $result = $this->authService->checkPermission('user.delete');

        $this->assertFalse($result);
    }

    public function test_check_any_permission_guest()
    {
        Auth::shouldReceive('user')->andReturn(null);

        $result = $this->authService->checkAnyPermission(['perm1', 'perm2']);

        $this->assertFalse($result);
    }

    public function test_check_any_permission_super_admin()
    {
        $user = new User(['is_super_admin' => true]);
        Auth::shouldReceive('user')->andReturn($user);

        $result = $this->authService->checkAnyPermission(['perm1', 'perm2']);

        $this->assertTrue($result);
    }

    public function test_check_any_permission_has_one()
    {
        $perm1 = Permission::factory()->platform()->create(['name' => 'perm1']);
        $perm2 = Permission::factory()->platform()->create(['name' => 'perm2']);
        $role = Role::factory()->platform()->create();
        $role->syncPermissions([$perm1->id]);

        $user = new User(['is_super_admin' => false]);
        $user->setRelation('role', $role);
        Auth::shouldReceive('user')->andReturn($user);

        $result = $this->authService->checkAnyPermission(['perm1', 'perm2']);

        $this->assertTrue($result);
    }

    public function test_check_any_permission_has_none()
    {
        $perm1 = Permission::factory()->platform()->create(['name' => 'perm1']);
        $role = Role::factory()->platform()->create();
        $role->syncPermissions([$perm1->id]);

        $user = new User(['is_super_admin' => false]);
        $user->setRelation('role', $role);
        Auth::shouldReceive('user')->andReturn($user);

        $result = $this->authService->checkAnyPermission(['perm2', 'perm3']);

        $this->assertFalse($result);
    }

    public function test_check_any_permission_wrong_guard()
    {
        $perm1 = Permission::factory()->platform()->create(['name' => 'perm1']);
        $role = Role::factory()->platform()->create();
        $role->syncPermissions([$perm1->id]);

        $user = new User(['is_super_admin' => false]);
        $user->setRelation('role', $role);
        Auth::shouldReceive('user')->andReturn($user);

        $result = $this->authService->checkAnyPermission(['perm1'], GuardType::MERCHANT->value);

        $this->assertFalse($result);
    }

    public function test_ensure_permission_success()
    {
        $permission = Permission::factory()->platform()->create(['name' => 'user.view']);
        $role = Role::factory()->platform()->create();
        $role->syncPermissions([$permission->id]);

        $user = new User(['is_super_admin' => false]);
        $user->setRelation('role', $role);
        Auth::shouldReceive('user')->andReturn($user);

        $this->expectNotToPerformAssertions();
        $this->authService->ensurePermission('user.view');
    }

    public function test_ensure_permission_failure()
    {
        $user = new User(['is_super_admin' => false]);
        $user->setRelation('role', null);
        Auth::shouldReceive('user')->andReturn($user);

        $this->expectException(BusinessException::class);
        $this->expectExceptionMessage('没有权限执行此操作');
        $this->expectExceptionCode(403);

        $this->authService->ensurePermission('some.permission');
    }

    public function test_ensure_any_permission_success()
    {
        $perm1 = Permission::factory()->platform()->create(['name' => 'perm1']);
        $role = Role::factory()->platform()->create();
        $role->syncPermissions([$perm1->id]);

        $user = new User(['is_super_admin' => false]);
        $user->setRelation('role', $role);
        Auth::shouldReceive('user')->andReturn($user);

        $this->expectNotToPerformAssertions();
        $this->authService->ensureAnyPermission(['perm1', 'perm2']);
    }

    public function test_ensure_any_permission_failure()
    {
        $user = new User(['is_super_admin' => false]);
        $user->setRelation('role', null);
        Auth::shouldReceive('user')->andReturn($user);

        $this->expectException(BusinessException::class);
        $this->expectExceptionMessage('没有权限执行此操作');
        $this->expectExceptionCode(403);

        $this->authService->ensureAnyPermission(['perm1', 'perm2']);
    }

    public function test_get_user_permissions_guest()
    {
        Auth::shouldReceive('user')->andReturn(null);

        $result = $this->authService->getUserPermissions();

        $this->assertEmpty($result);
    }

    public function test_get_user_permissions_super_admin()
    {
        Permission::factory()->count(3)->platform()->create();
        Permission::factory()->count(2)->merchant()->create();

        $user = new User(['is_super_admin' => true]);
        Auth::shouldReceive('user')->andReturn($user);

        $result = $this->authService->getUserPermissions();

        $this->assertCount(5, $result);
    }

    public function test_get_user_permissions_super_admin_filtered()
    {
        Permission::factory()->count(3)->platform()->create();
        Permission::factory()->count(2)->merchant()->create();

        $user = new User(['is_super_admin' => true]);
        Auth::shouldReceive('user')->andReturn($user);

        $result = $this->authService->getUserPermissions(GuardType::PLATFORM->value);

        $this->assertCount(3, $result);
    }

    public function test_get_user_permissions_no_role()
    {
        $user = new User(['is_super_admin' => false]);
        $user->setRelation('role', null);
        Auth::shouldReceive('user')->andReturn($user);

        $result = $this->authService->getUserPermissions();

        $this->assertEmpty($result);
    }

    public function test_get_user_permissions_with_role()
    {
        $perm1 = Permission::factory()->platform()->create(['name' => 'perm1']);
        $perm2 = Permission::factory()->platform()->create(['name' => 'perm2']);
        $role = Role::factory()->platform()->create();
        $role->syncPermissions([$perm1->id, $perm2->id]);

        $user = new User(['is_super_admin' => false]);
        $user->setRelation('role', $role);
        Auth::shouldReceive('user')->andReturn($user);

        $result = $this->authService->getUserPermissions();

        $this->assertCount(2, $result);
        $this->assertContains('perm1', $result);
        $this->assertContains('perm2', $result);
    }

    public function test_get_user_permissions_wrong_guard()
    {
        $perm1 = Permission::factory()->platform()->create(['name' => 'perm1']);
        $role = Role::factory()->platform()->create();
        $role->syncPermissions([$perm1->id]);

        $user = new User(['is_super_admin' => false]);
        $user->setRelation('role', $role);
        Auth::shouldReceive('user')->andReturn($user);

        $result = $this->authService->getUserPermissions(GuardType::MERCHANT->value);

        $this->assertEmpty($result);
    }

    public function test_get_user_role_guest()
    {
        Auth::shouldReceive('user')->andReturn(null);

        $result = $this->authService->getUserRole();

        $this->assertNull($result);
    }

    public function test_get_user_role_no_role()
    {
        $user = new User(['is_super_admin' => false]);
        $user->setRelation('role', null);
        Auth::shouldReceive('user')->andReturn($user);

        $result = $this->authService->getUserRole();

        $this->assertNull($result);
    }

    public function test_get_user_role_with_role()
    {
        $role = Role::factory()->platform()->create();

        $user = new User(['is_super_admin' => false]);
        $user->setRelation('role', $role);
        Auth::shouldReceive('user')->andReturn($user);

        $result = $this->authService->getUserRole();

        $this->assertNotNull($result);
        $this->assertEquals($role->id, $result->id);
    }

    public function test_get_user_role_wrong_guard()
    {
        $role = Role::factory()->platform()->create();

        $user = new User(['is_super_admin' => false]);
        $user->setRelation('role', $role);
        Auth::shouldReceive('user')->andReturn($user);

        $result = $this->authService->getUserRole(GuardType::MERCHANT->value);

        $this->assertNull($result);
    }

    public function test_is_super_admin_guest()
    {
        Auth::shouldReceive('user')->andReturn(null);

        $result = $this->authService->isSuperAdmin();

        $this->assertFalse($result);
    }

    public function test_is_super_admin_true()
    {
        $user = new User(['is_super_admin' => true]);
        Auth::shouldReceive('user')->andReturn($user);

        $result = $this->authService->isSuperAdmin();

        $this->assertTrue($result);
    }

    public function test_is_super_admin_false()
    {
        $user = new User(['is_super_admin' => false]);
        Auth::shouldReceive('user')->andReturn($user);

        $result = $this->authService->isSuperAdmin();

        $this->assertFalse($result);
    }

    public function test_is_super_admin_null_attribute()
    {
        $user = new User([]);
        Auth::shouldReceive('user')->andReturn($user);

        $result = $this->authService->isSuperAdmin();

        $this->assertFalse($result);
    }
}
