<?php

use App\Enums\EventType;
use App\Enums\Goal;
use App\Enums\VerificationStatus;
use App\Models\Branch;
use App\Models\Event;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BranchSeeder::class);
    $this->seed(RoleAndPermissionSeeder::class);
});

test('authenticated user can export events as csv with excel utf8 bom', function () {
    $marketing = User::factory()->create();
    $marketing->assignRole('marketing');

    $branch = Branch::first();

    Event::create([
        'code' => 'EXP-2026-001',
        'name' => 'Event Ekspor Pertama',
        'type' => EventType::GATHERING_INTERNAL,
        'event_date' => '2026-11-25',
        'branch_id' => $branch->id,
        'location' => 'Grand City',
        'pic_name' => 'Budi',
        'pic_user_id' => $marketing->id,
        'submitter_email' => $marketing->email,
        'goal' => Goal::SALES,
        'theme_product' => 'Kencana Truss Baja Ringan',
        'proposal_cost' => 10000000,
        'team_budget' => 2000000,
        'verification_status' => VerificationStatus::BELUM_DIPERIKSA,
    ]);

    $response = $this->actingAs($marketing)->get('/events/export');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

    $content = $response->streamedContent();

    // Contains UTF-8 BOM
    expect(str_starts_with($content, "\xEF\xBB\xBF"))->toBeTrue();
    expect($content)->toContain('EXP-2026-001')
        ->and($content)->toContain('Event Ekspor Pertama')
        ->and($content)->toContain('Grand City');
});

test('pic branch data isolation is respected in csv export', function () {
    $branchSby = Branch::where('code', 'SBY-HQ')->first();
    $branchJkt = Branch::where('code', 'JKT')->first();

    $picSby = User::factory()->create(['branch_id' => $branchSby->id]);
    $picSby->assignRole('pic');

    $picJkt = User::factory()->create(['branch_id' => $branchJkt->id]);
    $picJkt->assignRole('pic');

    Event::create([
        'code' => 'SBY-EXP-01',
        'name' => 'Event Surabaya',
        'type' => EventType::GATHERING_INTERNAL,
        'event_date' => '2026-11-25',
        'branch_id' => $branchSby->id,
        'location' => 'Surabaya',
        'pic_name' => 'Budi SBY',
        'pic_user_id' => $picSby->id,
        'submitter_email' => $picSby->email,
        'goal' => Goal::AWARENESS,
        'theme_product' => 'Genteng Metal Kencana',
        'proposal_cost' => 5000000,
        'team_budget' => 1000000,
        'verification_status' => VerificationStatus::BELUM_DIPERIKSA,
    ]);

    Event::create([
        'code' => 'JKT-EXP-01',
        'name' => 'Event Jakarta',
        'type' => EventType::GATHERING_INTERNAL,
        'event_date' => '2026-11-25',
        'branch_id' => $branchJkt->id,
        'location' => 'Jakarta',
        'pic_name' => 'Andi JKT',
        'pic_user_id' => $picJkt->id,
        'submitter_email' => $picJkt->email,
        'goal' => Goal::AWARENESS,
        'theme_product' => 'Baja Ringan Kencana',
        'proposal_cost' => 5000000,
        'team_budget' => 1000000,
        'verification_status' => VerificationStatus::BELUM_DIPERIKSA,
    ]);

    $response = $this->actingAs($picSby)->get('/events/export');
    $response->assertOk();

    $content = $response->streamedContent();
    expect($content)->toContain('SBY-EXP-01')
        ->and($content)->not->toContain('JKT-EXP-01');
});
