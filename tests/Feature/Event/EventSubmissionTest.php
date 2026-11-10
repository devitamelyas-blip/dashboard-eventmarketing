<?php

use App\Enums\EventPortfolio;
use App\Enums\VerificationStatus;
use App\Mail\NewEventSubmittedMail;
use App\Models\Branch;
use App\Models\Event;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Support\Facades\Mail;
use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    $this->seed(BranchSeeder::class);
    $this->seed(RoleAndPermissionSeeder::class);
    Mail::fake();
});

test('pic can submit a new event with valid data', function () {
    $branch = Branch::where('code', 'SBY-HQ')->first();
    $pic = User::factory()->create(['branch_id' => $branch->id]);
    $pic->assignRole('pic');

    $marketing = User::factory()->create();
    $marketing->assignRole('marketing');

    $response = $this->actingAs($pic)->post('/events', [
        'code' => ' sby-gath-2026-01 ',
        'name' => 'Gathering Mitra Kencana Surabaya',
        'type' => 'gathering_internal',
        'event_date' => '2026-11-20',
        'branch_id' => $branch->id,
        'location' => 'Hotel Bumi Surabaya',
        'pic_name' => 'Budi Santoso',
        'goal' => 'sales',
        'theme_product' => 'Kencana Truss Baja Ringan',
        'target_participants' => 100,
        'target_leads' => 25,
        'estimated_relevance_pct' => 85.5,
        'estimated_relevance_basis' => 'Undangan khusus kontraktor aplikator baja ringan',
        'proposal_cost' => 15000000,
        'team_budget' => 3500000,
    ]);

    $response->assertRedirect('/events');

    // Code must be normalized to uppercase and trimmed
    $this->assertDatabaseHas('events', [
        'code' => 'SBY-GATH-2026-01',
        'name' => 'Gathering Mitra Kencana Surabaya',
        'branch_id' => $branch->id,
        'pic_user_id' => $pic->id,
        'submitter_email' => $pic->email,
        'verification_status' => VerificationStatus::BELUM_DIPERIKSA->value,
    ]);

    $event = Event::where('code', 'SBY-GATH-2026-01')->first();
    expect($event->survey_token)->not->toBeEmpty();
    expect($event->portfolio)->toBe(EventPortfolio::OPERATIONAL);

    // Verify activity log
    $activity = Activity::where('subject_type', Event::class)
        ->where('subject_id', $event->id)
        ->first();
    expect($activity)->not->toBeNull();

    // Verify email sent to marketing
    Mail::assertSent(NewEventSubmittedMail::class, function ($mail) use ($marketing) {
        return $mail->hasTo($marketing->email);
    });
});

test('code must be unique across all events', function () {
    $branch = Branch::where('code', 'SBY-HQ')->first();
    $pic = User::factory()->create(['branch_id' => $branch->id]);
    $pic->assignRole('pic');

    Event::factory()->create(['code' => 'DUPLICATE-01', 'branch_id' => $branch->id]);

    $response = $this->actingAs($pic)->post('/events', [
        'code' => 'duplicate-01',
        'name' => 'Duplicated Event',
        'type' => 'gathering_internal',
        'event_date' => '2026-11-20',
        'branch_id' => $branch->id,
        'location' => 'Surabaya',
        'pic_name' => 'Budi',
        'goal' => 'sales',
        'theme_product' => 'Truss',
        'target_participants' => 50,
        'target_leads' => 10,
        'estimated_relevance_pct' => 80,
        'proposal_cost' => 10000000,
        'team_budget' => 2000000,
    ]);

    $response->assertSessionHasErrors('code');
});

test('code cannot be dash or empty', function () {
    $branch = Branch::where('code', 'SBY-HQ')->first();
    $pic = User::factory()->create(['branch_id' => $branch->id]);
    $pic->assignRole('pic');

    $response = $this->actingAs($pic)->post('/events', [
        'code' => '-',
        'name' => 'Invalid Code Event',
        'type' => 'gathering_internal',
        'event_date' => '2026-11-20',
        'branch_id' => $branch->id,
        'location' => 'Surabaya',
        'pic_name' => 'Budi',
        'goal' => 'sales',
        'theme_product' => 'Truss',
        'target_participants' => 50,
        'target_leads' => 10,
        'estimated_relevance_pct' => 80,
        'proposal_cost' => 10000000,
        'team_budget' => 2000000,
    ]);

    $response->assertSessionHasErrors('code');
});

test('proposal cost and team budget reject Rp prefix and text formatting', function () {
    $branch = Branch::where('code', 'SBY-HQ')->first();
    $pic = User::factory()->create(['branch_id' => $branch->id]);
    $pic->assignRole('pic');

    $response = $this->actingAs($pic)->post('/events', [
        'code' => 'TEST-REJECT-RP',
        'name' => 'Test Currency Reject',
        'type' => 'gathering_internal',
        'event_date' => '2026-11-20',
        'branch_id' => $branch->id,
        'location' => 'Surabaya',
        'pic_name' => 'Budi',
        'goal' => 'sales',
        'theme_product' => 'Truss',
        'target_participants' => 50,
        'target_leads' => 10,
        'estimated_relevance_pct' => 80,
        'proposal_cost' => 'Rp 15.000.000',
        'team_budget' => 'Rp 3.000.000',
    ]);

    $response->assertSessionHasErrors(['proposal_cost', 'team_budget']);
});

test('portfolio is correctly computed from event code prefix', function () {
    expect(EventPortfolio::fromCode('DEMO-001'))->toBe(EventPortfolio::DEMO);
    expect(EventPortfolio::fromCode('demo-gath-01'))->toBe(EventPortfolio::DEMO);
    expect(EventPortfolio::fromCode('DEVON-UAT-001'))->toBe(EventPortfolio::UAT);
    expect(EventPortfolio::fromCode('devon-uat-test'))->toBe(EventPortfolio::UAT);
    expect(EventPortfolio::fromCode('SBY-2026-001'))->toBe(EventPortfolio::OPERATIONAL);
    expect(EventPortfolio::fromCode('JKT-GATH-01'))->toBe(EventPortfolio::OPERATIONAL);
});
