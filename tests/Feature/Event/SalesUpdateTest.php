<?php

use App\Enums\Goal;
use App\Enums\VerificationStatus;
use App\Models\Branch;
use App\Models\Event;
use App\Models\SalesUpdate;
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

test('awareness and relationship events cannot access H+90 sales update', function () {
    $branch = Branch::where('code', 'SBY-HQ')->first();
    $pic = User::factory()->create(['branch_id' => $branch->id]);
    $pic->assignRole('pic');

    $eventAwareness = Event::factory()->create([
        'branch_id' => $branch->id,
        'goal' => Goal::AWARENESS,
        'event_date' => now()->subDays(100)->format('Y-m-d'),
    ]);

    $this->actingAs($pic)->get("/events/{$eventAwareness->id}/sales")->assertForbidden();
    $this->actingAs($pic)->post("/events/{$eventAwareness->id}/sales", ['revenue' => 50000000])->assertForbidden();

    $eventRelationship = Event::factory()->create([
        'branch_id' => $branch->id,
        'goal' => Goal::RELATIONSHIP,
        'event_date' => now()->subDays(100)->format('Y-m-d'),
    ]);

    $this->actingAs($pic)->get("/events/{$eventRelationship->id}/sales")->assertForbidden();
    $this->actingAs($pic)->post("/events/{$eventRelationship->id}/sales", ['revenue' => 50000000])->assertForbidden();
});

test('sales event before H+90 days cannot submit H+90 sales update', function () {
    $branch = Branch::where('code', 'SBY-HQ')->first();
    $pic = User::factory()->create(['branch_id' => $branch->id]);
    $pic->assignRole('pic');

    // Exactly 89 days after event date (not yet H+90)
    $event = Event::factory()->create([
        'branch_id' => $branch->id,
        'goal' => Goal::SALES,
        'event_date' => now()->subDays(89)->format('Y-m-d'),
    ]);

    $this->actingAs($pic)->get("/events/{$event->id}/sales")->assertForbidden();
    $this->actingAs($pic)->post("/events/{$event->id}/sales", ['revenue' => 50000000])->assertForbidden();
});

test('sales event exactly on or after H+90 days can submit sales update and upload invoice', function () {
    $branch = Branch::where('code', 'SBY-HQ')->first();
    $pic = User::factory()->create(['branch_id' => $branch->id]);
    $pic->assignRole('pic');

    // Exactly 90 days after event date
    $event = Event::factory()->create([
        'branch_id' => $branch->id,
        'goal' => Goal::SALES,
        'event_date' => now()->subDays(90)->format('Y-m-d'),
    ]);

    $invoiceFile = UploadedFile::fake()->create('invoice_kencana.pdf', 1024, 'application/pdf');

    $response = $this->actingAs($pic)->post("/events/{$event->id}/sales", [
        'revenue' => 150000000,
        'invoice' => $invoiceFile,
        'notes' => 'Closing 3 proyek perumahan',
    ]);

    $response->assertRedirect('/events');

    $this->assertDatabaseHas('sales_updates', [
        'event_id' => $event->id,
        'revenue' => 150000000.00,
        'notes' => 'Closing 3 proyek perumahan',
    ]);

    $salesUpdate = SalesUpdate::where('event_id', $event->id)->first();
    expect($salesUpdate->invoice_path)->not->toBeNull();
    Storage::disk('private')->assertExists($salesUpdate->invoice_path);
});

test('null revenue remains null and 0 is preserved as 0.00', function () {
    $branch = Branch::where('code', 'SBY-HQ')->first();
    $pic = User::factory()->create(['branch_id' => $branch->id]);
    $pic->assignRole('pic');

    $event1 = Event::factory()->create([
        'branch_id' => $branch->id,
        'goal' => Goal::SALES,
        'event_date' => now()->subDays(95)->format('Y-m-d'),
    ]);

    // Empty revenue (unreported)
    $this->actingAs($pic)->post("/events/{$event1->id}/sales", [
        'revenue' => '',
        'notes' => 'Belum ada laporan dari tim sales',
    ]);

    $sales1 = SalesUpdate::where('event_id', $event1->id)->first();
    expect($sales1->revenue)->toBeNull();

    $event2 = Event::factory()->create([
        'branch_id' => $branch->id,
        'goal' => Goal::SALES,
        'event_date' => now()->subDays(95)->format('Y-m-d'),
    ]);

    // Explicitly 0 revenue
    $this->actingAs($pic)->post("/events/{$event2->id}/sales", [
        'revenue' => 0,
        'notes' => 'Tidak ada closing transaksi',
    ]);

    $sales2 = SalesUpdate::where('event_id', $event2->id)->first();
    expect($sales2->revenue)->not->toBeNull();
    expect((float) $sales2->revenue)->toBe(0.00);
});

test('modifying sales update after event is verified resets verification status and marks director decision stale', function () {
    $branch = Branch::where('code', 'SBY-HQ')->first();
    $pic = User::factory()->create(['branch_id' => $branch->id]);
    $pic->assignRole('pic');

    $event = Event::factory()->create([
        'branch_id' => $branch->id,
        'goal' => Goal::SALES,
        'event_date' => now()->subDays(100)->format('Y-m-d'),
        'verification_status' => VerificationStatus::TERVERIFIKASI,
        'director_decision' => 'Layak Diikuti Lagi',
        'decision_stale_flag' => false,
    ]);

    $this->actingAs($pic)->post("/events/{$event->id}/sales", [
        'revenue' => 200000000,
        'notes' => 'Koreksi penambahan transaksi susulan',
    ]);

    $event->refresh();
    expect($event->verification_status)->toBe(VerificationStatus::BELUM_DIPERIKSA);
    expect($event->decision_stale_flag)->toBeTrue();
    expect($event->director_decision)->toBe('Layak Diikuti Lagi');
});

test('only authorized users can download invoice from private disk', function () {
    $branchSby = Branch::where('code', 'SBY-HQ')->first();
    $branchJkt = Branch::where('code', 'JKT')->first();

    $picSby = User::factory()->create(['branch_id' => $branchSby->id]);
    $picSby->assignRole('pic');

    $picJkt = User::factory()->create(['branch_id' => $branchJkt->id]);
    $picJkt->assignRole('pic');

    $marketing = User::factory()->create();
    $marketing->assignRole('marketing');

    $event = Event::factory()->create([
        'branch_id' => $branchSby->id,
        'goal' => Goal::SALES,
        'event_date' => now()->subDays(92)->format('Y-m-d'),
    ]);

    $path = Storage::disk('private')->put('invoices/test.pdf', 'dummy pdf content');
    SalesUpdate::create([
        'event_id' => $event->id,
        'revenue' => 50000000,
        'invoice_path' => 'invoices/test.pdf',
    ]);

    // PIC Surabaya (owner) can download
    $this->actingAs($picSby)->get("/events/{$event->id}/invoice")->assertOk();

    // Marketing can download
    $this->actingAs($marketing)->get("/events/{$event->id}/invoice")->assertOk();

    // PIC Jakarta (different branch) CANNOT download
    $this->actingAs($picJkt)->get("/events/{$event->id}/invoice")->assertForbidden();
});
