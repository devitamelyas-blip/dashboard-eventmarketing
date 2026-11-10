<?php

namespace App\Services\Evaluation;

class SurveyThreshold
{
    /**
     * Menghitung ambang jumlah survei minimum berdasarkan peserta aktual:
     * - <= 30 peserta: ceil(80% * peserta)
     * - 31-100 peserta: 30 respons
     * - > 100 peserta: 50 respons
     *
     * Jika peserta aktual belum dilaporkan (null), mengembalikan null.
     */
    public static function calculate(?int $actualParticipants): ?int
    {
        if ($actualParticipants === null) {
            return null;
        }

        if ($actualParticipants <= 0) {
            return 0;
        }

        if ($actualParticipants <= 30) {
            return (int) ceil(0.80 * $actualParticipants);
        }

        if ($actualParticipants <= 100) {
            return 30;
        }

        return 50;
    }
}
