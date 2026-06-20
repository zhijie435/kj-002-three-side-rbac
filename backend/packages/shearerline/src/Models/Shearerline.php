<?php

namespace Shearerline\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shearerline extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'type',
        'location',
        'status',
        'max_capacity',
        'current_load',
        'operator_id',
        'description',
        'sort_order',
    ];

    protected $casts = [
        'max_capacity' => 'integer',
        'current_load' => 'integer',
        'sort_order' => 'integer',
    ];

    public function tasks()
    {
        return $this->hasMany(ShearerlineTask::class);
    }

    public function pendingTasks()
    {
        return $this->tasks()->whereIn('status', ['pending', 'assigned', 'processing']);
    }

    public function completedTasks()
    {
        return $this->tasks()->where('status', 'completed');
    }

    public function start()
    {
        $this->status = 'running';
        $this->save();
    }

    public function stop()
    {
        $this->status = 'idle';
        $this->save();
    }

    public function setMaintenance()
    {
        $this->status = 'maintenance';
        $this->save();
    }

    public function setError()
    {
        $this->status = 'error';
        $this->save();
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('id', 'desc');
    }

    public function getStatusLabelAttribute()
    {
        $statuses = config('shearerline.statuses', []);
        return $statuses[$this->status] ?? $this->status;
    }
}
