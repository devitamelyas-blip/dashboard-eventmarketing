<?php

namespace App\Enums;

enum Goal: string
{
    case SALES = 'sales';
    case AWARENESS = 'awareness';
    case RELATIONSHIP = 'relationship';

    public function label(): string
    {
        return match ($this) {
            self::SALES => 'Sales',
            self::AWARENESS => 'Awareness',
            self::RELATIONSHIP => 'Relationship',
        };
    }
}
