<?php

use App\Enums\DataStatus;
use App\Enums\EventType;
use App\Enums\Goal;
use App\Enums\VerificationStatus;
use App\Models\Branch;
use App\Models\Event;
use App\Models\EventReport;
use App\Models\Respondent;
use App\Models\SalesUpdate;
use App\Models\SurveyResponse;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BranchSeeder::class);
    $this->seed(RoleAndPermissionSeeder::class);
});

test('admin, marketing, and direksi can access dashboard', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $direksi = User::factory()->create();
    $direksi->assignRole('direksi');

    $marketing = User::factory()->create();
    $marketing->assignRole('marketing');

    $this->actingAs($admin)->get('/dashboard')->assertOk();
    $this->actingAs($direksi)->get('/dashboard')->assertOk();
    $this->actingAs($marketing)->get('/dashboard')->assertOk();
});

test('dashboard computes 4 kpi cards accurately and supports portfolio filter', function () {
    $branch = Branch::first();
    $picUser = User::factory()->create(['branch_id' => $branch->id]);
    $picUser->assignRole('pic');

    $marketing = User::factory()->create();
    $marketing->assignRole('marketing');

    // Operational event 1: Terverifikasi, Sales
    $evt1 = Event::create([
        'code' => 'KMB-OPS-001',
        'name' => 'Operational Sales Fair',
        'type' => EventType::GATHERING_INTERNAL,
        'event_date' => now()->subDays(100),
        'branch_id' => $branch->id,
        'location' => 'Surabaya',
        'pic_name' => 'Budi',
        'pic_user_id' => $picUser->id,
        'submitter_email' => $picUser->email,
        'goal' => Goal::SALES,
        'theme_product' => 'Baja Ringan',
        'target_participants' => 100,
        'target_leads' => 20,
        'proposal_cost' => 10000000,
        'team_budget' => 2000000,
        'verification_status' => VerificationStatus::TERVERIFIKASI,
    ]);

    EventReport::create([
        'event_id' => $evt1->id,
        'actual_participants' => 95,
        'valid_leads' => 18,
        'actual_team_cost' => 1800000,
    ]);

    SalesUpdate::create([
        'event_id' => $evt1->id,
        'revenue' => 150000000,
    ]);

    // Demo event: Terverifikasi, but portfolio = demo
    $evtDemo = Event::create([
        'code' => 'DEMO-001',
        'name' => 'Demo Architectural Expo',
        'type' => EventType::JOIN_EXTERNAL,
        'event_date' => now()->subDays(30),
        'branch_id' => $branch->id,
        'location' => 'Jakarta',
        'pic_name' => 'Andi',
        'pic_user_id' => $picUser->id,
        'submitter_email' => $picUser->email,
        'goal' => Goal::AWARENESS,
        'theme_product' => 'Genteng Metal',
        'target_participants' => 50,
        'target_leads' => 10,
        'proposal_cost' => 5000000,
        'team_budget' => 1000000,
        'verification_status' => VerificationStatus::TERVERIFIKASI,
    ]);

    // When portfolio = operational (default), DEMO event is excluded
    $response = $this->actingAs($marketing)->get('/dashboard?portfolio=operational');
    $response->assertOk();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Dashboard/Index')
        ->where('kpis.verified_events_count', 1)
        ->where('kpis.pending_verification_count', 0)
        ->where('kpis.verified_actual_cost', 11800000) // 10jt + 1.8jt
        ->where('kpis.verified_revenue', 150000000)
    );

    // When portfolio = demo, only DEMO event is counted
    $demoResponse = $this->actingAs($marketing)->get('/dashboard?portfolio=demo');
    $demoResponse->assertOk();

    $demoResponse->assertInertia(fn (Assert $page) => $page
        ->component('Dashboard/Index')
        ->where('kpis.verified_events_count', 1)
        ->where('selectedPortfolio', 'demo')
    );
});

test('dashboard and print payload strictly isolate respondent personal identities', function () {
    $branch = Branch::first();
    $picUser = User::factory()->create(['branch_id' => $branch->id]);
    $picUser->assignRole('pic');

    $direksi = User::factory()->create();
    $direksi->assignRole('direksi');

    $event = Event::create([
        'code' => 'KMB-AWR-001',
        'name' => 'Awareness Festival',
        'type' => EventType::JOIN_EXTERNAL,
        'event_date' => now()->subDays(10),
        'branch_id' => $branch->id,
        'location' => 'Semarang',
        'pic_name' => 'Dewi',
        'pic_user_id' => $picUser->id,
        'submitter_email' => $picUser->email,
        'goal' => Goal::AWARENESS,
        'theme_product' => 'Rangka Baja',
        'target_participants' => 30,
        'target_leads' => 5,
        'proposal_cost' => 5000000,
        'team_budget' => 1000000,
        'verification_status' => VerificationStatus::TERVERIFIKASI,
    ]);

    // Create respondent with sensitive personal information
    $respondent = Respondent::create([
        'email' => 'secret_customer@example.com',
        'name' => 'Rahasia Pribadi Peserta',
        'organization' => 'CV Kontraktor Rahasia',
        'phone' => '081234567890',
    ]);

    // Create survey response linked via email hash
    SurveyResponse::create([
        'event_id' => $event->id,
        'email_hash' => hash('sha256', 'secret_customer@example.com' . config('app.key')),
        'counted' => true,
        'answers' => [
            'relevance_score' => 4,
            'unassisted_brand' => 'KENCANA',
            'is_current_client' => true,
            'relationship_score' => 5,
            'pre_event_awareness' => 3,
            'post_event_awareness' => 5,
        ],
    ]);

    // 1. Check /dashboard
    $dashResponse = $this->actingAs($direksi)->get("/dashboard?event_id={$event->id}");
    $dashResponse->assertOk();

    $dashContent = $dashResponse->getContent();
    expect($dashContent)->not->toContain('secret_customer@example.com')
        ->and($dashContent)->not->toContain('Rahasia Pribadi Peserta')
        ->and($dashContent)->not->toContain('081234567890')
        ->and($dashContent)->not->toContain('CV Kontraktor Rahasia');

    // 2. Check /dashboard/print/{$event->id}
    $printResponse = $this->actingAs($direksi)->get("/dashboard/print/{$event->id}");
    $printResponse->assertOk();

    $printContent = $printResponse->getContent();
    expect($printContent)->not->toContain('secret_customer@example.com')
        ->and($printContent)->not->toContain('Rahasia Pribadi Peserta')
        ->and($printContent)->not->toContain('081234567890')
        ->and($printContent)->not->toContain('CV Kontraktor Rahasia');
});

test('pic can only access print view for their own branch events', function () {
    $branches = Branch::all();
    $branchA = $branches[0];
    $branchB = $branches[1];

    $picUserA = User::factory()->create(['branch_id' => $branchA->id]);
    $picUserA->assignRole('pic');

    $picUserB = User::factory()->create(['branch_id' => $branchB->id]);
    $picUserB->assignRole('pic');

    $eventBranchB = Event::create([
        'code' => 'KMB-B-001',
        'name' => 'Event Cabang B',
        'type' => EventType::GATHERING_INTERNAL,
        'event_date' => now()->subDays(5),
        'branch_id' => $branchB->id,
        'location' => 'Bandung',
        'pic_name' => 'PIC B',
        'pic_user_id' => $picUserB->id,
        'submitter_email' => $picUserB->email,
        'goal' => Goal::RELATIONSHIP,
        'theme_product' => 'Atap Spandek',
        'target_participants' => 20,
        'target_leads' => 5,
        'proposal_cost' => 3000000,
        'team_budget' => 500000,
        'verification_status' => VerificationStatus::BELUM_DIPERIKSA,
    ]);

    // PIC User A cannot view print of Branch B event
    $this->actingAs($picUserA)->get("/dashboard/print/{$eventBranchB->id}")
        ->assertForbidden();

    // Event on PIC User A branch works
    $eventBranchA = Event::create([
        'code' => 'KMB-A-001',
        'name' => 'Event Cabang A',
        'type' => EventType::GATHERING_INTERNAL,
        'event_date' => now()->subDays(5),
        'branch_id' => $branchA->id,
        'location' => 'Surabaya',
        'pic_name' => 'PIC A',
        'pic_user_id' => $picUserA->id,
        'submitter_email' => $picUserA->email,
        'goal' => Goal::RELATIONSHIP,
        'theme_product' => 'Atap Spandek',
        'target_participants' => 20,
        'target_leads' => 5,
        'proposal_cost' => 3000000,
        'team_budget' => 500000,
        'verification_status' => VerificationStatus::BELUM_DIPERIKSA,
    ]);

    $this->actingAs($picUserA)->get("/dashboard/print/{$eventBranchA->id}")
        ->assertOk();
});
