<?php

namespace Shearerline\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Shearerline\Models\Shearerline;
use Shearerline\Http\Requests\StoreShearerlineRequest;
use Shearerline\Http\Requests\UpdateShearerlineRequest;
use Illuminate\Support\Facades\DB;

class ShearerlineController extends Controller
{
    public function index(Request $request)
    {
        $query = Shearerline::withCount([
            'tasks as task_count',
            'pendingTasks as pending_task_count',
            'completedTasks as completed_task_count',
        ])->ordered();

        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->where('code', 'like', "%{$keyword}%")
                    ->orWhere('name', 'like', "%{$keyword}%")
                    ->orWhere('type', 'like', "%{$keyword}%")
                    ->orWhere('location', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('status')) {
            $query->byStatus($request->input('status'));
        }

        if ($request->filled('type')) {
            $query->byType($request->input('type'));
        }

        $perPage = $request->input('per_page', config('shearerline.pagination.per_page', 15));
        $shearers = $query->paginate($perPage);

        $statsQuery = Shearerline::query();
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $statsQuery->where(function ($q) use ($keyword) {
                $q->where('code', 'like', "%{$keyword}%")
                    ->orWhere('name', 'like', "%{$keyword}%")
                    ->orWhere('type', 'like', "%{$keyword}%")
                    ->orWhere('location', 'like', "%{$keyword}%");
            });
        }
        if ($request->filled('status')) {
            $statsQuery->byStatus($request->input('status'));
        }

        $totalCount = (clone $statsQuery)->count();
        $idleCount = (clone $statsQuery)->byStatus('idle')->count();
        $runningCount = (clone $statsQuery)->byStatus('running')->count();
        $maintenanceCount = (clone $statsQuery)->byStatus('maintenance')->count();
        $errorCount = (clone $statsQuery)->byStatus('error')->count();

        $list = $shearers->items();
        foreach ($list as &$item) {
            $item->status_label = $item->status_label;
            $item->load_ratio = $item->max_capacity > 0 ? round(($item->current_load / $item->max_capacity) * 100, 1) : 0;
        }

        return response()->json([
            'code' => 0,
            'message' => 'success',
            'data' => [
                'list' => $list,
                'pagination' => [
                    'total' => $shearers->total(),
                    'page' => $shearers->currentPage(),
                    'per_page' => $shearers->perPage(),
                    'total_pages' => $shearers->lastPage(),
                ],
                'stats' => [
                    'total' => $totalCount,
                    'idle' => $idleCount,
                    'running' => $runningCount,
                    'maintenance' => $maintenanceCount,
                    'error' => $errorCount,
                ],
            ],
        ]);
    }

    public function show($id)
    {
        $shearerline = Shearerline::with([
            'tasks' => function ($q) {
                $q->ordered()->limit(10);
            },
        ])->withCount([
            'tasks as task_count',
            'pendingTasks as pending_task_count',
            'completedTasks as completed_task_count',
        ])->findOrFail($id);

        $shearerline->status_label = $shearerline->status_label;
        $shearerline->load_ratio = $shearerline->max_capacity > 0 ? round(($shearerline->current_load / $shearerline->max_capacity) * 100, 1) : 0;

        return response()->json([
            'code' => 0,
            'message' => 'success',
            'data' => $shearerline,
        ]);
    }

    public function store(StoreShearerlineRequest $request)
    {
        DB::beginTransaction();
        try {
            $shearerline = Shearerline::create($request->validated());
            DB::commit();

            $shearerline->status_label = $shearerline->status_label;

            return response()->json([
                'code' => 0,
                'message' => '剪切线创建成功',
                'data' => $shearerline,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'message' => '创建失败：' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateShearerlineRequest $request, $id)
    {
        $shearerline = Shearerline::findOrFail($id);

        DB::beginTransaction();
        try {
            $shearerline->update($request->validated());
            DB::commit();

            $shearerline->status_label = $shearerline->status_label;

            return response()->json([
                'code' => 0,
                'message' => '剪切线更新成功',
                'data' => $shearerline,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'message' => '更新失败：' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $shearerline = Shearerline::findOrFail($id);

        DB::beginTransaction();
        try {
            $shearerline->tasks()->update(['shearerline_id' => null]);
            $shearerline->delete();
            DB::commit();

            return response()->json([
                'code' => 0,
                'message' => '剪切线删除成功',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'message' => '删除失败：' . $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus($id, $action)
    {
        $shearerline = Shearerline::findOrFail($id);

        $validActions = ['start', 'stop', 'maintenance', 'error'];
        if (!in_array($action, $validActions)) {
            return response()->json([
                'code' => 400,
                'message' => '无效的操作',
            ], 400);
        }

        switch ($action) {
            case 'start':
                $shearerline->start();
                $message = '剪切线已启动';
                break;
            case 'stop':
                $shearerline->stop();
                $message = '剪切线已停止';
                break;
            case 'maintenance':
                $shearerline->setMaintenance();
                $message = '剪切线设置为维护中';
                break;
            case 'error':
                $shearerline->setError();
                $message = '剪切线设置为故障状态';
                break;
        }

        return response()->json([
            'code' => 0,
            'message' => $message,
            'data' => [
                'status' => $shearerline->status,
                'status_label' => $shearerline->status_label,
            ],
        ]);
    }

    public function all()
    {
        $shearers = Shearerline::where('status', '!=', 'disabled')
            ->ordered()
            ->get(['id', 'code', 'name', 'type', 'status', 'max_capacity', 'current_load']);

        foreach ($shearers as &$item) {
            $item->status_label = $item->status_label;
            $item->load_ratio = $item->max_capacity > 0 ? round(($item->current_load / $item->max_capacity) * 100, 1) : 0;
        }

        return response()->json([
            'code' => 0,
            'message' => 'success',
            'data' => $shearers,
        ]);
    }
}
