<?php

namespace App\Enums;

enum GuardType: string
{
    case PLATFORM = 'platform';
    case MERCHANT = 'merchant';
    case WAREHOUSE = 'warehouse';

    public function label(): string
    {
        return match ($this) {
            self::PLATFORM => '平台端',
            self::MERCHANT => '商家端',
            self::WAREHOUSE => '仓库端',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function isValid(string $guard): bool
    {
        return in_array($guard, self::values(), true);
    }
}
