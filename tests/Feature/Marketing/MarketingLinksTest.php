<?php

use App\Enums\Goal;
use App\Models\Branch;
use App\Models\Event;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleAndPermissionSeeder;

beforeEach(function () {
    $this->seed(BranchSeeder::class);
    $this->seed(RoleAndPermissionSeeder::class);
});

test('marketing and admin can view marketing links and qr code page', function () {
    $branch = Branch::where('code', 'SBY-HQ')->first();
    $marketing = User::factory()->create();
    $marketing->assignRole('marketing');

    $event = Event::factory()->create([
        'code' => 'SBY-GATH-01',
        'branch_id' => $branch->id,
        'goal' => Goal::SALES,
    ]);

    $response = $this->actingAs($marketing)->get('/marketing/links');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Marketing/Links')
        ->has('events.data', 1)
        ->where('events.data.0.code', 'SBY-GATH-01')
        ->where('events.data.0.survey_url', url("/survei/{$event->survey_token}"))
    );
});

test('pic and direksi cannot access marketing links page', function () {
    $pic = User::factory()->create();
    $pic->assignRole('pic');

    $direksi = User::factory()->create();
    $direksi->assignRole('direksi');

    $this->actingAs($pic)->get('/marketing/links')->assertForbidden();
    $this->actingAs($direksi)->get('/marketing/links')->assertForbidden();
});

test('marketing can download qr code png with strict naming QR_SURVEI_[KODE].png', function () {
    $branch = Branch::where('code', 'SBY-HQ')->first();
    $marketing = User::factory()->create();
    $marketing->assignRole('marketing');

    $event = Event::factory()->create([
        'code' => 'SBY-QR-TEST-01',
        'branch_id' => $branch->id,
    ]);

    $response = $this->actingAs($marketing)->get("/marketing/links/{$event->id}/qr");

    $response->assertOk();
    $response->assertHeader('Content-Type', 'image/png');
    $response->assertHeader('Content-Disposition', 'attachment; filename="QR_SURVEI_SBY-QR-TEST-01.png"');

    // Binary content should be a valid PNG (PNG magic header: 89 50 4E 47 0D 0A 1A 0A)
    $content = $response->getContent();
    expect(str_starts_with($content, "\x89PNG\r\n\x1a\n"))->toBeTrue();
});
