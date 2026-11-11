<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Otomatisasi pengingat H+90 batas waktu laporan omzet event sales (setiap hari pkl 08:00)
Schedule::command('events:remind-h90')->dailyAt('08:00');

// Otomatisasi penyamaran identitas responden lama (>365 hari) demi retensi privasi (setiap hari pkl 02:00)
Schedule::command('events:anonymize-respondents')->dailyAt('02:00');
