<?php

use App\Enums\Goal;
use App\Enums\EventPortfolio;
use App\Enums\VerificationStatus;
use App\Models\Branch;
use App\Models\Event;
use App\Models\EventReport;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(BranchSeeder::class);
    $this->seed(RoleAndPermissionSeeder::class);
    Storage::fake('private');
});

test('pic can upload proposal PDF when creating event', function () {
    $branch = Branch::where('code', 'SBY-HQ')->first();
    $pic = User::factory()->create(['branch_id' => $branch->id]);
    $pic->assignRole('pic');

    $file = UploadedFile::fake()->create('proposal_kencana.pdf', 500, 'application/pdf');

    $response = $this->actingAs($pic)->post('/events', [
        'code' => 'SBY-DOC-01',
        'name' => 'Gathering Toko Besi Surabaya',
        'event_date' => now()->addDays(7)->format('Y-m-d'),
        'location' => 'Hotel Bumi Surabaya',
        'pic_name' => 'Budi Santoso',
        'branch_id' => $branch->id,
        'goal' => Goal::SALES->value,
        'type' => 'gathering_internal',
        'theme_product' => 'Atap Kencana Maxima',
        'target_participants' => 60,
        'target_leads' => 20,
        'estimated_relevance_pct' => 80.0,
        'proposal_cost' => 12000000,
        'team_budget' => 3000000,
        'proposal_doc' => $file,
    ]);

    $response->assertRedirect('/events');

    $event = Event::where('code', 'SBY-DOC-01')->first();
    expect($event)->not->toBeNull();
    expect($event->proposal_doc_path)->not->toBeNull();

    Storage::disk('private')->assertExists($event->proposal_doc_path);
});

test('proposal upload rejects non-pdf files', function () {
    $branch = Branch::where('code', 'SBY-HQ')->first();
    $pic = User::factory()->create(['branch_id' => $branch->id]);
    $pic->assignRole('pic');

    $file = UploadedFile::fake()->create('script.exe', 500, 'application/x-msdownload');

    $response = $this->actingAs($pic)->post('/events', [
        'code' => 'SBY-INVALID-01',
        'name' => 'Gathering Invalid',
        'event_date' => now()->addDays(7)->format('Y-m-d'),
        'location' => 'Hotel Bumi Surabaya',
        'pic_name' => 'Budi Santoso',
        'branch_id' => $branch->id,
        'goal' => Goal::SALES->value,
        'type' => 'gathering_internal',
        'theme_product' => 'Atap Kencana Maxima',
        'target_participants' => 60,
        'target_leads' => 20,
        'estimated_relevance_pct' => 80.0,
        'proposal_cost' => 12000000,
        'team_budget' => 3000000,
        'proposal_doc' => $file,
    ]);

    $response->assertSessionHasErrors(['proposal_doc']);
});

test('authorized pic and marketing can download proposal PDF', function () {
    $branch = Branch::where('code', 'SBY-HQ')->first();
    $pic = User::factory()->create(['branch_id' => $branch->id]);
    $pic->assignRole('pic');

    $marketing = User::factory()->create();
    $marketing->assignRole('marketing');

    $path = Storage::disk('private')->putFile('proposals', UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'));

    $event = Event::factory()->create([
        'branch_id' => $branch->id,
        'pic_user_id' => $pic->id,
        'proposal_doc_path' => $path,
    ]);

    // PIC can download
    $resPic = $this->actingAs($pic)->get("/events/{$event->id}/proposal");
    $resPic->assertOk();

    // Marketing can download
    $resMkt = $this->actingAs($marketing)->get("/events/{$event->id}/proposal");
    $resMkt->assertOk();
});

test('pic from another branch is forbidden from downloading proposal PDF', function () {
    $branchSby = Branch::where('code', 'SBY-HQ')->first();
    $branchJkt = Branch::where('code', 'JKT')->first();

    $picSby = User::factory()->create(['branch_id' => $branchSby->id]);
    $picSby->assignRole('pic');

    $picJkt = User::factory()->create(['branch_id' => $branchJkt->id]);
    $picJkt->assignRole('pic');

    $path = Storage::disk('private')->putFile('proposals', UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'));

    $event = Event::factory()->create([
        'branch_id' => $branchSby->id,
        'pic_user_id' => $picSby->id,
        'proposal_doc_path' => $path,
    ]);

    $response = $this->actingAs($picJkt)->get("/events/{$event->id}/proposal");
    $response->assertForbidden();
});

test('pic can upload attendance and documentation files when submitting report', function () {
    $branch = Branch::where('code', 'SBY-HQ')->first();
    $pic = User::factory()->create(['branch_id' => $branch->id]);
    $pic->assignRole('pic');

    $event = Event::factory()->create([
        'branch_id' => $branch->id,
        'pic_user_id' => $pic->id,
        'event_date' => now()->subDay()->format('Y-m-d'),
    ]);

    $attendanceFile = UploadedFile::fake()->create('daftar_hadir.pdf', 200, 'application/pdf');
    $docPhoto = UploadedFile::fake()->image('foto_suasana.jpg');

    $response = $this->actingAs($pic)->post("/events/{$event->id}/report", [
        'actual_participants' => 75,
        'valid_leads' => 15,
        'actual_team_cost' => 2500000,
        'notes' => 'Laporan lengkap dengan presensi & foto',
        'attendance_doc' => $attendanceFile,
        'documentation_doc' => $docPhoto,
    ]);

    $response->assertRedirect('/events');

    $report = EventReport::where('event_id', $event->id)->first();
    expect($report)->not->toBeNull();
    expect($report->attendance_doc_path)->not->toBeNull();
    expect($report->documentation_doc_path)->not->toBeNull();

    Storage::disk('private')->assertExists($report->attendance_doc_path);
    Storage::disk('private')->assertExists($report->documentation_doc_path);

    // Download test
    $this->actingAs($pic)->get("/events/{$event->id}/attendance")->assertOk();
    $this->actingAs($pic)->get("/events/{$event->id}/documentation")->assertOk();
});
