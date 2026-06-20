<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RoleService;
use App\Traits\ApiResponse;
use App\Exceptions\BusinessException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected RoleService $roleService
    ) {}

    public function index(Request $request)
    {
        try {
            $filters = [
                'guard' => $request->input('guard'),
                'keyword' => $request->input('keyword'),
                'status' => $request->input('status'),
            ];

            $perPage = (int) $request->input('per_page', 15);
            $result = $this->roleService->getRoleList($filters, $perPage);

            return $this->paginatedWithStats(
                $result['paginator'],
                $result['stats']
            );
        } catch (BusinessException $e) {
            return $this->error($e->getMessage(), $e->getErrorCode());
        } catch (\Exception $e) {
            return $this->error('获取角色列表失败', 500);
        }
    }

    public function show($id)
    {
        try {
            $role = $this->roleService->getRoleById((int) $id);

            return $this->success($role);
        } catch (BusinessException $e) {
            return $this->error($e->getMessage(), $e->getErrorCode());
        } catch (\Exception $e) {
            return $this->error('获取角色详情失败', 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:50',
                'guard' => 'required|string|max:50',
                'display_name' => 'required|string|max:100',
                'description' => 'nullable|string|max:500',
                'status' => 'boolean',
                'sort_order' => 'integer|min:0',
                'permissions' => 'array',
                'permissions.*' => 'exists:permissions,id',
            ], [
                'name.required' => '角色标识不能为空',
                'guard.required' => '守卫端不能为空',
                'display_name.required' => '角色名称不能为空',
            ]);

            if ($validator->fails()) {
                return $this->error('参数验证失败', 422, $validator->errors());
            }

            $role = $this->roleService->createRole($validator->validated());

            return $this->success($role, '角色创建成功', 201);
        } catch (BusinessException $e) {
            return $this->error($e->getMessage(), $e->getErrorCode());
        } catch (\Exception $e) {
            return $this->error('创建角色失败', 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'string|max:50',
                'guard' => 'string|max:50',
                'display_name' => 'string|max:100',
                'description' => 'nullable|string|max:500',
                'status' => 'boolean',
                'sort_order' => 'integer|min:0',
                'permissions' => 'array',
                'permissions.*' => 'exists:permissions,id',
            ]);

            if ($validator->fails()) {
                return $this->error('参数验证失败', 422, $validator->errors());
            }

            $role = $this->roleService->updateRole((int) $id, $validator->validated());

            return $this->success($role, '角色更新成功');
        } catch (BusinessException $e) {
            return $this->error($e->getMessage(), $e->getErrorCode());
        } catch (\Exception $e) {
            return $this->error('更新角色失败', 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->roleService->deleteRole((int) $id);

            return $this->success(null, '角色删除成功');
        } catch (BusinessException $e) {
            return $this->error($e->getMessage(), $e->getErrorCode());
        } catch (\Exception $e) {
            return $this->error('删除角色失败', 500);
        }
    }

    public function toggleStatus($id)
    {
        try {
            $role = $this->roleService->toggleRoleStatus((int) $id);

            return $this->success(
                ['status' => $role->status],
                $role->status ? '角色已启用' : '角色已禁用'
            );
        } catch (BusinessException $e) {
            return $this->error($e->getMessage(), $e->getErrorCode());
        } catch (\Exception $e) {
            return $this->error('状态切换失败', 500);
        }
    }

    public function all(Request $request)
    {
        try {
            $guard = $request->input('guard');
            $roles = $this->roleService->getAllRoles($guard);

            return $this->success($roles);
        } catch (BusinessException $e) {
            return $this->error($e->getMessage(), $e->getErrorCode());
        } catch (\Exception $e) {
            return $this->error('获取角色列表失败', 500);
        }
    }
}
