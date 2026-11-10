<?php

use App\Services\Evaluation\CostEvaluator;

test('cost evaluation calculates budget, actual, gap, and variance correctly', function () {
    $result = CostEvaluator::evaluate(10000000.0, 2000000.0, 1800000.0);

    expect($result['total_budget'])->toBe(12000000.0);
    expect($result['total_actual'])->toBe(11800000.0);
    expect($result['budget_gap'])->toBe(200000.0); // positive gap = savings
    expect($result['budget_variance'])->toBe(-0.0167); // (11.8M - 12M) / 12M = -1.67%
    expect($result['status'])->toBe('hemat_atau_sesuai');
    expect($result['is_complete'])->toBeTrue();
});

test('null cost components result in null evaluation instead of converting to zero', function () {
    // Actual team cost is null (unreported)
    $result = CostEvaluator::evaluate(10000000.0, 2000000.0, null);

    expect($result['total_budget'])->toBe(12000000.0);
    expect($result['total_actual'])->toBeNull(); // Must NOT convert to 10M + 0
    expect($result['budget_gap'])->toBeNull();
    expect($result['budget_variance'])->toBeNull();
    expect($result['status'])->toBe('belum_dapat_dinilai');
    expect($result['is_complete'])->toBeFalse();
});

test('explicit zero cost is preserved as zero and calculated accurately', function () {
    $result = CostEvaluator::evaluate(10000000.0, 2000000.0, 0.0);

    expect($result['total_budget'])->toBe(12000000.0);
    expect($result['total_actual'])->toBe(10000000.0);
    expect($result['budget_gap'])->toBe(2000000.0);
    expect($result['status'])->toBe('hemat_atau_sesuai');
    expect($result['is_complete'])->toBeTrue();
});

test('over budget scenario sets status to over_budget with positive variance', function () {
    $result = CostEvaluator::evaluate(10000000.0, 2000000.0, 3000000.0);

    expect($result['total_budget'])->toBe(12000000.0);
    expect($result['total_actual'])->toBe(13000000.0);
    expect($result['budget_gap'])->toBe(-1000000.0); // negative = deficit
    expect($result['budget_variance'])->toBe(0.0833);
    expect($result['status'])->toBe('over_budget');
});

test('zero total budget does not throw division by zero and returns null variance', function () {
    $result = CostEvaluator::evaluate(0.0, 0.0, 500000.0);

    expect($result['total_budget'])->toBe(0.0);
    expect($result['total_actual'])->toBe(500000.0);
    expect($result['budget_variance'])->toBeNull(); // division by zero safe
});
