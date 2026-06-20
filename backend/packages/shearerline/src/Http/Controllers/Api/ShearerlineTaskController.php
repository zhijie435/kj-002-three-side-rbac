<?php

namespace Shearerline\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Shearerline\Models\ShearerlineTask;
use Shearerline\Http\Requests\StoreShearerlineTaskRequest;
use Shearerline\Http\Requests\UpdateShearerlineTaskRequest;
use Illuminate\Support\Facades\DB;

class ShearerlineTaskController extends Controller
{
    public function index(Request $request)
    {
        $query = ShearerlineTask::with(['shearerline:id,code,name'])
            ->ordered();

        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->where('order_no', 'like', "%{$keyword}%")
                    ->orWhere('product_name', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('status')) {
            $query->byStatus($request->input('status'));
        }

        if ($request->filled('priority')) {
            $query->byPriority($request->input('priority'));
        }

        if ($request->filled('shearerline_id')) {
            $query->byShearerline($request->input('shearerline_id'));
        }

        $perPage = $request->input('per_page', config('shearerline.pagination.per_page', 15));
        $tasks = $query->paginate($perPage);

        $statsQuery = ShearerlineTask::query();
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $statsQuery->where(function ($q) use ($keyword) {
                $q->where('order_no', 'like', "%{$keyword}%")
                    ->orWhere('product_name', 'like', "%{$keyword}%");
            });
        }
        if ($request->filled('shearerline_id')) {
            $statsQuery->byShearerline($request->input('shearerline_id'));
        }

        $totalCount = (clone $statsQuery)->count();
        $pendingCount = (clone $statsQuery)->byStatus('pending')->count();
        $processingCount = (clone $statsQuery)->byStatus('processing')->count();
        $completedCount = (clone $statsQuery)->byStatus('completed')->count();

        $list = $tasks->items();
        foreach ($list as &$item) {
            $item->status_label = $item->status_label;
            $item->priority_label = $item->priority_label;
        }

        return response()->json([
            'code' => 0,
            'message' => 'success',
            'data' => [
                'list' => $list,
                'pagination' => [
                    'total' => $tasks->total(),
                    'page' => $tasks->currentPage(),
                    'per_page' => $tasks->perPage(),
                    'total_pages' => $tasks->lastPage(),
                ],
                'stats' => [
                    'total' => $totalCount,
                    'pending' => $pendingCount,
                    'processing' => $processingCount,
                    'completed' => $completedCount,
                ],
            ],
        ]);
    }

    public function show($id)
    {
        $task = ShearerlineTask::with(['shearerline:id,code,name'])->findOrFail($id);
        $task->status_label = $task->status_label;
        $task->priority_label = $task->priority_label;

        return response()->json([
            'code' => 0,
            'message' => 'success',
            'data' => $task,
        ]);
    }

    public function store(StoreShearerlineTaskRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            if (isset($data['shearerline_id']) && !isset($data['status'])) {
                $data['status'] = 'assigned';
            }
            $task = ShearerlineTask::create($data);
            DB::commit();

            $task->status_label = $task->status_label;
            $task->priority_label = $task->priority_label;
            $task->load('shearerline:id,code,name');

            return response()->json([
                'code' => 0,
                'message' => '任务创建成功',
                'data' => $task,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'message' => '创建失败：' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateShearerlineTaskRequest $request, $id)
    {
        $task = ShearerlineTask::findOrFail($id);

        DB::beginTransaction();
        try {
            $task->update($request->validated());
            DB::commit();

            $task->status_label = $task->status_label;
            $task->priority_label = $task->priority_label;
            $task->load('shearerline:id,code,name');

            return response()->json([
                'code' => 0,
                'message' => '任务更新成功',
                'data' => $task,
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
        $task = ShearerlineTask::findOrFail($id);

        DB::beginTransaction();
        try {
            $task->delete();
            DB::commit();

            return response()->json([
                'code' => 0,
                'message' => '任务删除成功',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'message' => '删除失败：' . $e->getMessage(),
            ], 500);
        }
    }

    public function assign($id, Request $request)
    {
        $task = ShearerlineTask::findOrFail($id);
        $shearerlineId = $request->input('shearerline_id');

        if (!$shearerlineId) {
            return response()->json([
                'code' => 400,
                'message' => '请选择剪切线',
            ], 400);
        }

        DB::beginTransaction();
        try {
            $task->assign($shearerlineId);
            DB::commit();

            $task->status_label = $task->status_label;
            $task->load('shearerline:id,code,name');

            return response()->json([
                'code' => 0,
                'message' => '任务分配成功',
                'data' => $task,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'message' => '分配失败：' . $e->getMessage(),
            ], 500);
        }
    }

    public function start($id)
    {
        $task = ShearerlineTask::findOrFail($id);

        DB::beginTransaction();
        try {
            $task->start();
            DB::commit();

            $task->status_label = $task->status_label;

            return response()->json([
                'code' => 0,
                'message' => '任务已开始',
                'data' => $task,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'message' => '操作失败：' . $e->getMessage(),
            ], 500);
        }
    }

    public function complete($id)
    {
        $task = ShearerlineTask::findOrFail($id);

        DB::beginTransaction();
        try {
            $task->complete();
            DB::commit();

            $task->status_label = $task->status_label;

            return response()->json([
                'code' => 0,
                'message' => '任务已完成',
                'data' => $task,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'message' => '操作失败：' . $e->getMessage(),
            ], 500);
        }
    }

    public function cancel($id)
    {
        $task = ShearerlineTask::findOrFail($id);

        DB::beginTransaction();
        try {
            $task->cancel();
            DB::commit();

            $task->status_label = $task->status_label;

            return response()->json([
                'code' => 0,
                'message' => '任务已取消',
                'data' => $task,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'message' => '操作失败：' . $e->getMessage(),
            ], 500);
        }
    }
}
