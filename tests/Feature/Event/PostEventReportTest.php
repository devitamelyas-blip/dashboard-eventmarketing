<?php

use App\Enums\VerificationStatus;
use App\Models\Branch;
use App\Models\Event;
use App\Models\EventReport;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleAndPermissionSeeder;

beforeEach(function () {
    $this->seed(BranchSeeder::class);
    $this->seed(RoleAndPermissionSeeder::class);
});

test('pic cannot submit report before the event date', function () {
    $branch = Branch::where('code', 'SBY-HQ')->first();
    $pic = User::factory()->create(['branch_id' => $branch->id]);
    $pic->assignRole('pic');

    // Future event
    $event = Event::factory()->create([
        'branch_id' => $branch->id,
        'event_date' => now()->addDays(5)->format('Y-m-d'),
    ]);

    $response = $this->actingAs($pic)->get("/events/{$event->id}/report");
    $response->assertForbidden();

    $postResponse = $this->actingAs($pic)->post("/events/{$event->id}/report", [
        'actual_participants' => 50,
        'valid_leads' => 10,
        'actual_team_cost' => 2000000,
    ]);
    $postResponse->assertForbidden();
});

test('pic can submit post-event report on or after event date', function () {
    $branch = Branch::where('code', 'SBY-HQ')->first();
    $pic = User::factory()->create(['branch_id' => $branch->id]);
    $pic->assignRole('pic');

    $event = Event::factory()->create([
        'branch_id' => $branch->id,
        'event_date' => now()->subDay()->format('Y-m-d'),
    ]);

    $response = $this->actingAs($pic)->post("/events/{$event->id}/report", [
        'actual_participants' => 75,
        'valid_leads' => 15,
        'actual_team_cost' => 2500000,
        'notes' => 'Acara berjalan lancar',
    ]);

    $response->assertRedirect('/events');

    $this->assertDatabaseHas('event_reports', [
        'event_id' => $event->id,
        'actual_participants' => 75,
        'valid_leads' => 15,
        'actual_team_cost' => 2500000.00,
        'notes' => 'Acara berjalan lancar',
    ]);
});

test('submitting report uses updateOrCreate on the same event and does not duplicate', function () {
    $branch = Branch::where('code', 'SBY-HQ')->first();
    $pic = User::factory()->create(['branch_id' => $branch->id]);
    $pic->assignRole('pic');

    $event = Event::factory()->create([
        'branch_id' => $branch->id,
        'event_date' => now()->subDay()->format('Y-m-d'),
    ]);

    // First submission
    $this->actingAs($pic)->post("/events/{$event->id}/report", [
        'actual_participants' => 50,
        'valid_leads' => 10,
        'actual_team_cost' => 1000000,
    ]);

    expect(EventReport::where('event_id', $event->id)->count())->toBe(1);

    // Second submission (update)
    $this->actingAs($pic)->post("/events/{$event->id}/report", [
        'actual_participants' => 60,
        'valid_leads' => 12,
        'actual_team_cost' => 1200000,
    ]);

    expect(EventReport::where('event_id', $event->id)->count())->toBe(1);
    $report = EventReport::where('event_id', $event->id)->first();
    expect($report->actual_participants)->toBe(60);
    expect((float) $report->actual_team_cost)->toBe(1200000.00);
});

test('null cost remains null and is not converted to 0, while 0 is preserved as 0', function () {
    $branch = Branch::where('code', 'SBY-HQ')->first();
    $pic = User::factory()->create(['branch_id' => $branch->id]);
    $pic->assignRole('pic');

    $event1 = Event::factory()->create([
        'branch_id' => $branch->id,
        'event_date' => now()->subDays(2)->format('Y-m-d'),
    ]);

    // Submitting with empty actual_team_cost (unreported)
    $this->actingAs($pic)->post("/events/{$event1->id}/report", [
        'actual_participants' => 40,
        'valid_leads' => 5,
        'actual_team_cost' => '',
    ]);

    $report1 = EventReport::where('event_id', $event1->id)->first();
    expect($report1->actual_team_cost)->toBeNull();

    $event2 = Event::factory()->create([
        'branch_id' => $branch->id,
        'event_date' => now()->subDays(2)->format('Y-m-d'),
    ]);

    // Submitting with explicitly 0 cost
    $this->actingAs($pic)->post("/events/{$event2->id}/report", [
        'actual_participants' => 40,
        'valid_leads' => 5,
        'actual_team_cost' => 0,
    ]);

    $report2 = EventReport::where('event_id', $event2->id)->first();
    expect($report2->actual_team_cost)->not->toBeNull();
    expect((float) $report2->actual_team_cost)->toBe(0.00);
});

test('modifying report after event is verified resets verification status and marks director decision stale', function () {
    $branch = Branch::where('code', 'SBY-HQ')->first();
    $pic = User::factory()->create(['branch_id' => $branch->id]);
    $pic->assignRole('pic');

    $event = Event::factory()->create([
        'branch_id' => $branch->id,
        'event_date' => now()->subDays(3)->format('Y-m-d'),
        'verification_status' => VerificationStatus::TERVERIFIKASI,
        'director_decision' => 'Layak Diikuti Lagi',
        'decision_stale_flag' => false,
    ]);

    $this->actingAs($pic)->post("/events/{$event->id}/report", [
        'actual_participants' => 90,
        'valid_leads' => 20,
        'actual_team_cost' => 3000000,
    ]);

    $event->refresh();
    expect($event->verification_status)->toBe(VerificationStatus::BELUM_DIPERIKSA);
    expect($event->decision_stale_flag)->toBeTrue();
    expect($event->director_decision)->toBe('Layak Diikuti Lagi'); // decision itself is not deleted
});
