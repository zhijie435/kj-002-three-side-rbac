<?php

namespace App\Traits;

use App\Enums\GuardType;
use Illuminate\Database\Eloquent\Builder;

trait HasGuardScopes
{
    public function scopeByGuard(Builder $query, string $guard): Builder
    {
        return $query->where('guard', $guard);
    }

    public function scopePlatform(Builder $query): Builder
    {
        return $query->where('guard', GuardType::PLATFORM->value);
    }

    public function scopeMerchant(Builder $query): Builder
    {
        return $query->where('guard', GuardType::MERCHANT->value);
    }

    public function scopeWarehouse(Builder $query): Builder
    {
        return $query->where('guard', GuardType::WAREHOUSE->value);
    }

    public function scopeValidGuard(Builder $query): Builder
    {
        return $query->whereIn('guard', GuardType::values());
    }
}
