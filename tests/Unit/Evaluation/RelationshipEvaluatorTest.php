<?php

use App\Services\Evaluation\RelationshipEvaluator;

test('relationship evaluation fails when survey count is below threshold', function () {
    $responses = [];
    for ($i = 0; $i < 10; $i++) {
        $responses[] = [
            'answers' => [
                'is_customer_or_partner' => true,
                'audience_relevance' => true,
                'relationship_score' => 5,
            ],
        ];
    }

    $result = RelationshipEvaluator::evaluate(10, 20, $responses);

    expect($result['is_survey_sufficient'])->toBeFalse();
    expect($result['passed'])->toBeFalse();
    expect($result['status'])->toBe('data_belum_cukup');
});

test('relationship evaluation passes when surveys sufficient, >= 5 partners, score >= 4.0, relevance >= 50%', function () {
    // 20 responses
    $responses = [];
    for ($i = 0; $i < 20; $i++) {
        $responses[] = [
            'answers' => [
                'is_customer_or_partner' => ($i < 12), // 12 partners (>= 5)
                'audience_relevance' => true,
                'relationship_score' => ($i < 10 ? 5 : 4), // average 4.5 (>= 4.0)
            ],
        ];
    }

    $result = RelationshipEvaluator::evaluate(20, 20, $responses);

    expect($result['is_survey_sufficient'])->toBeTrue();
    expect($result['partner_count'])->toBe(12);
    expect($result['avg_relationship_score'])->toBe(4.5);
    expect($result['passed'])->toBeTrue();
    expect($result['status'])->toBe('lolos');
});

test('relationship evaluation fails if active partners count is below 5', function () {
    $responses = [];
    for ($i = 0; $i < 20; $i++) {
        $responses[] = [
            'answers' => [
                'is_customer_or_partner' => ($i < 4), // only 4 partners (< 5)
                'audience_relevance' => true,
                'relationship_score' => 5,
            ],
        ];
    }

    $result = RelationshipEvaluator::evaluate(20, 20, $responses);

    expect($result['partner_count'])->toBe(4);
    expect($result['passed'])->toBeFalse();
    expect($result['status'])->toBe('tidak_lolos');
});

test('relationship evaluation fails if average score is below 4.0', function () {
    $responses = [];
    for ($i = 0; $i < 20; $i++) {
        $responses[] = [
            'answers' => [
                'is_customer_or_partner' => true, // 20 partners
                'audience_relevance' => true,
                'relationship_score' => 3, // average 3.0 (< 4.0)
            ],
        ];
    }

    $result = RelationshipEvaluator::evaluate(20, 20, $responses);

    expect($result['avg_relationship_score'])->toBe(3.0);
    expect($result['passed'])->toBeFalse();
    expect($result['status'])->toBe('tidak_lolos');
});
