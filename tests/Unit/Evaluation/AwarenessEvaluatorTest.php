<?php

use App\Services\Evaluation\AwarenessEvaluator;

test('awareness evaluation fails when survey count is below threshold', function () {
    // Required 30 responses, only 20 provided
    $responses = [];
    for ($i = 0; $i < 20; $i++) {
        $responses[] = [
            'answers' => [
                'brand_recalled_kencana' => true,
                'audience_relevance' => true,
                'awareness_before' => 2,
                'awareness_after' => 5,
            ],
        ];
    }

    $result = AwarenessEvaluator::evaluate(20, 30, $responses);

    expect($result['is_survey_sufficient'])->toBeFalse();
    expect($result['passed'])->toBeFalse();
    expect($result['status'])->toBe('data_belum_cukup');
});

test('awareness evaluation passes when all thresholds are met (uplift >= 0.5, recall >= 50%, relevance >= 50%)', function () {
    // 30 responses
    $responses = [];
    for ($i = 0; $i < 30; $i++) {
        $responses[] = [
            'answers' => [
                'brand_recalled_kencana' => ($i < 18), // 18/30 = 60% (>= 50%)
                'audience_relevance' => ($i < 25), // 25/30 = 83.3% (>= 50%)
                'awareness_before' => 3,
                'awareness_after' => 4, // uplift = 1.0 (>= 0.5)
            ],
        ];
    }

    $result = AwarenessEvaluator::evaluate(30, 30, $responses);

    expect($result['is_survey_sufficient'])->toBeTrue();
    expect($result['recall_pct'])->toBe(60.0);
    expect($result['avg_uplift'])->toBe(1.0);
    expect($result['audience_relevance_pct'])->toBe(83.33);
    expect($result['passed'])->toBeTrue();
    expect($result['status'])->toBe('lolos');
});

test('awareness evaluation fails if recall is below 50% even with high uplift', function () {
    $responses = [];
    for ($i = 0; $i < 30; $i++) {
        $responses[] = [
            'answers' => [
                'brand_recalled_kencana' => ($i < 14), // 14/30 = 46.67% (< 50%)
                'audience_relevance' => true,
                'awareness_before' => 1,
                'awareness_after' => 5, // uplift = 4.0
            ],
        ];
    }

    $result = AwarenessEvaluator::evaluate(30, 30, $responses);

    expect($result['recall_pct'])->toBe(46.67);
    expect($result['passed'])->toBeFalse();
    expect($result['status'])->toBe('tidak_lolos');
});

test('awareness evaluation fails if uplift is below 0.5 even with high recall', function () {
    $responses = [];
    for ($i = 0; $i < 30; $i++) {
        $responses[] = [
            'answers' => [
                'brand_recalled_kencana' => true, // 100%
                'audience_relevance' => true,
                'awareness_before' => 4,
                'awareness_after' => 4, // uplift = 0.0 (< 0.5)
            ],
        ];
    }

    $result = AwarenessEvaluator::evaluate(30, 30, $responses);

    expect($result['avg_uplift'])->toBe(0.0);
    expect($result['passed'])->toBeFalse();
    expect($result['status'])->toBe('tidak_lolos');
});
