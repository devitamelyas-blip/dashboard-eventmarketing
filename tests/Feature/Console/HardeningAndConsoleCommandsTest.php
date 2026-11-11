<?php

use App\Enums\EventType;
use App\Enums\Goal;
use App\Enums\VerificationStatus;
use App\Models\Branch;
use App\Models\Event;
use App\Models\Respondent;
use App\Models\SurveyResponse;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BranchSeeder::class);
    $this->seed(RoleAndPermissionSeeder::class);
});

test('security headers are present in web responses', function () {
    $response = $this->get('/login');
    $response->assertOk();
    $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
});

test('anonymize respondents command anonymizes old pii while preserving survey kpi hashes', function () {
    $branch = Branch::first();
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $event = Event::create([
        'code' => 'KMB-OLD-001',
        'name' => 'Historical Event 2024',
        'type' => EventType::GATHERING_INTERNAL,
        'event_date' => now()->subDays(400),
        'branch_id' => $branch->id,
        'location' => 'Surabaya',
        'pic_name' => 'PIC Old',
        'pic_user_id' => $admin->id,
        'submitter_email' => $admin->email,
        'goal' => Goal::AWARENESS,
        'theme_product' => 'Genteng Metal',
        'target_participants' => 30,
        'target_leads' => 10,
        'proposal_cost' => 5000000,
        'team_budget' => 1000000,
        'verification_status' => VerificationStatus::TERVERIFIKASI,
    ]);

    // 1. Old respondent (> 365 days)
    $oldRespondent = Respondent::create([
        'email' => 'old_customer@example.com',
        'name' => 'Budi Lama',
        'organization' => 'PT Kontraktor Kuno',
        'phone' => '08123456789',
    ]);
    $oldRespondent->created_at = now()->subDays(400);
    $oldRespondent->save();

    // 2. Recent respondent (< 365 days)
    $recentRespondent = Respondent::create([
        'email' => 'recent_customer@example.com',
        'name' => 'Andi Baru',
        'organization' => 'CV Kontraktor Baru',
        'phone' => '08987654321',
    ]);
    $recentRespondent->created_at = now()->subDays(30);
    $recentRespondent->save();

    // 3. Survey response linked via hash
    $emailHash = hash('sha256', 'old_customer@example.com' . config('app.key'));
    $surveyResponse = SurveyResponse::create([
        'event_id' => $event->id,
        'email_hash' => $emailHash,
        'counted' => true,
        'answers' => [
            'relevance_score' => 4,
            'unassisted_brand' => 'KENCANA',
            'is_current_client' => true,
        ],
    ]);

    $this->artisan('events:anonymize-respondents', ['--days' => 365])
        ->assertSuccessful();

    $oldRespondent->refresh();
    $recentRespondent->refresh();
    $surveyResponse->refresh();

    // Old respondent PII is wiped
    expect($oldRespondent->name)->toBeNull()
        ->and($oldRespondent->phone)->toBeNull()
        ->and($oldRespondent->organization)->toBe('Anonim');

    // Recent respondent PII is preserved
    expect($recentRespondent->name)->toBe('Andi Baru')
        ->and($recentRespondent->phone)->toBe('08987654321')
        ->and($recentRespondent->organization)->toBe('CV Kontraktor Baru');

    // Survey KPI Hash and answers are intact
    expect($surveyResponse->email_hash)->toBe($emailHash)
        ->and($surveyResponse->counted)->toBeTrue()
        ->and($surveyResponse->answers['unassisted_brand'])->toBe('KENCANA');
});

test('import legacy command imports valid rows and reports corrupt rows', function () {
    $tempCsv = tempnam(sys_get_temp_dir(), 'event_test_') . '.csv';

    $csvContent = implode("\n", [
        'code,name,type,event_date,branch,location,pic_name,goal,theme_product,target_participants,target_leads,proposal_cost,team_budget,actual_participants,valid_leads,actual_team_cost,revenue,verification_status',
        'KMB-LEG-001,Valid Sales Event,gathering_internal,2026-05-10,Surabaya PUSAT,Grand City,Budi,sales,Baja Ringan,50,10,10000000,2000000,48,9,1900000,120000000,terverifikasi',
        '-,Invalid Empty Code Row,gathering_internal,2026-05-11,Jakarta,JIExpo,Andi,awareness,Genteng,30,5,5000000,1000000,,,,terverifikasi',
        'KMB-LEG-002,Invalid Goal Event,gathering_internal,2026-05-12,Jakarta,JIExpo,Andi,unknown_goal,Genteng,30,5,5000000,1000000,,,,terverifikasi',
        'KMB-LEG-003,Valid Awareness Event,join_external,2026-06-15,Bandung,Sudirman Hall,Dewi,awareness,Rangka Baja,40,8,6000000,1500000,38,7,1400000,,terverifikasi',
    ]);

    File::put($tempCsv, $csvContent);

    // 1. Dry run test
    $this->artisan('events:import-legacy', [
        'file' => $tempCsv,
        '--dry-run' => true,
    ])->assertSuccessful();

    // In dry-run, no events should be committed
    expect(Event::where('code', 'KMB-LEG-001')->exists())->toBeFalse();

    // 2. Real import test
    $this->artisan('events:import-legacy', [
        'file' => $tempCsv,
    ])->assertSuccessful();

    // Valid events created
    $evt1 = Event::where('code', 'KMB-LEG-001')->first();
    expect($evt1)->not->toBeNull()
        ->and($evt1->name)->toBe('Valid Sales Event')
        ->and($evt1->goal)->toBe(Goal::SALES)
        ->and((float) $evt1->proposal_cost)->toBe(10000000.0);

    // Associated report and sales created
    expect($evt1->report)->not->toBeNull()
        ->and($evt1->report->actual_participants)->toBe(48)
        ->and($evt1->salesUpdate)->not->toBeNull()
        ->and((float) $evt1->salesUpdate->revenue)->toBe(120000000.0);

    $evt3 = Event::where('code', 'KMB-LEG-003')->first();
    expect($evt3)->not->toBeNull()
        ->and($evt3->name)->toBe('Valid Awareness Event')
        ->and($evt3->goal)->toBe(Goal::AWARENESS);

    // Corrupt rows not created
    expect(Event::where('code', '-')->exists())->toBeFalse()
        ->and(Event::where('code', 'KMB-LEG-002')->exists())->toBeFalse();

    @unlink($tempCsv);
});
