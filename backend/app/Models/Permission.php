<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

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

    public function scopeByGuard($query, string $guard)
    {
        return $query->where('guard', $guard);
    }

    public function scopePlatform($query)
    {
        return $query->where('guard', 'platform');
    }

    public function scopeMerchant($query)
    {
        return $query->where('guard', 'merchant');
    }

    public function scopeWarehouse($query)
    {
        return $query->where('guard', 'warehouse');
    }
}
