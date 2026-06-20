<?php

namespace App\Models;

use App\Traits\HasGuardScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory, HasGuardScopes;

    protected $fillable = [
        'name',
        'guard',
        'display_name',
        'group',
        'description',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('guard')->orderBy('group')->orderBy('id');
    }
}
