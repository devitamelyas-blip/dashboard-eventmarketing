<?php

namespace App\Enums;

enum EventPortfolio: string
{
    case OPERATIONAL = 'operational';
    case DEMO = 'demo';
    case UAT = 'uat';

    public function label(): string
    {
        return match ($this) {
            self::OPERATIONAL => 'Operasional',
            self::DEMO => 'Demo',
            self::UAT => 'UAT',
        };
    }

    public static function fromCode(string $code): self
    {
        $normalized = strtoupper(trim($code));

        if (str_starts_with($normalized, 'DEMO-')) {
            return self::DEMO;
        }

        if (str_starts_with($normalized, 'UAT-')) {
            return self::UAT;
        }

        return self::OPERATIONAL;
    }
}
