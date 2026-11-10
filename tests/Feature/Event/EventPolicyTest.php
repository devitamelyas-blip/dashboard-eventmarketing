<?php

use App\Models\Branch;
use App\Models\Event;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleAndPermissionSeeder;

beforeEach(function () {
    $this->seed(BranchSeeder::class);
    $this->seed(RoleAndPermissionSeeder::class);
});

test('pic only sees events from their own branch in index', function () {
    $branchSby = Branch::where('code', 'SBY-HQ')->first();
    $branchJkt = Branch::where('code', 'JKT')->first();

    $picSby = User::factory()->create(['branch_id' => $branchSby->id]);
    $picSby->assignRole('pic');

    $eventSby = Event::factory()->create(['code' => 'SBY-EVENT-01', 'branch_id' => $branchSby->id]);
    $eventJkt = Event::factory()->create(['code' => 'JKT-EVENT-01', 'branch_id' => $branchJkt->id]);

    $response = $this->actingAs($picSby)->get('/events');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Events/Index')
        ->where('events.data.0.code', 'SBY-EVENT-01')
        ->has('events.data', 1)
    );
});

test('pic cannot force event creation for another branch', function () {
    $branchSby = Branch::where('code', 'SBY-HQ')->first();
    $branchJkt = Branch::where('code', 'JKT')->first();

    $picSby = User::factory()->create(['branch_id' => $branchSby->id]);
    $picSby->assignRole('pic');

    $response = $this->actingAs($picSby)->post('/events', [
        'code' => 'FORCED-JKT-01',
        'name' => 'Attempt to hijack Jakarta Branch',
        'type' => 'gathering_internal',
        'event_date' => '2026-11-20',
        'branch_id' => $branchJkt->id, // Attempting to use Jakarta branch
        'location' => 'Jakarta',
        'pic_name' => 'Budi',
        'goal' => 'sales',
        'theme_product' => 'Truss',
        'target_participants' => 50,
        'target_leads' => 10,
        'estimated_relevance_pct' => 80,
        'proposal_cost' => 10000000,
        'team_budget' => 2000000,
    ]);

    $response->assertRedirect('/events');

    // The event must be forced to Surabaya branch because the submitter is PIC Surabaya
    $event = Event::where('code', 'FORCED-JKT-01')->first();
    expect($event->branch_id)->toBe($branchSby->id);
});

test('marketing and direksi see events from all branches', function () {
    $branchSby = Branch::where('code', 'SBY-HQ')->first();
    $branchJkt = Branch::where('code', 'JKT')->first();

    $marketing = User::factory()->create(['branch_id' => $branchSby->id]);
    $marketing->assignRole('marketing');

    $direksi = User::factory()->create(['branch_id' => $branchSby->id]);
    $direksi->assignRole('direksi');

    Event::factory()->create(['code' => 'SBY-ALL-01', 'branch_id' => $branchSby->id]);
    Event::factory()->create(['code' => 'JKT-ALL-01', 'branch_id' => $branchJkt->id]);

    $responseMarketing = $this->actingAs($marketing)->get('/events');
    $responseMarketing->assertOk();
    $responseMarketing->assertInertia(fn ($page) => $page->has('events.data', 2));

    $responseDireksi = $this->actingAs($direksi)->get('/events');
    $responseDireksi->assertOk();
    $responseDireksi->assertInertia(fn ($page) => $page->has('events.data', 2));
});
