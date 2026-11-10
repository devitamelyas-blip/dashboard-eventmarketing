<?php

namespace App\Enums;

enum ReportStage: string
{
    case PROPOSAL = 'proposal';
    case POST_EVENT = 'post_event';
    case SALES_H90 = 'sales_h90';

    public function label(): string
    {
        return match ($this) {
            self::PROPOSAL => 'Tahap 1: Pengajuan Event',
            self::POST_EVENT => 'Tahap 2: Laporan Pasca-Event',
            self::SALES_H90 => 'Tahap 3: Pembaruan Omzet H+90',
        };
    }
}
