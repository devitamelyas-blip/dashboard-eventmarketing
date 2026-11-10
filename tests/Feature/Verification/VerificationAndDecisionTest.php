<?php

use App\Enums\EventType;
use App\Enums\Goal;
use App\Enums\VerificationStatus;
use App\Mail\EventCorrectionRequiredMail;
use App\Mail\PostEventReportSubmittedMail;
use App\Mail\SalesUpdateReminderMail;
use App\Mail\SalesUpdateSubmittedMail;
use App\Models\Branch;
use App\Models\Event;
use App\Models\EventReport;
use App\Models\SalesUpdate;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BranchSeeder::class);
    $this->seed(RoleAndPermissionSeeder::class);
});

test('marketing can verify operational event with valid data', function () {
    $branch = Branch::first();
    $pic = User::factory()->create(['branch_id' => $branch->id]);
    $pic->assignRole('pic');

    $marketing = User::factory()->create();
    $marketing->assignRole('marketing');

    $event = Event::create([
        'code' => 'KMB-SBY-001',
        'name' => 'Operational Expo 2026',
        'type' => EventType::GATHERING_INTERNAL,
        'event_date' => now()->subDays(10),
        'branch_id' => $branch->id,
        'location' => 'Surabaya Grand City',
        'pic_name' => 'Budi Santoso',
        'pic_user_id' => $pic->id,
        'submitter_email' => $pic->email,
        'goal' => Goal::AWARENESS,
        'theme_product' => 'Genteng Metal Kencana',
        'target_participants' => 100,
        'target_leads' => 20,
        'proposal_cost' => 5000000,
        'team_budget' => 1000000,
        'verification_status' => VerificationStatus::BELUM_DIPERIKSA,
    ]);

    $response = $this->actingAs($marketing)->post("/events/{$event->id}/verify", [
        'verification_status' => 'terverifikasi',
        'verification_note' => 'Dokumen dan realisasi lengkap.',
    ]);

    $response->assertRedirect();
    $event->refresh();
    expect($event->verification_status)->toBe(VerificationStatus::TERVERIFIKASI)
        ->and($event->verification_note)->toBe('Dokumen dan realisasi lengkap.');
});

test('system blocks verification of demo or uat events as operational', function () {
    $branch = Branch::first();
    $marketing = User::factory()->create();
    $marketing->assignRole('marketing');

    $pic = User::factory()->create(['branch_id' => $branch->id]);
    $pic->assignRole('pic');

    // Demo event
    $demoEvent = Event::create([
        'code' => 'DEMO-999',
        'name' => 'Demo Architectural Mockup',
        'type' => EventType::JOIN_EXTERNAL,
        'event_date' => now()->subDays(5),
        'branch_id' => $branch->id,
        'location' => 'Jakarta',
        'pic_name' => 'Tester',
        'pic_user_id' => $pic->id,
        'submitter_email' => $pic->email,
        'goal' => Goal::AWARENESS,
        'theme_product' => 'Baja Ringan',
        'target_participants' => 30,
        'target_leads' => 5,
        'proposal_cost' => 2000000,
        'team_budget' => 500000,
        'verification_status' => VerificationStatus::BELUM_DIPERIKSA,
    ]);

    $response = $this->actingAs($marketing)->post("/events/{$demoEvent->id}/verify", [
        'verification_status' => 'terverifikasi',
    ]);

    $response->assertSessionHasErrors(['verification_status']);
    $demoEvent->refresh();
    expect($demoEvent->verification_status)->toBe(VerificationStatus::BELUM_DIPERIKSA);
});

test('marketing requires note when marking status perlu_koreksi and sends email to pic', function () {
    Mail::fake();

    $branch = Branch::first();
    $pic = User::factory()->create(['branch_id' => $branch->id]);
    $pic->assignRole('pic');

    $marketing = User::factory()->create();
    $marketing->assignRole('marketing');

    $event = Event::create([
        'code' => 'KMB-BDG-001',
        'name' => 'Bandung Builders Gathering',
        'type' => EventType::GATHERING_INTERNAL,
        'event_date' => now()->subDays(5),
        'branch_id' => $branch->id,
        'location' => 'Bandung',
        'pic_name' => 'Ahmad',
        'pic_user_id' => $pic->id,
        'submitter_email' => $pic->email,
        'goal' => Goal::RELATIONSHIP,
        'theme_product' => 'Rangka Hollow',
        'target_participants' => 50,
        'target_leads' => 10,
        'proposal_cost' => 4000000,
        'team_budget' => 1000000,
        'verification_status' => VerificationStatus::BELUM_DIPERIKSA,
    ]);

    // Attempt without note should fail validation
    $failedResponse = $this->actingAs($marketing)->post("/events/{$event->id}/verify", [
        'verification_status' => 'perlu_koreksi',
        'verification_note' => '',
    ]);
    $failedResponse->assertSessionHasErrors(['verification_note']);

    // Providing note succeeds and sends email
    $successResponse = $this->actingAs($marketing)->post("/events/{$event->id}/verify", [
        'verification_status' => 'perlu_koreksi',
        'verification_note' => 'Jumlah peserta aktual di laporan tidak wajar, mohon lampirkan presensi.',
    ]);

    $successResponse->assertRedirect();
    $event->refresh();
    expect($event->verification_status)->toBe(VerificationStatus::PERLU_KOREKSI);

    Mail::assertSent(EventCorrectionRequiredMail::class, function ($mail) use ($pic) {
        return $mail->hasTo($pic->email) && str_contains($mail->reason, 'Jumlah peserta aktual');
    });
});

test('pic cannot verify events or record director decisions', function () {
    $branch = Branch::first();
    $pic = User::factory()->create(['branch_id' => $branch->id]);
    $pic->assignRole('pic');

    $event = Event::create([
        'code' => 'KMB-JKT-001',
        'name' => 'Jakarta Expo',
        'type' => EventType::JOIN_EXTERNAL,
        'event_date' => now()->subDays(5),
        'branch_id' => $branch->id,
        'location' => 'JIExpo Kemayoran',
        'pic_name' => 'Bambang',
        'pic_user_id' => $pic->id,
        'submitter_email' => $pic->email,
        'goal' => Goal::SALES,
        'theme_product' => 'Genteng Metal',
        'target_participants' => 80,
        'target_leads' => 20,
        'proposal_cost' => 8000000,
        'team_budget' => 2000000,
        'verification_status' => VerificationStatus::BELUM_DIPERIKSA,
    ]);

    // PIC cannot verify
    $this->actingAs($pic)->post("/events/{$event->id}/verify", [
        'verification_status' => 'terverifikasi',
    ])->assertForbidden();

    // PIC cannot record director decision
    $this->actingAs($pic)->post("/events/{$event->id}/decision", [
        'director_decision' => 'layak',
    ])->assertForbidden();
});

test('marketing cannot record or modify director decision', function () {
    $branch = Branch::first();
    $marketing = User::factory()->create();
    $marketing->assignRole('marketing');

    $pic = User::factory()->create(['branch_id' => $branch->id]);
    $pic->assignRole('pic');

    $event = Event::create([
        'code' => 'KMB-MLG-001',
        'name' => 'Malang Partner Day',
        'type' => EventType::GATHERING_INTERNAL,
        'event_date' => now()->subDays(12),
        'branch_id' => $branch->id,
        'location' => 'Malang',
        'pic_name' => 'Doni',
        'pic_user_id' => $pic->id,
        'submitter_email' => $pic->email,
        'goal' => Goal::RELATIONSHIP,
        'theme_product' => 'Genteng Metal',
        'target_participants' => 30,
        'target_leads' => 10,
        'proposal_cost' => 3000000,
        'team_budget' => 500000,
        'verification_status' => VerificationStatus::TERVERIFIKASI,
    ]);

    $this->actingAs($marketing)->post("/events/{$event->id}/decision", [
        'director_decision' => 'layak',
        'director_note' => 'Diubah oleh marketing',
    ])->assertForbidden();
});

test('direksi and admin can record director decision and reset stale flag', function () {
    $branch = Branch::first();
    $pic = User::factory()->create(['branch_id' => $branch->id]);
    $pic->assignRole('pic');

    $direksi = User::factory()->create();
    $direksi->assignRole('direksi');

    $event = Event::create([
        'code' => 'KMB-SMG-001',
        'name' => 'Semarang Expo',
        'type' => EventType::JOIN_EXTERNAL,
        'event_date' => now()->subDays(10),
        'branch_id' => $branch->id,
        'location' => 'Semarang',
        'pic_name' => 'Eko',
        'pic_user_id' => $pic->id,
        'submitter_email' => $pic->email,
        'goal' => Goal::AWARENESS,
        'theme_product' => 'Atap Spandek',
        'target_participants' => 50,
        'target_leads' => 15,
        'proposal_cost' => 6000000,
        'team_budget' => 1000000,
        'verification_status' => VerificationStatus::TERVERIFIKASI,
        'decision_stale_flag' => true, // Flagged because report was modified
    ]);

    $response = $this->actingAs($direksi)->post("/events/{$event->id}/decision", [
        'director_decision' => 'layak',
        'director_note' => 'Disetujui untuk diulang tahun depan dengan penyesuaian materi promosi.',
    ]);

    $response->assertRedirect();
    $event->refresh();
    expect($event->director_decision)->toBe('layak')
        ->and($event->director_note)->toContain('Disetujui untuk diulang')
        ->and($event->decision_stale_flag)->toBeFalse();
});

test('submitting report or sales update sends notification email to marketing', function () {
    Mail::fake();

    $branch = Branch::first();
    $pic = User::factory()->create(['branch_id' => $branch->id]);
    $pic->assignRole('pic');

    $marketing = User::factory()->create();
    $marketing->assignRole('marketing');

    $event = Event::create([
        'code' => 'KMB-DPS-001',
        'name' => 'Denpasar Gathering',
        'type' => EventType::GATHERING_INTERNAL,
        'event_date' => now()->subDays(95),
        'branch_id' => $branch->id,
        'location' => 'Denpasar',
        'pic_name' => 'Wayan',
        'pic_user_id' => $pic->id,
        'submitter_email' => $pic->email,
        'goal' => Goal::SALES,
        'theme_product' => 'Baja Ringan',
        'target_participants' => 40,
        'target_leads' => 10,
        'proposal_cost' => 5000000,
        'team_budget' => 1000000,
        'verification_status' => VerificationStatus::BELUM_DIPERIKSA,
    ]);

    // 1. Submit Post-Event Report
    $this->actingAs($pic)->post("/events/{$event->id}/report", [
        'actual_participants' => 38,
        'valid_leads' => 9,
        'actual_team_cost' => 900000,
        'notes' => 'Acara berlangsung lancar.',
    ])->assertRedirect();

    Mail::assertSent(PostEventReportSubmittedMail::class, function ($mail) use ($marketing) {
        return $mail->hasTo($marketing->email);
    });

    // 2. Submit Sales Update H+90
    $this->actingAs($pic)->post("/events/{$event->id}/sales", [
        'revenue' => 85000000,
        'notes' => 'Omzet terealisasi dari 5 proyek mitra.',
    ])->assertRedirect();

    Mail::assertSent(SalesUpdateSubmittedMail::class, function ($mail) use ($marketing) {
        return $mail->hasTo($marketing->email);
    });
});

test('artisan devon:remind-h90 sends email reminder to pic for overdue sales updates', function () {
    Mail::fake();

    $branch = Branch::first();
    $pic = User::factory()->create(['branch_id' => $branch->id]);
    $pic->assignRole('pic');

    // Sales event past 90 days without revenue
    $eventOverdue = Event::create([
        'code' => 'KMB-SLS-090',
        'name' => 'Overdue Sales Fair',
        'type' => EventType::GATHERING_INTERNAL,
        'event_date' => now()->subDays(95),
        'branch_id' => $branch->id,
        'location' => 'Solo',
        'pic_name' => 'Joko',
        'pic_user_id' => $pic->id,
        'submitter_email' => $pic->email,
        'goal' => Goal::SALES,
        'theme_product' => 'Genteng Metal',
        'target_participants' => 50,
        'target_leads' => 10,
        'proposal_cost' => 5000000,
        'team_budget' => 1000000,
        'verification_status' => VerificationStatus::BELUM_DIPERIKSA,
    ]);

    // Sales event not yet 90 days
    $eventRecent = Event::create([
        'code' => 'KMB-SLS-030',
        'name' => 'Recent Sales Fair',
        'type' => EventType::GATHERING_INTERNAL,
        'event_date' => now()->subDays(30),
        'branch_id' => $branch->id,
        'location' => 'Yogyakarta',
        'pic_name' => 'Tri',
        'pic_user_id' => $pic->id,
        'submitter_email' => $pic->email,
        'goal' => Goal::SALES,
        'theme_product' => 'Genteng Metal',
        'target_participants' => 50,
        'target_leads' => 10,
        'proposal_cost' => 5000000,
        'team_budget' => 1000000,
        'verification_status' => VerificationStatus::BELUM_DIPERIKSA,
    ]);

    $this->artisan('devon:remind-h90')->assertSuccessful();

    // Only overdue event should trigger reminder mail
    Mail::assertSent(SalesUpdateReminderMail::class, function ($mail) use ($eventOverdue) {
        return $mail->event->id === $eventOverdue->id;
    });

    Mail::assertNotSent(SalesUpdateReminderMail::class, function ($mail) use ($eventRecent) {
        return $mail->event->id === $eventRecent->id;
    });
});
