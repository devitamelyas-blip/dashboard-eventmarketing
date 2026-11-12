<?php

use App\Enums\Goal;
use App\Enums\EventPortfolio;
use App\Enums\VerificationStatus;
use App\Models\Branch;
use App\Models\Event;
use App\Models\User;
use App\Notifications\EventActivityNotification;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleAndPermissionSeeder;

beforeEach(function () {
    $this->seed(BranchSeeder::class);
    $this->seed(RoleAndPermissionSeeder::class);
});

test('marketing users receive in-app notification when pic creates event', function () {
    $branch = Branch::where('code', 'SBY-HQ')->first();
    $pic = User::factory()->create(['branch_id' => $branch->id]);
    $pic->assignRole('pic');

    $marketing = User::factory()->create();
    $marketing->assignRole('marketing');

    $this->actingAs($pic)->post('/events', [
        'code' => 'SBY-NOTIF-01',
        'name' => 'Gathering Mitra Kencana',
        'event_date' => now()->addDays(10)->format('Y-m-d'),
        'location' => 'Hotel Bumi Surabaya',
        'pic_name' => 'Budi Santoso',
        'branch_id' => $branch->id,
        'goal' => Goal::SALES->value,
        'type' => 'gathering_internal',
        'theme_product' => 'Atap Kencana Maxima',
        'target_participants' => 50,
        'target_leads' => 15,
        'estimated_relevance_pct' => 85.0,
        'proposal_cost' => 10000000,
        'team_budget' => 2000000,
    ]);

    expect($marketing->notifications()->count())->toBe(1);
    $notification = $marketing->notifications()->first();
    expect($notification->data['type'])->toBe('info');
    expect($notification->data['title'])->toContain('Pengajuan Event Baru');
});

test('marketing users receive in-app notification when pic submits report', function () {
    $branch = Branch::where('code', 'SBY-HQ')->first();
    $pic = User::factory()->create(['branch_id' => $branch->id]);
    $pic->assignRole('pic');

    $marketing = User::factory()->create();
    $marketing->assignRole('marketing');

    $event = Event::factory()->create([
        'branch_id' => $branch->id,
        'pic_user_id' => $pic->id,
        'event_date' => now()->subDay()->format('Y-m-d'),
    ]);

    $this->actingAs($pic)->post("/events/{$event->id}/report", [
        'actual_participants' => 50,
        'valid_leads' => 10,
        'actual_team_cost' => 2000000,
        'notes' => 'Acara sukses',
    ]);

    expect($marketing->notifications()->count())->toBe(1);
    $notification = $marketing->notifications()->first();
    expect($notification->data['title'])->toContain('Laporan Pasca-Event Diisi');
});

test('pic receives in-app notification when marketing verifies event', function () {
    $branch = Branch::where('code', 'SBY-HQ')->first();
    $pic = User::factory()->create(['branch_id' => $branch->id]);
    $pic->assignRole('pic');

    $marketing = User::factory()->create();
    $marketing->assignRole('marketing');

    $event = Event::factory()->create([
        'branch_id' => $branch->id,
        'pic_user_id' => $pic->id,
        'verification_status' => VerificationStatus::BELUM_DIPERIKSA,
    ]);

    $this->actingAs($marketing)->post("/events/{$event->id}/verify", [
        'verification_status' => VerificationStatus::TERVERIFIKASI->value,
        'verification_note' => 'Dokumen dan bukti fisik lengkap',
    ]);

    expect($pic->notifications()->count())->toBe(1);
    $notification = $pic->notifications()->first();
    expect($notification->data['title'])->toContain('Status Verifikasi');
});

test('pic receives in-app notification when director makes decision', function () {
    $branch = Branch::where('code', 'SBY-HQ')->first();
    $pic = User::factory()->create(['branch_id' => $branch->id]);
    $pic->assignRole('pic');

    $director = User::factory()->create();
    $director->assignRole('direksi');

    $event = Event::factory()->create([
        'branch_id' => $branch->id,
        'pic_user_id' => $pic->id,
    ]);

    $this->actingAs($director)->post("/events/{$event->id}/decision", [
        'director_decision' => 'layak',
        'director_note' => 'Disetujui untuk replikasi tahun depan',
    ]);

    expect($pic->notifications()->count())->toBe(1);
    $notification = $pic->notifications()->first();
    expect($notification->data['title'])->toContain('Keputusan Direksi');
});

test('user can mark notification as read', function () {
    $user = User::factory()->create();
    $user->notify(new EventActivityNotification(
        title: 'Test Notifikasi',
        message: 'Pesan pengujian',
        url: '/events',
        type: 'test'
    ));

    $notification = $user->unreadNotifications()->first();
    expect($notification)->not->toBeNull();

    $response = $this->actingAs($user)->postJson("/notifications/{$notification->id}/read");
    $response->assertOk();

    expect($user->fresh()->unreadNotifications()->count())->toBe(0);
});

test('user can mark all notifications as read', function () {
    $user = User::factory()->create();
    $user->notify(new EventActivityNotification('Judul 1', 'Pesan 1', '/events', 'test'));
    $user->notify(new EventActivityNotification('Judul 2', 'Pesan 2', '/events', 'test'));

    expect($user->unreadNotifications()->count())->toBe(2);

    $response = $this->actingAs($user)->postJson('/notifications/read-all');
    $response->assertOk();

    expect($user->fresh()->unreadNotifications()->count())->toBe(0);
});
