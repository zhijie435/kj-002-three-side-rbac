<?php

return [
    'name' => 'Shearerline 剪切线管理',
    'version' => '1.0.0',
    'default_status' => 'idle',
    'statuses' => [
        'idle' => '空闲',
        'running' => '运行中',
        'maintenance' => '维护中',
        'error' => '故障',
        'disabled' => '已停用',
    ],
    'task_priorities' => [
        'low' => '低',
        'medium' => '中',
        'high' => '高',
        'urgent' => '紧急',
    ],
    'task_statuses' => [
        'pending' => '待处理',
        'assigned' => '已分配',
        'processing' => '处理中',
        'completed' => '已完成',
        'cancelled' => '已取消',
    ],
    'pagination' => [
        'per_page' => 15,
    ],
];
