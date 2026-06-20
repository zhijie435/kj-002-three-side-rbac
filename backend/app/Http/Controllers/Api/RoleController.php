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
        $query = Role::with(['permissions:id,name,display_name,group'])
            ->withCount([
                'permissions as permission_count',
            ])
            ->ordered();

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

        $totalQuery = Role::query();
        $keywordFilter($totalQuery);
        if ($request->filled('status')) {
            $totalQuery->where('status', $request->input('status') === '1');
        }
        $totalCount = $totalQuery->count();

        $activeQuery = Role::query();
        $keywordFilter($activeQuery);
        $activeCount = $activeQuery->where('status', true)->count();

        $inactiveQuery = Role::query();
        $keywordFilter($inactiveQuery);
        $inactiveCount = $inactiveQuery->where('status', false)->count();

        $systemQuery = Role::query();
        $keywordFilter($systemQuery);
        if ($request->filled('status')) {
            $systemQuery->where('status', $request->input('status') === '1');
        }
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
        $role = Role::with(['permissions:id,name,display_name,group'])
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
            'name' => 'required|string|max:50|unique:roles,name',
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'status' => 'boolean',
            'sort_order' => 'integer|min:0',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ], [
            'name.required' => '角色标识不能为空',
            'name.unique' => '角色标识已存在',
            'display_name.required' => '角色名称不能为空',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'message' => '参数验证失败',
                'errors' => $validator->errors(),
            ], 422);
        }

        $role = Role::create([
            'name' => $request->input('name'),
            'display_name' => $request->input('display_name'),
            'description' => $request->input('description'),
            'status' => $request->input('status', true),
            'sort_order' => $request->input('sort_order', 0),
        ]);

        if ($request->filled('permissions')) {
            $role->syncPermissions($request->input('permissions'));
        }

        $role->load(['permissions:id,name,display_name,group']);

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
            'name' => 'string|max:50|unique:roles,name,' . $role->id,
            'display_name' => 'string|max:100',
            'description' => 'nullable|string|max:500',
            'status' => 'boolean',
            'sort_order' => 'integer|min:0',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ], [
            'name.unique' => '角色标识已存在',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'message' => '参数验证失败',
                'errors' => $validator->errors(),
            ], 422);
        }

        $role->update($request->only([
            'name',
            'display_name',
            'description',
            'status',
            'sort_order',
        ]));

        if ($request->has('permissions')) {
            $role->syncPermissions($request->input('permissions', []));
        }

        $role->load(['permissions:id,name,display_name,group']);

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

    public function all()
    {
        $roles = Role::active()
            ->ordered()
            ->get(['id', 'name', 'display_name']);

        return response()->json([
            'code' => 0,
            'message' => 'success',
            'data' => $roles,
        ]);
    }
}
