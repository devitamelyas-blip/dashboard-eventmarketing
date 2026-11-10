<?php

use App\Enums\DataStatus;
use App\Enums\EventType;
use App\Enums\Goal;
use App\Enums\Recommendation;
use App\Enums\VerificationStatus;
use App\Models\Event;
use App\Models\EventReport;
use App\Models\SalesUpdate;
use App\Services\Evaluation\EventEvaluator;
use Illuminate\Support\Carbon;

test('event evaluator runs as pure function without database queries and evaluates sales event', function () {
    $event = new Event([
        'code' => 'SBY-GATH-01',
        'name' => 'Gathering Surabaya',
        'type' => EventType::GATHERING_INTERNAL,
        'goal' => Goal::SALES,
        'event_date' => Carbon::parse('2026-06-01'),
        'proposal_cost' => 10000000.0,
        'team_budget' => 2000000.0,
        'verification_status' => VerificationStatus::TERVERIFIKASI,
    ]);

    $report = new EventReport([
        'actual_participants' => 60,
        'valid_leads' => 15,
        'actual_team_cost' => 1800000.0,
    ]);

    $salesUpdate = new SalesUpdate([
        'revenue' => 150000000.0, // 15x
    ]);

    $event->setRelation('report', $report);
    $event->setRelation('salesUpdate', $salesUpdate);

    // 30 counted responses
    $surveyResponses = [];
    for ($i = 0; $i < 30; $i++) {
        $surveyResponses[] = [
            'counted' => true,
            'answers' => ['audience_relevance' => true],
        ];
    }

    $asOf = Carbon::parse('2026-10-01'); // > 90 days
    $evaluation = EventEvaluator::evaluate($event, $surveyResponses, $asOf);

    expect($evaluation['portfolio'])->toBe('operational');
    expect($evaluation['data_status'])->toBe(DataStatus::TERVERIFIKASI->value);
    expect($evaluation['recommendation'])->toBe(Recommendation::LAYAK->value);
    expect($evaluation['cost']['status'])->toBe('hemat_atau_sesuai');
    expect($evaluation['goal_result']['ratio'])->toBe(15.0);
    expect($evaluation['goal_result']['passed'])->toBeTrue();
});

test('unverified event returns recommendation belum final and data status reflects pending verification', function () {
    $event = new Event([
        'code' => 'SBY-GATH-02',
        'name' => 'Gathering Belum Diverifikasi',
        'type' => EventType::GATHERING_INTERNAL,
        'goal' => Goal::SALES,
        'event_date' => Carbon::parse('2026-06-01'),
        'proposal_cost' => 10000000.0,
        'team_budget' => 2000000.0,
        'verification_status' => VerificationStatus::BELUM_DIPERIKSA, // NOT verified
    ]);

    $report = new EventReport([
        'actual_participants' => 50,
        'actual_team_cost' => 1500000.0,
    ]);

    $salesUpdate = new SalesUpdate([
        'revenue' => 120000000.0,
    ]);

    $event->setRelation('report', $report);
    $event->setRelation('salesUpdate', $salesUpdate);

    $asOf = Carbon::parse('2026-10-01');
    $evaluation = EventEvaluator::evaluate($event, [], $asOf);

    expect($evaluation['data_status'])->toBe(DataStatus::MENUNGGU_VERIFIKASI->value);
    expect($evaluation['recommendation'])->toBe(Recommendation::BELUM_FINAL->value);
    expect($evaluation['flags']['needs_verification'])->toBeTrue();
});

test('event with goal passed but over budget receives recommendation perlu evaluasi', function () {
    $event = new Event([
        'code' => 'SBY-GATH-03',
        'name' => 'Gathering Over Budget',
        'type' => EventType::GATHERING_INTERNAL,
        'goal' => Goal::SALES,
        'event_date' => Carbon::parse('2026-06-01'),
        'proposal_cost' => 10000000.0,
        'team_budget' => 2000000.0, // Total budget = 12M
        'verification_status' => VerificationStatus::TERVERIFIKASI,
    ]);

    $report = new EventReport([
        'actual_participants' => 50,
        'actual_team_cost' => 3500000.0, // Total actual = 13.5M (Over budget!)
    ]);

    $salesUpdate = new SalesUpdate([
        'revenue' => 120000000.0, // 12x (Lolos)
    ]);

    $event->setRelation('report', $report);
    $event->setRelation('salesUpdate', $salesUpdate);

    $surveyResponses = array_fill(0, 30, ['counted' => true, 'answers' => ['audience_relevance' => true]]);

    $asOf = Carbon::parse('2026-10-01');
    $evaluation = EventEvaluator::evaluate($event, $surveyResponses, $asOf);

    expect($evaluation['data_status'])->toBe(DataStatus::TERVERIFIKASI->value);
    expect($evaluation['cost']['status'])->toBe('over_budget');
    expect($evaluation['goal_result']['passed'])->toBeTrue();
    expect($evaluation['recommendation'])->toBe(Recommendation::PERLU_EVALUASI->value);
});
