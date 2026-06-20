<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $query = Role::with(['permissions:id,name,display_name,group,guard'])
            ->withCount([
                'permissions as permission_count',
            ])
            ->ordered();

        if ($request->filled('guard')) {
            $query->byGuard($request->input('guard'));
        }

        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('display_name', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status') === '1');
        }

        $perPage = $request->input('per_page', 15);
        $roles = $query->paginate($perPage);

        $keywordFilter = function ($q) use ($request) {
            if ($request->filled('keyword')) {
                $keyword = $request->input('keyword');
                $q->where(function ($query) use ($keyword) {
                    $query->where('name', 'like', "%{$keyword}%")
                        ->orWhere('display_name', 'like', "%{$keyword}%")
                        ->orWhere('description', 'like', "%{$keyword}%");
                });
            }
        };

        $guardFilter = function ($q) use ($request) {
            if ($request->filled('guard')) {
                $q->where('guard', $request->input('guard'));
            }
        };

        $statusFilter = function ($q) use ($request) {
            if ($request->filled('status')) {
                $q->where('status', $request->input('status') === '1');
            }
        };

        $totalQuery = Role::query();
        $guardFilter($totalQuery);
        $keywordFilter($totalQuery);
        $statusFilter($totalQuery);
        $totalCount = $totalQuery->count();

        $activeQuery = Role::query();
        $guardFilter($activeQuery);
        $keywordFilter($activeQuery);
        $statusFilter($activeQuery);
        $activeCount = $activeQuery->where('status', true)->count();

        $inactiveQuery = Role::query();
        $guardFilter($inactiveQuery);
        $keywordFilter($inactiveQuery);
        $statusFilter($inactiveQuery);
        $inactiveCount = $inactiveQuery->where('status', false)->count();

        $systemQuery = Role::query();
        $guardFilter($systemQuery);
        $keywordFilter($systemQuery);
        $statusFilter($systemQuery);
        $systemCount = $systemQuery->where('is_system', true)->count();

        return response()->json([
            'code' => 0,
            'message' => 'success',
            'data' => [
                'list' => $roles->items(),
                'pagination' => [
                    'total' => $roles->total(),
                    'page' => $roles->currentPage(),
                    'per_page' => $roles->perPage(),
                    'total_pages' => $roles->lastPage(),
                ],
                'stats' => [
                    'total' => $totalCount,
                    'active' => $activeCount,
                    'inactive' => $inactiveCount,
                    'system' => $systemCount,
                ],
            ],
        ]);
    }

    public function show($id)
    {
        $role = Role::with(['permissions:id,name,display_name,group,guard'])
            ->findOrFail($id);

        return response()->json([
            'code' => 0,
            'message' => 'success',
            'data' => $role,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'guard' => 'required|string|max:50|in:platform,merchant,warehouse',
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'status' => 'boolean',
            'sort_order' => 'integer|min:0',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ], [
            'name.required' => '角色标识不能为空',
            'guard.required' => '守卫端不能为空',
            'guard.in' => '守卫端值不正确',
            'display_name.required' => '角色名称不能为空',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'message' => '参数验证失败',
                'errors' => $validator->errors(),
            ], 422);
        }

        $exists = Role::where('guard', $request->input('guard'))
            ->where('name', $request->input('name'))
            ->exists();
        if ($exists) {
            return response()->json([
                'code' => 422,
                'message' => '该守卫端下角色标识已存在',
            ], 422);
        }

        $role = Role::create([
            'name' => $request->input('name'),
            'guard' => $request->input('guard'),
            'display_name' => $request->input('display_name'),
            'description' => $request->input('description'),
            'status' => $request->input('status', true),
            'sort_order' => $request->input('sort_order', 0),
        ]);

        if ($request->filled('permissions')) {
            $guardPermissions = Permission::where('guard', $request->input('guard'))
                ->pluck('id')
                ->toArray();
            $validPermissions = array_intersect($request->input('permissions'), $guardPermissions);
            $role->syncPermissions($validPermissions);
        }

        $role->load(['permissions:id,name,display_name,group,guard']);

        return response()->json([
            'code' => 0,
            'message' => '角色创建成功',
            'data' => $role,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        if ($role->is_system) {
            return response()->json([
                'code' => 403,
                'message' => '系统内置角色不允许修改',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:50',
            'guard' => 'string|max:50|in:platform,merchant,warehouse',
            'display_name' => 'string|max:100',
            'description' => 'nullable|string|max:500',
            'status' => 'boolean',
            'sort_order' => 'integer|min:0',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ], [
            'guard.in' => '守卫端值不正确',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'message' => '参数验证失败',
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($request->has('name') || $request->has('guard')) {
            $guard = $request->input('guard', $role->guard);
            $name = $request->input('name', $role->name);
            $exists = Role::where('guard', $guard)
                ->where('name', $name)
                ->where('id', '!=', $role->id)
                ->exists();
            if ($exists) {
                return response()->json([
                    'code' => 422,
                    'message' => '该守卫端下角色标识已存在',
                ], 422);
            }
        }

        $role->update($request->only([
            'name',
            'guard',
            'display_name',
            'description',
            'status',
            'sort_order',
        ]));

        if ($request->has('permissions')) {
            $guardPermissions = Permission::where('guard', $role->guard)
                ->pluck('id')
                ->toArray();
            $validPermissions = array_intersect($request->input('permissions', []), $guardPermissions);
            $role->syncPermissions($validPermissions);
        }

        $role->load(['permissions:id,name,display_name,group,guard']);

        return response()->json([
            'code' => 0,
            'message' => '角色更新成功',
            'data' => $role,
        ]);
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        if ($role->is_system) {
            return response()->json([
                'code' => 403,
                'message' => '系统内置角色不允许删除',
            ], 403);
        }

        $role->permissions()->detach();
        $role->delete();

        return response()->json([
            'code' => 0,
            'message' => '角色删除成功',
        ]);
    }

    public function toggleStatus($id)
    {
        $role = Role::findOrFail($id);

        if ($role->is_system) {
            return response()->json([
                'code' => 403,
                'message' => '系统内置角色不允许修改状态',
            ], 403);
        }

        $role->status = !$role->status;
        $role->save();

        return response()->json([
            'code' => 0,
            'message' => $role->status ? '角色已启用' : '角色已禁用',
            'data' => ['status' => $role->status],
        ]);
    }

    public function all(Request $request)
    {
        $query = Role::active()->ordered();

        if ($request->filled('guard')) {
            $query->byGuard($request->input('guard'));
        }

        $roles = $query->get(['id', 'name', 'guard', 'display_name']);

        return response()->json([
            'code' => 0,
            'message' => 'success',
            'data' => $roles,
        ]);
    }
}
