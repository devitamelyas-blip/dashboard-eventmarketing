<?php

namespace App\Enums;

enum DataStatus: string
{
    case DATA_BELUM_CUKUP = 'data_belum_cukup';
    case BELUM_JATUH_TEMPO = 'belum_jatuh_tempo';
    case MENUNGGU_LAPORAN = 'menunggu_laporan';
    case MENUNGGU_VERIFIKASI = 'menunggu_verifikasi';
    case TERVERIFIKASI = 'terverifikasi';

    public function label(): string
    {
        return match ($this) {
            self::DATA_BELUM_CUKUP => 'Data Belum Cukup',
            self::BELUM_JATUH_TEMPO => 'Belum Jatuh Tempo (H+90)',
            self::MENUNGGU_LAPORAN => 'Menunggu Laporan H+90',
            self::MENUNGGU_VERIFIKASI => 'Menunggu Verifikasi',
            self::TERVERIFIKASI => 'Terverifikasi',
        };
    }
}
