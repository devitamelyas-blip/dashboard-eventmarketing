<?php

namespace App\Enums;

enum EventType: string
{
    case GATHERING_INTERNAL = 'gathering_internal';
    case JOIN_EXTERNAL = 'join_external';

    public function label(): string
    {
        return match ($this) {
            self::GATHERING_INTERNAL => 'Gathering / Internal Event',
            self::JOIN_EXTERNAL => 'Join Event / External Event',
        };
    }
}
