<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $query = Permission::query()->orderBy('group')->orderBy('id');

        if ($request->filled('group')) {
            $query->byGroup($request->input('group'));
        }

        $permissions = $query->get();

        $grouped = $permissions->groupBy('group')->map(function ($items, $group) {
            return [
                'group' => $group,
                'group_name' => $this->getGroupName($group),
                'permissions' => $items,
            ];
        })->values();

        return response()->json([
            'code' => 0,
            'message' => 'success',
            'data' => $grouped,
        ]);
    }

    public function all()
    {
        $permissions = Permission::query()
            ->orderBy('group')
            ->orderBy('id')
            ->get(['id', 'name', 'display_name', 'group']);

        $grouped = $permissions->groupBy('group')->map(function ($items, $group) {
            return [
                'group' => $group,
                'group_name' => $this->getGroupName($group),
                'children' => $items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'display_name' => $item->display_name,
                    ];
                }),
            ];
        })->values();

        return response()->json([
            'code' => 0,
            'message' => 'success',
            'data' => $grouped,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:permissions,name',
            'display_name' => 'required|string|max:100',
            'group' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
        ]);

        $permission = Permission::create($validated);

        return response()->json([
            'code' => 0,
            'message' => '权限创建成功',
            'data' => $permission,
        ], 201);
    }

    private function getGroupName(string $group): string
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
        ];

        return $names[$group] ?? $group;
    }
}
