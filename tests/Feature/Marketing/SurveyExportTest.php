<?php

use App\Enums\Goal;
use App\Models\Branch;
use App\Models\Event;
use App\Models\SurveyResponse;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleAndPermissionSeeder;

beforeEach(function () {
    $this->seed(BranchSeeder::class);
    $this->seed(RoleAndPermissionSeeder::class);
});

test('marketing can export survey responses as UTF-8 BOM CSV', function () {
    $branch = Branch::where('code', 'SBY-HQ')->first();
    $marketing = User::factory()->create();
    $marketing->assignRole('marketing');

    $event = Event::factory()->create([
        'code' => 'SBY-EXP-01',
        'name' => 'Gathering Arsitek Surabaya',
        'branch_id' => $branch->id,
        'goal' => Goal::SALES,
    ]);

    SurveyResponse::create([
        'event_id' => $event->id,
        'email_hash' => hash('sha256', 'test@kencana.id'),
        'counted' => true,
        'answers' => [
            'unaided_brand_recall' => 'Kencana Maxima',
            'audience_relevance' => true,
            'is_customer_or_partner' => true,
            'relationship_score' => 5,
            'awareness_before' => 2,
            'awareness_after' => 5,
        ],
        'submitted_at' => now(),
    ]);

    $response = $this->actingAs($marketing)->get("/marketing/links/{$event->id}/survey-responses");

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

    $content = $response->streamedContent();

    // Verify UTF-8 BOM is present
    expect(str_starts_with($content, "\xEF\xBB\xBF"))->toBeTrue();

    // Verify CSV Headers
    expect($content)->toContain('ID Respon');
    expect($content)->toContain('Spontaneous Brand Recall (Unaided)');
    expect($content)->toContain('Uplift Pemahaman');

    // Verify Row Data
    expect($content)->toContain('SBY-EXP-01');
    expect($content)->toContain('Gathering Arsitek Surabaya');
    expect($content)->toContain('Kencana Maxima');
    expect($content)->toContain('+3');
});

test('pic cannot export survey responses', function () {
    $branch = Branch::where('code', 'SBY-HQ')->first();
    $pic = User::factory()->create(['branch_id' => $branch->id]);
    $pic->assignRole('pic');

    $event = Event::factory()->create([
        'branch_id' => $branch->id,
    ]);

    $response = $this->actingAs($pic)->get("/marketing/links/{$event->id}/survey-responses");
    $response->assertForbidden();
});

test('admin can export survey responses', function () {
    $branch = Branch::where('code', 'SBY-HQ')->first();
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $event = Event::factory()->create([
        'code' => 'SBY-ADM-01',
        'branch_id' => $branch->id,
    ]);

    $response = $this->actingAs($admin)->get("/marketing/links/{$event->id}/survey-responses");
    $response->assertOk();
});
