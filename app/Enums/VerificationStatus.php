<?php

namespace App\Enums;

enum VerificationStatus: string
{
    case BELUM_DIPERIKSA = 'belum_diperiksa';
    case TERVERIFIKASI = 'terverifikasi';
    case PERLU_KOREKSI = 'perlu_koreksi';

    public function label(): string
    {
        return match ($this) {
            self::BELUM_DIPERIKSA => 'Belum Diperiksa',
            self::TERVERIFIKASI => 'Terverifikasi',
            self::PERLU_KOREKSI => 'Perlu Koreksi',
        };
    }
}
