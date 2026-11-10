<?php

use App\Services\Evaluation\SalesEvaluator;
use Illuminate\Support\Carbon;

test('sales evaluation before H+90 returns belum_jatuh_tempo', function () {
    $eventDate = Carbon::parse('2026-08-01');
    $asOfDate = Carbon::parse('2026-10-29'); // Exactly 89 days after (not yet 90)

    $result = SalesEvaluator::evaluate($eventDate, 10000000.0, 150000000.0, true, $asOfDate);

    expect($result['is_due'])->toBeFalse();
    expect($result['days_remaining'])->toBe(1);
    expect($result['status'])->toBe('belum_jatuh_tempo');
    expect($result['ratio'])->toBeNull();
    expect($result['passed'])->toBeFalse();
});

test('sales evaluation on or after H+90 with missing revenue returns menunggu_laporan', function () {
    $eventDate = Carbon::parse('2026-08-01');
    $asOfDate = Carbon::parse('2026-11-01'); // 92 days after

    $result = SalesEvaluator::evaluate($eventDate, 10000000.0, null, true, $asOfDate);

    expect($result['is_due'])->toBeTrue();
    expect($result['status'])->toBe('menunggu_laporan');
    expect($result['ratio'])->toBeNull();
    expect($result['passed'])->toBeFalse();
});

test('sales evaluation with unverified revenue returns menunggu_verifikasi', function () {
    $eventDate = Carbon::parse('2026-08-01');
    $asOfDate = Carbon::parse('2026-11-01');

    $result = SalesEvaluator::evaluate($eventDate, 10000000.0, 150000000.0, false, $asOfDate);

    expect($result['is_due'])->toBeTrue();
    expect($result['status'])->toBe('menunggu_verifikasi');
    expect($result['ratio'])->toBeNull();
    expect($result['passed'])->toBeFalse();
});

test('sales evaluation ratio exact boundary 10.0x passes and under 10.0x fails', function () {
    $eventDate = Carbon::parse('2026-08-01');
    $asOfDate = Carbon::parse('2026-11-01');
    $proposalCost = 10000000.0;

    // 9.99x (Rp 99.900.000) -> fails
    $failResult = SalesEvaluator::evaluate($eventDate, $proposalCost, 99900000.0, true, $asOfDate);
    expect($failResult['ratio'])->toBe(9.99);
    expect($failResult['passed'])->toBeFalse();
    expect($failResult['status'])->toBe('tidak_lolos');

    // 10.00x exact boundary (Rp 100.000.000) -> passes
    $passResult = SalesEvaluator::evaluate($eventDate, $proposalCost, 100000000.0, true, $asOfDate);
    expect($passResult['ratio'])->toBe(10.0);
    expect($passResult['passed'])->toBeTrue();
    expect($passResult['status'])->toBe('lolos');

    // 15.00x (Rp 150.000.000) -> passes
    $overResult = SalesEvaluator::evaluate($eventDate, $proposalCost, 150000000.0, true, $asOfDate);
    expect($overResult['ratio'])->toBe(15.0);
    expect($overResult['passed'])->toBeTrue();
});
