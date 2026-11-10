<?php

use App\Models\Branch;
use App\Models\Event;
use App\Models\Respondent;
use App\Models\SurveyResponse;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleAndPermissionSeeder;

beforeEach(function () {
    $this->seed(BranchSeeder::class);
    $this->seed(RoleAndPermissionSeeder::class);
});

test('accessing survey with invalid token returns 404', function () {
    $this->get('/survei/invalid-non-existent-token')->assertNotFound();
});

test('accessing survey with valid token displays survey form', function () {
    $branch = Branch::where('code', 'SBY-HQ')->first();
    $event = Event::factory()->create(['branch_id' => $branch->id]);

    $response = $this->get("/survei/{$event->survey_token}");
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Survey/Form')
        ->where('token', $event->survey_token)
        ->where('event.name', $event->name)
    );
});

test('participant can submit survey response and identity is saved separately in respondents table', function () {
    $branch = Branch::where('code', 'SBY-HQ')->first();
    $event = Event::factory()->create(['branch_id' => $branch->id]);

    $payload = [
        'email' => 'peserta.budi@gmail.com',
        'name' => 'Budi Mandor',
        'organization' => 'CV Mandiri Truss',
        'phone' => '081234567890',
        'unaided_brand_recall' => 'Kencana Truss Baja Ringan',
        'audience_relevance' => true,
        'is_customer_or_partner' => true,
        'relationship_score' => 5,
        'awareness_before' => 3,
        'awareness_after' => 5,
    ];

    $response = $this->post("/survei/{$event->survey_token}", $payload);

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Survey/Thanks'));

    // Identity is stored in respondents table
    $this->assertDatabaseHas('respondents', [
        'email' => 'peserta.budi@gmail.com',
        'name' => 'Budi Mandor',
        'organization' => 'CV Mandiri Truss',
        'phone' => '081234567890',
    ]);

    // Survey response is stored with email_hash, counted=true, and answers JSON
    $expectedHash = SurveyResponse::hashEmail('peserta.budi@gmail.com');

    $this->assertDatabaseHas('survey_responses', [
        'event_id' => $event->id,
        'email_hash' => $expectedHash,
        'counted' => true,
    ]);

    $surveyResponse = SurveyResponse::where('event_id', $event->id)->first();
    expect($surveyResponse->answers['brand_recalled_kencana'])->toBeTrue();
    expect($surveyResponse->answers['audience_relevance'])->toBeTrue();
    expect($surveyResponse->answers['relationship_score'])->toBe(5);
    expect($surveyResponse->answers['awareness_uplift'])->toBe(2);
});

test('deduplication stores duplicate response with counted=false for audit', function () {
    $branch = Branch::where('code', 'SBY-HQ')->first();
    $event = Event::factory()->create(['branch_id' => $branch->id]);

    $email = 'peserta.dedup@gmail.com';
    $payload = [
        'email' => $email,
        'name' => 'Peserta Dedup',
        'organization' => 'Toko Besi Makmur',
        'phone' => '0899999999',
        'unaided_brand_recall' => 'Kencana',
        'audience_relevance' => true,
        'is_customer_or_partner' => false,
        'relationship_score' => 4,
        'awareness_before' => 2,
        'awareness_after' => 4,
    ];

    // First submission
    $this->post("/survei/{$event->survey_token}", $payload)->assertOk();

    // Second submission with same email for same event
    $this->post("/survei/{$event->survey_token}", $payload)->assertOk();

    $expectedHash = SurveyResponse::hashEmail($email);
    $responses = SurveyResponse::where('event_id', $event->id)
        ->where('email_hash', $expectedHash)
        ->get();

    expect($responses)->toHaveCount(2);

    $countedList = $responses->pluck('counted')->toArray();
    // Exactly one counted=true, one counted=false
    expect(array_filter($countedList, fn ($c) => $c === true))->toHaveCount(1);
    expect(array_filter($countedList, fn ($c) => $c === false))->toHaveCount(1);
});

test('brand recall without assistance accurately identifies other brands', function () {
    $branch = Branch::where('code', 'SBY-HQ')->first();
    $event = Event::factory()->create(['branch_id' => $branch->id]);

    $this->post("/survei/{$event->survey_token}", [
        'email' => 'other.brand@gmail.com',
        'organization' => 'Umum',
        'unaided_brand_recall' => 'Baja Ringan Taso Prima',
        'audience_relevance' => true,
        'is_customer_or_partner' => false,
        'relationship_score' => 3,
        'awareness_before' => 2,
        'awareness_after' => 3,
    ])->assertOk();

    $expectedHash = SurveyResponse::hashEmail('other.brand@gmail.com');
    $response = SurveyResponse::where('email_hash', $expectedHash)->first();
    expect($response->answers['brand_recalled_kencana'])->toBeFalse();
});

test('respondent policy only allows marketing and admin to access respondent identities', function () {
    $marketing = User::factory()->create();
    $marketing->assignRole('marketing');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $pic = User::factory()->create();
    $pic->assignRole('pic');

    $direksi = User::factory()->create();
    $direksi->assignRole('direksi');

    $respondent = Respondent::create([
        'email' => 'confidential@gmail.com',
        'name' => 'Data Rahasia',
        'organization' => 'PT Mitra Terbatas',
        'phone' => '08123',
    ]);

    // Marketing and Admin can view
    expect($marketing->can('view', $respondent))->toBeTrue();
    expect($admin->can('view', $respondent))->toBeTrue();

    // PIC and Direksi CANNOT view
    expect($pic->can('view', $respondent))->toBeFalse();
    expect($direksi->can('view', $respondent))->toBeFalse();
});
