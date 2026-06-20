<?php

namespace Shearerline\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShearerlineTask extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'shearerline_id',
        'order_no',
        'product_name',
        'quantity',
        'priority',
        'status',
        'started_at',
        'completed_at',
        'operator_id',
        'description',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'sort_order' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function shearerline()
    {
        return $this->belongsTo(Shearerline::class);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByShearerline($query, int $shearerlineId)
    {
        return $query->where('shearerline_id', $shearerlineId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('id', 'desc');
    }

    public function assign(int $shearerlineId)
    {
        $this->shearerline_id = $shearerlineId;
        $this->status = 'assigned';
        $this->save();
    }

    public function start()
    {
        $this->status = 'processing';
        $this->started_at = now();
        $this->save();
    }

    public function complete()
    {
        $this->status = 'completed';
        $this->completed_at = now();
        $this->save();
    }

    public function cancel()
    {
        $this->status = 'cancelled';
        $this->save();
    }

    public function getPriorityLabelAttribute()
    {
        $priorities = config('shearerline.task_priorities', []);
        return $priorities[$this->priority] ?? $this->priority;
    }

    public function getStatusLabelAttribute()
    {
        $statuses = config('shearerline.task_statuses', []);
        return $statuses[$this->status] ?? $this->status;
    }
}
