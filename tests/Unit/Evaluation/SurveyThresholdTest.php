<?php

use App\Services\Evaluation\SurveyThreshold;

test('survey threshold calculation exact boundary test table', function (?int $participants, ?int $expected) {
    expect(SurveyThreshold::calculate($participants))->toBe($expected);
})->with([
    'null participants returns null' => [null, null],
    'zero participants returns zero' => [0, 0],
    'negative participants returns zero' => [-5, 0],
    '1 participant (ceil 0.8*1)' => [1, 1],
    '10 participants (ceil 0.8*10)' => [10, 8],
    '20 participants (ceil 0.8*20)' => [20, 16],
    '25 participants (ceil 0.8*25)' => [25, 20],
    '29 participants (ceil 0.8*29 = 23.2 -> 24)' => [29, 24],
    '30 participants (exact boundary <= 30)' => [30, 24],
    '31 participants (exact boundary 31-100)' => [31, 30],
    '50 participants' => [50, 30],
    '100 participants (exact boundary 31-100)' => [100, 30],
    '101 participants (exact boundary > 100)' => [101, 50],
    '250 participants' => [250, 50],
]);
