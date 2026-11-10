<?php

namespace Database\Seeders;

use App\Enums\EventType;
use App\Enums\Goal;
use App\Enums\VerificationStatus;
use App\Models\Branch;
use App\Models\Event;
use App\Models\EventReport;
use App\Models\SalesUpdate;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class DemoFixtureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::role('admin')->first() ?? User::first();
        $surabaya = Branch::where('code', 'SBY-HQ')->first();
        $jakarta = Branch::where('code', 'JKT')->first();
        $bandung = Branch::where('code', 'BDG')->first();
        $semarang = Branch::where('code', 'SMG')->first();

        // 1. DEMO-001: Sales Lolos Layak
        $event1 = Event::updateOrCreate(
            ['code' => 'DEMO-001'],
            [
                'name' => 'Simulasi Gathering Toko Baja Ringan Surabaya',
                'type' => EventType::GATHERING_INTERNAL,
                'event_date' => Carbon::now()->subDays(95)->format('Y-m-d'),
                'branch_id' => $surabaya?->id,
                'location' => 'Surabaya',
                'pic_name' => 'Budi Santoso',
                'pic_user_id' => $admin?->id,
                'goal' => Goal::SALES,
                'theme_product' => 'Kencana Truss Baja Ringan',
                'target_participants' => 50,
                'target_leads' => 15,
                'estimated_relevance_pct' => 80,
                'proposal_cost' => 10000000.00,
                'team_budget' => 2000000.00,
                'verification_status' => VerificationStatus::TERVERIFIKASI,
                'survey_token' => 'demo-token-001',
                'submitter_email' => 'admin@kencana.id',
            ]
        );

        EventReport::updateOrCreate(
            ['event_id' => $event1->id],
            [
                'actual_participants' => 60,
                'valid_leads' => 15,
                'actual_team_cost' => 1800000.00,
                'notes' => 'Acara gathering berjalan baik dan hemat biaya',
            ]
        );

        SalesUpdate::updateOrCreate(
            ['event_id' => $event1->id],
            [
                'revenue' => 150000000.00, // 15x proposal (>= 10x)
                'notes' => 'Closing transaksi toko rekanan melampaui target',
            ]
        );

        // 32 survey responses for DEMO-001 (threshold is 30)
        for ($i = 1; $i <= 32; $i++) {
            SurveyResponse::updateOrCreate(
                ['event_id' => $event1->id, 'email_hash' => hash('sha256', "demo1_user{$i}@kencana.id" . config('app.key'))],
                [
                    'counted' => true,
                    'answers' => [
                        'unaided_brand_recall' => ($i % 2 === 0 ? 'Kencana' : 'Kencana Truss'),
                        'brand_recalled_kencana' => true,
                        'audience_relevance' => true,
                        'is_customer_or_partner' => true,
                        'relationship_score' => 5,
                        'awareness_before' => 3,
                        'awareness_after' => 5,
                        'awareness_uplift' => 2,
                    ],
                ]
            );
        }

        // 2. DEMO-002: Awareness Lolos Layak
        $event2 = Event::updateOrCreate(
            ['code' => 'DEMO-002'],
            [
                'name' => 'Simulasi Pameran Indobuildtech Expo Jakarta',
                'type' => EventType::JOIN_EXTERNAL,
                'event_date' => Carbon::now()->subDays(20)->format('Y-m-d'),
                'branch_id' => $jakarta?->id,
                'location' => 'Jakarta',
                'pic_name' => 'Hendra',
                'pic_user_id' => $admin?->id,
                'goal' => Goal::AWARENESS,
                'theme_product' => 'Genteng Metal & Spandek Kencana',
                'target_participants' => 100,
                'target_leads' => 25,
                'estimated_relevance_pct' => 85,
                'proposal_cost' => 25000000.00,
                'team_budget' => 5000000.00,
                'verification_status' => VerificationStatus::TERVERIFIKASI,
                'survey_token' => 'demo-token-002',
                'submitter_email' => 'admin@kencana.id',
            ]
        );

        EventReport::updateOrCreate(
            ['event_id' => $event2->id],
            [
                'actual_participants' => 80,
                'valid_leads' => 25,
                'actual_team_cost' => 4800000.00,
                'notes' => 'Expo berhasil menjangkau banyak pengunjung baru',
            ]
        );

        // 35 responses (threshold is 30): 23 recall kencana (~65.7%), all relevant, uplift avg 0.85
        for ($i = 1; $i <= 35; $i++) {
            $isKencana = ($i <= 23);
            SurveyResponse::updateOrCreate(
                ['event_id' => $event2->id, 'email_hash' => hash('sha256', "demo2_user{$i}@kencana.id" . config('app.key'))],
                [
                    'counted' => true,
                    'answers' => [
                        'unaided_brand_recall' => $isKencana ? 'Kencana' : 'Taso',
                        'brand_recalled_kencana' => $isKencana,
                        'audience_relevance' => ($i <= 31),
                        'is_customer_or_partner' => ($i <= 10),
                        'relationship_score' => 4,
                        'awareness_before' => 3,
                        'awareness_after' => ($i % 2 === 0 ? 4 : 5),
                        'awareness_uplift' => ($i % 2 === 0 ? 1 : 2),
                    ],
                ]
            );
        }

        // 3. DEMO-003: Relationship Lolos Layak
        $event3 = Event::updateOrCreate(
            ['code' => 'DEMO-003'],
            [
                'name' => 'Simulasi Temu Kemitraan Aplikator Bandung',
                'type' => EventType::GATHERING_INTERNAL,
                'event_date' => Carbon::now()->subDays(15)->format('Y-m-d'),
                'branch_id' => $bandung?->id,
                'location' => 'Bandung',
                'pic_name' => 'Deden',
                'pic_user_id' => $admin?->id,
                'goal' => Goal::RELATIONSHIP,
                'theme_product' => 'Kencana Hollow & Galvalum',
                'target_participants' => 30,
                'target_leads' => 10,
                'estimated_relevance_pct' => 90,
                'proposal_cost' => 15000000.00,
                'team_budget' => 3000000.00,
                'verification_status' => VerificationStatus::TERVERIFIKASI,
                'survey_token' => 'demo-token-003',
                'submitter_email' => 'admin@kencana.id',
            ]
        );

        EventReport::updateOrCreate(
            ['event_id' => $event3->id],
            [
                'actual_participants' => 25,
                'valid_leads' => 8,
                'actual_team_cost' => 2900000.00,
                'notes' => 'Hubungan mitra cabang bandung sangat solid',
            ]
        );

        // 22 responses (threshold for 25 is ceil(25*0.8) = 20): 18 partners (>= 5), scores 4-5
        for ($i = 1; $i <= 22; $i++) {
            SurveyResponse::updateOrCreate(
                ['event_id' => $event3->id, 'email_hash' => hash('sha256', "demo3_user{$i}@kencana.id" . config('app.key'))],
                [
                    'counted' => true,
                    'answers' => [
                        'unaided_brand_recall' => 'Kencana',
                        'brand_recalled_kencana' => true,
                        'audience_relevance' => true,
                        'is_customer_or_partner' => ($i <= 18),
                        'relationship_score' => ($i <= 15 ? 5 : 4),
                        'awareness_before' => 4,
                        'awareness_after' => 5,
                        'awareness_uplift' => 1,
                    ],
                ]
            );
        }

        // 4. DEMO-031: Menunggu H+90 & Belum Final
        $event4 = Event::updateOrCreate(
            ['code' => 'DEMO-031'],
            [
                'name' => 'Simulasi Event Belum Final - Menunggu H+90 & Survei',
                'type' => EventType::GATHERING_INTERNAL,
                'event_date' => Carbon::now()->subDays(45)->format('Y-m-d'), // 45 days ago -> H+90 is in 45 days
                'branch_id' => $semarang?->id,
                'location' => 'Semarang',
                'pic_name' => 'Agus',
                'pic_user_id' => $admin?->id,
                'goal' => Goal::SALES,
                'theme_product' => 'Kencana Baja Ringan',
                'target_participants' => 45,
                'target_leads' => 10,
                'estimated_relevance_pct' => 75,
                'proposal_cost' => 12000000.00,
                'team_budget' => 2500000.00,
                'verification_status' => VerificationStatus::BELUM_DIPERIKSA,
                'survey_token' => 'demo-token-031',
                'submitter_email' => 'admin@kencana.id',
            ]
        );

        EventReport::updateOrCreate(
            ['event_id' => $event4->id],
            [
                'actual_participants' => 45,
                'valid_leads' => 10,
                'actual_team_cost' => 2400000.00,
                'notes' => 'Menunggu laporan omzet H+90 dan kecukupan survei',
            ]
        );
    }
}
