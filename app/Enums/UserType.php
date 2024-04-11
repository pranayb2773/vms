<?php

namespace App\Enums;

enum UserType: string
{
    case EXTERNAL = 'external';
    case INTERNAL = 'internal';

    public function isInternal(): bool
    {
        return $this === UserType::INTERNAL;
    }

    public function isExternal(): bool
    {
        return $this === UserType::EXTERNAL;
    }

    public function name(): string
    {
        return match ($this) {
            self::EXTERNAL => 'External',
            self::INTERNAL => 'Internal',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::EXTERNAL => 'danger',
            self::INTERNAL => 'primary',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::EXTERNAL => 'heroicon-m-arrow-right-circle',
            self::INTERNAL => 'heroicon-m-arrow-left-circle',
        };
    }

    public static function toArray(): array
    {
        return [
            [
                'id' => UserType::INTERNAL->value,
                'name' => UserType::INTERNAL->name(),
                'summary' => 'Internal indicate that user is belongs to Organisation.',
                'color' => UserType::INTERNAL->color(),
                'icon' => UserType::INTERNAL->icon(),
            ],
            [
                'id' => UserType::EXTERNAL->value,
                'name' => UserType::EXTERNAL->name(),
                'summary' => 'External indicate that user is visitor.',
                'color' => UserType::EXTERNAL->color(),
                'icon' => UserType::EXTERNAL->icon(),
            ],
        ];
    }
}
