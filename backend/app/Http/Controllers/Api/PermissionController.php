<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PermissionService;
use App\Traits\ApiResponse;
use App\Exceptions\BusinessException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PermissionController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PermissionService $permissionService
    ) {}

    public function index(Request $request)
    {
        try {
            $guard = $request->input('guard');
            $permissions = $this->permissionService->getPermissionTree($guard);

            return $this->success($permissions);
        } catch (BusinessException $e) {
            return $this->error($e->getMessage(), $e->getErrorCode());
        } catch (\Exception $e) {
            return $this->error('获取权限列表失败', 500);
        }
    }

    public function all(Request $request)
    {
        try {
            $guard = $request->input('guard');
            $permissions = $this->permissionService->getPermissionFlatTree($guard);

            return $this->success($permissions);
        } catch (BusinessException $e) {
            return $this->error($e->getMessage(), $e->getErrorCode());
        } catch (\Exception $e) {
            return $this->error('获取权限列表失败', 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100',
                'guard' => 'required|string|max:50',
                'display_name' => 'required|string|max:100',
                'group' => 'required|string|max:50',
                'description' => 'nullable|string|max:500',
            ], [
                'name.required' => '权限标识不能为空',
                'guard.required' => '守卫端不能为空',
                'display_name.required' => '权限名称不能为空',
                'group.required' => '权限分组不能为空',
            ]);

            if ($validator->fails()) {
                return $this->error('参数验证失败', 422, $validator->errors());
            }

            $permission = $this->permissionService->createPermission($validator->validated());

            return $this->success($permission, '权限创建成功', 201);
        } catch (BusinessException $e) {
            return $this->error($e->getMessage(), $e->getErrorCode());
        } catch (\Exception $e) {
            return $this->error('创建权限失败', 500);
        }
    }
}
