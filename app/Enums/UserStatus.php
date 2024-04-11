<?php

namespace App\Enums;

enum UserStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case PENDING = 'pending';

    public function isActive(): bool
    {
        return $this === UserStatus::ACTIVE;
    }

    public function isInactive(): bool
    {
        return $this === UserStatus::INACTIVE;
    }

    public function isPending(): bool
    {
        return $this === UserStatus::PENDING;
    }

    public function name(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
            self::PENDING => 'Pending',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'primary',
            self::INACTIVE => 'danger',
            self::PENDING => 'warning',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::ACTIVE => 'heroicon-m-check-circle',
            self::INACTIVE => 'heroicon-m-exclamation-triangle',
            self::PENDING => 'heroicon-m-exclamation-circle',
        };
    }

    public static function toArray(): array
    {
        return [
            [
                'id' => UserStatus::ACTIVE->value,
                'name' => UserStatus::ACTIVE->name(),
                'summary' => 'It indicates user status in active.',
                'color' => UserStatus::ACTIVE->color(),
                'icon' => UserStatus::ACTIVE->icon(),
            ],
            [
                'id' => UserStatus::INACTIVE->value,
                'name' => UserStatus::INACTIVE->name(),
                'summary' => 'It indicates user status in inactive.',
                'color' => UserStatus::INACTIVE->color(),
                'icon' => UserStatus::INACTIVE->icon(),
            ],
            [
                'id' => UserStatus::PENDING->value,
                'name' => UserStatus::PENDING->name(),
                'summary' => 'It indicates user status in pending.',
                'color' => UserStatus::PENDING->color(),
                'icon' => UserStatus::PENDING->icon(),
            ],
        ];
    }
}
