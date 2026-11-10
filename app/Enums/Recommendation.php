<?php

namespace App\Enums;

enum Recommendation: string
{
    case LAYAK = 'layak';
    case PERLU_EVALUASI = 'perlu_evaluasi';
    case TIDAK_LAYAK = 'tidak_layak';
    case BELUM_FINAL = 'belum_final';

    public function label(): string
    {
        return match ($this) {
            self::LAYAK => 'Layak diikuti lagi',
            self::PERLU_EVALUASI => 'Perlu evaluasi',
            self::TIDAK_LAYAK => 'Tidak layak',
            self::BELUM_FINAL => 'Belum final – belum dapat dinilai',
        };
    }
}
