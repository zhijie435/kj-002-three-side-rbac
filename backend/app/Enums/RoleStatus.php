<?php

namespace App\Enums;

enum RoleStatus: int
{
    case ACTIVE = 1;
    case INACTIVE = 0;

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => '已启用',
            self::INACTIVE => '已禁用',
        };
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public static function fromBoolean(bool $status): self
    {
        return $status ? self::ACTIVE : self::INACTIVE;
    }
}
