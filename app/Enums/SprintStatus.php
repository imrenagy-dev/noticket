<?php

namespace App\Enums;

enum SprintStatus: string
{
    case Planned   = 'planned';
    case Active    = 'active';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Planned   => 'Planned',
            self::Active    => 'Active',
            self::Completed => 'Completed',
        };
    }
}
