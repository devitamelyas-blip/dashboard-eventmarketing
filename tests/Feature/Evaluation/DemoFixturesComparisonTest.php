<?php

use App\Models\Event;
use App\Services\Evaluation\EventEvaluator;
use Database\Seeders\BranchSeeder;
use Database\Seeders\DemoFixtureSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\UserSeeder;

beforeEach(function () {
    $this->seed(BranchSeeder::class);
    $this->seed(RoleAndPermissionSeeder::class);
    $this->seed(UserSeeder::class);
    $this->seed(DemoFixtureSeeder::class);
});

test('fixture DEMO-001 matches legacy Google Sheets v1.8 benchmark (Sales goal passed, Layak)', function () {
    $event = Event::with(['branch', 'report', 'salesUpdate', 'surveyResponses'])
        ->where('code', 'DEMO-001')
        ->firstOrFail();

    $result = EventEvaluator::evaluate($event);

    expect($result['portfolio'])->toBe('demo');
    expect($result['data_status'])->toBe('terverifikasi');
    expect($result['cost']['status'])->toBe('hemat_atau_sesuai');
    expect($result['goal_result']['goal'])->toBe('sales');
    expect($result['goal_result']['ratio'])->toBe(15.0);
    expect($result['goal_result']['passed'])->toBeTrue();
    expect($result['recommendation'])->toBe('layak');
    expect($result['recommendation_label'])->toBe('Layak diikuti lagi');
});

test('fixture DEMO-002 matches legacy Google Sheets v1.8 benchmark (Awareness goal passed, Layak)', function () {
    $event = Event::with(['branch', 'report', 'salesUpdate', 'surveyResponses'])
        ->where('code', 'DEMO-002')
        ->firstOrFail();

    $result = EventEvaluator::evaluate($event);

    expect($result['portfolio'])->toBe('demo');
    expect($result['data_status'])->toBe('terverifikasi');
    expect($result['cost']['status'])->toBe('hemat_atau_sesuai');
    expect($result['goal_result']['goal'])->toBe('awareness');
    expect($result['goal_result']['recall_pct'])->toBeGreaterThanOrEqual(50.0);
    expect($result['goal_result']['avg_uplift'])->toBeGreaterThanOrEqual(0.5);
    expect($result['goal_result']['passed'])->toBeTrue();
    expect($result['recommendation'])->toBe('layak');
    expect($result['recommendation_label'])->toBe('Layak diikuti lagi');
});

test('fixture DEMO-003 matches legacy Google Sheets v1.8 benchmark (Relationship goal passed, Layak)', function () {
    $event = Event::with(['branch', 'report', 'salesUpdate', 'surveyResponses'])
        ->where('code', 'DEMO-003')
        ->firstOrFail();

    $result = EventEvaluator::evaluate($event);

    expect($result['portfolio'])->toBe('demo');
    expect($result['data_status'])->toBe('terverifikasi');
    expect($result['cost']['status'])->toBe('hemat_atau_sesuai');
    expect($result['goal_result']['goal'])->toBe('relationship');
    expect($result['goal_result']['partner_count'])->toBeGreaterThanOrEqual(5);
    expect($result['goal_result']['avg_relationship_score'])->toBeGreaterThanOrEqual(4.0);
    expect($result['goal_result']['passed'])->toBeTrue();
    expect($result['recommendation'])->toBe('layak');
    expect($result['recommendation_label'])->toBe('Layak diikuti lagi');
});

test('fixture DEMO-031 matches legacy Google Sheets v1.8 benchmark (Belum jatuh tempo, Belum Final)', function () {
    $event = Event::with(['branch', 'report', 'salesUpdate', 'surveyResponses'])
        ->where('code', 'DEMO-031')
        ->firstOrFail();

    $result = EventEvaluator::evaluate($event);

    expect($result['portfolio'])->toBe('demo');
    expect($result['data_status'])->toBe('belum_jatuh_tempo');
    expect($result['recommendation'])->toBe('belum_final');
    expect($result['recommendation_label'])->toBe('Belum final – belum dapat dinilai');
    expect($result['flags']['follow_ups'])->not->toBeEmpty();
});
