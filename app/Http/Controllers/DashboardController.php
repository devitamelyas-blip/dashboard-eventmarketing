<?php

namespace App\Http\Controllers;

use App\Enums\EventPortfolio;
use App\Enums\Goal;
use App\Enums\VerificationStatus;
use App\Models\Event;
use App\Services\Evaluation\EventEvaluator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display executive dashboard (Sheet 04 equivalent).
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Base Query scoped to role and portfolio
        $baseQuery = Event::query();

        if ($user->hasRole('pic')) {
            $baseQuery->where('branch_id', $user->branch_id);
        }

        // 1. Portfolio Filter (default: operational if operational events exist, otherwise 'all')
        $selectedPortfolio = $request->input('portfolio');
        if (!$selectedPortfolio || !in_array($selectedPortfolio, ['operational', 'demo', 'uat', 'all'])) {
            $hasOperational = (clone $baseQuery)
                ->where('code', 'not like', 'DEMO-%')
                ->where('code', 'not like', 'UAT-%')
                ->exists();
            $selectedPortfolio = $hasOperational ? EventPortfolio::OPERATIONAL->value : 'all';
        }

        $applyPortfolioFilter = function ($query) use ($selectedPortfolio) {
            if ($selectedPortfolio === 'operational') {
                $query->where('code', 'not like', 'DEMO-%')
                      ->where('code', 'not like', 'UAT-%');
            } elseif ($selectedPortfolio === 'demo') {
                $query->where('code', 'like', 'DEMO-%');
            } elseif ($selectedPortfolio === 'uat') {
                $query->where('code', 'like', 'UAT-%');
            }
        };

        $portfolioEventsQuery = (clone $baseQuery);
        $applyPortfolioFilter($portfolioEventsQuery);

        // KPI Ringkasan
        $totalEventsCount = (clone $portfolioEventsQuery)->count();

        // KPI 1: Event Terverifikasi
        $verifiedEventsCount = (clone $portfolioEventsQuery)
            ->where('verification_status', VerificationStatus::TERVERIFIKASI)
            ->count();

        // KPI 2: Menunggu Verifikasi
        $pendingVerificationCount = (clone $portfolioEventsQuery)
            ->where('verification_status', VerificationStatus::BELUM_DIPERIKSA)
            ->count();

        // KPI 2b: Perlu Koreksi
        $correctionNeededCount = (clone $portfolioEventsQuery)
            ->where('verification_status', VerificationStatus::PERLU_KOREKSI)
            ->count();

        // Total Anggaran Rencana (Proposal + Budget Tim)
        $totalBudget = (float) (clone $portfolioEventsQuery)
            ->selectRaw('COALESCE(SUM(proposal_cost + team_budget), 0) as total')
            ->value('total');

        // KPI 3: Biaya Aktual Terverifikasi (proposal_cost + actual_team_cost untuk event terverifikasi)
        $verifiedCostEvents = (clone $portfolioEventsQuery)
            ->where('verification_status', VerificationStatus::TERVERIFIKASI)
            ->with('report')
            ->get();

        $verifiedActualCost = 0.0;
        foreach ($verifiedCostEvents as $evt) {
            if ($evt->report && $evt->report->actual_team_cost !== null) {
                $verifiedActualCost += ((float) $evt->proposal_cost + (float) $evt->report->actual_team_cost);
            }
        }

        // KPI 4: Omzet H+90 Terverifikasi (khusus goal Sales yang terverifikasi)
        $verifiedRevenueEvents = (clone $portfolioEventsQuery)
            ->where('verification_status', VerificationStatus::TERVERIFIKASI)
            ->where('goal', Goal::SALES)
            ->with('salesUpdate')
            ->get();

        $verifiedRevenue = 0.0;
        foreach ($verifiedRevenueEvents as $evt) {
            if ($evt->salesUpdate && $evt->salesUpdate->revenue !== null) {
                $verifiedRevenue += (float) $evt->salesUpdate->revenue;
            }
        }

        // Analytics: Distribusi Goal
        $salesCount = (clone $portfolioEventsQuery)->where('goal', Goal::SALES)->count();
        $awarenessCount = (clone $portfolioEventsQuery)->where('goal', Goal::AWARENESS)->count();
        $relationshipCount = (clone $portfolioEventsQuery)->where('goal', Goal::RELATIONSHIP)->count();

        // Analytics: Kinerja Anggaran per Cabang
        $branchPerformance = (clone $portfolioEventsQuery)
            ->with(['branch', 'report'])
            ->get()
            ->groupBy('branch_id')
            ->map(function ($events) {
                $branchName = $events->first()->branch?->name ?? 'Cabang Lain';
                $plan = 0.0;
                $actual = 0.0;
                foreach ($events as $ev) {
                    $plan += ((float) $ev->proposal_cost + (float) $ev->team_budget);
                    if ($ev->report && $ev->report->actual_team_cost !== null) {
                        $actual += ((float) $ev->proposal_cost + (float) $ev->report->actual_team_cost);
                    }
                }
                return [
                    'branch_name' => $branchName,
                    'total_events' => $events->count(),
                    'plan_budget' => $plan,
                    'actual_cost' => $actual,
                ];
            })
            ->values();

        // Data Tabel Monitoring 10 Event Terbaru
        $tableEvents = (clone $portfolioEventsQuery)
            ->with(['branch', 'report', 'salesUpdate'])
            ->latest('event_date')
            ->take(10)
            ->get()
            ->map(function (Event $e) {
                $eval = EventEvaluator::evaluate($e);
                return [
                    'id' => $e->id,
                    'code' => $e->code,
                    'name' => $e->name,
                    'type_label' => $e->type->label(),
                    'event_date_formatted' => $e->event_date->format('d/m/Y'),
                    'branch_name' => $e->branch->name,
                    'goal' => $e->goal->value,
                    'goal_label' => $e->goal->label(),
                    'proposal_cost' => (float) $e->proposal_cost,
                    'team_budget' => (float) $e->team_budget,
                    'data_status' => $eval['data_status']['status'] ?? 'belum_lengkap',
                    'data_status_label' => $eval['data_status']['label'] ?? 'Belum Lengkap',
                    'verification_status' => $e->verification_status->value,
                    'verification_status_label' => $e->verification_status->label(),
                    'recommendation' => $eval['recommendation']['status'] ?? 'belum_final',
                    'recommendation_label' => $eval['recommendation']['label'] ?? 'Belum Final',
                    'director_decision' => $e->director_decision,
                ];
            });

        // 3. Daftar Pilihan Event untuk Dropdown (dengan live search)
        $eventOptions = (clone $portfolioEventsQuery)
            ->latest('event_date')
            ->get(['id', 'code', 'name', 'event_date', 'goal', 'verification_status'])
            ->map(fn (Event $e) => [
                'id' => $e->id,
                'code' => $e->code,
                'name' => $e->name,
                'event_date_formatted' => $e->event_date->format('d/m/Y'),
                'goal_label' => $e->goal->label(),
                'portfolio' => $e->portfolio->value,
                'is_verified' => $e->verification_status === VerificationStatus::TERVERIFIKASI,
            ]);

        // 4. Pilih Event Terpilih (Selected Event)
        $selectedEventId = $request->input('event_id');
        $selectedEvent = null;

        if ($selectedEventId) {
            $selectedEvent = (clone $portfolioEventsQuery)
                ->with(['branch', 'report', 'salesUpdate', 'surveyResponses' => fn ($q) => $q->where('counted', true)])
                ->find($selectedEventId);
        }

        // Jika tidak ada parameter event_id atau event tidak ditemukan dalam filter, default ke event terbaru
        if (!$selectedEvent && $eventOptions->isNotEmpty()) {
            $firstOptionId = $eventOptions->first()['id'];
            $selectedEvent = (clone $portfolioEventsQuery)
                ->with(['branch', 'report', 'salesUpdate', 'surveyResponses' => fn ($q) => $q->where('counted', true)])
                ->find($firstOptionId);
        }

        // 5. Jalankan Evaluator Murni pada Selected Event (Jika ada)
        $evaluation = null;
        if ($selectedEvent) {
            $evaluation = EventEvaluator::evaluate($selectedEvent);
        }

        return Inertia::render('Dashboard/Index', [
            'kpis' => [
                'total_events' => $totalEventsCount,
                'verified_events_count' => $verifiedEventsCount,
                'pending_verification_count' => $pendingVerificationCount,
                'correction_needed_count' => $correctionNeededCount,
                'total_budget' => $totalBudget,
                'verified_actual_cost' => $verifiedActualCost,
                'verified_revenue' => $verifiedRevenue,
            ],
            'analytics' => [
                'goal_distribution' => [
                    'sales' => $salesCount,
                    'awareness' => $awarenessCount,
                    'relationship' => $relationshipCount,
                ],
                'verification_distribution' => [
                    'verified' => $verifiedEventsCount,
                    'pending' => $pendingVerificationCount,
                    'correction' => $correctionNeededCount,
                ],
                'branch_performance' => $branchPerformance,
            ],
            'tableEvents' => $tableEvents,
            'selectedPortfolio' => $selectedPortfolio,
            'eventOptions' => $eventOptions,
            'selectedEventId' => $selectedEvent?->id,
            'selectedEvent' => $selectedEvent ? [
                'id' => $selectedEvent->id,
                'code' => $selectedEvent->code,
                'name' => $selectedEvent->name,
                'type_label' => $selectedEvent->type->label(),
                'event_date_formatted' => $selectedEvent->event_date->format('d/m/Y'),
                'branch_name' => $selectedEvent->branch->name,
                'location' => $selectedEvent->location,
                'pic_name' => $selectedEvent->pic_name,
                'goal' => $selectedEvent->goal->value,
                'goal_label' => $selectedEvent->goal->label(),
                'theme_product' => $selectedEvent->theme_product,
                'target_participants' => $selectedEvent->target_participants,
                'target_leads' => $selectedEvent->target_leads,
                'proposal_cost' => (float) $selectedEvent->proposal_cost,
                'team_budget' => (float) $selectedEvent->team_budget,
                'verification_status' => $selectedEvent->verification_status->value,
                'verification_status_label' => $selectedEvent->verification_status->label(),
                'verification_note' => $selectedEvent->verification_note,
                'director_decision' => $selectedEvent->director_decision,
                'director_note' => $selectedEvent->director_note,
                'decision_stale_flag' => $selectedEvent->decision_stale_flag,
                'can' => [
                    'recordDecision' => $user->can('recordDecision', $selectedEvent),
                    'verify' => $user->can('verify', $selectedEvent),
                ],
            ] : null,
            'evaluation' => $evaluation,
        ]);
    }

    /**
     * Print view optimized for A4 Landscape 1 page.
     */
    public function print(Request $request, Event $event): Response
    {
        $user = $request->user();

        if ($user->hasRole('pic') && (int) $user->branch_id !== (int) $event->branch_id) {
            abort(403, 'Akses terbatas untuk event cabang Anda.');
        }

        $event->load(['branch', 'report', 'salesUpdate', 'surveyResponses' => fn ($q) => $q->where('counted', true)]);
        $evaluation = EventEvaluator::evaluate($event);

        return Inertia::render('Dashboard/Print', [
            'event' => [
                'id' => $event->id,
                'code' => $event->code,
                'name' => $event->name,
                'type_label' => $event->type->label(),
                'event_date_formatted' => $event->event_date->format('d/m/Y'),
                'branch_name' => $event->branch->name,
                'location' => $event->location,
                'pic_name' => $event->pic_name,
                'goal_label' => $event->goal->label(),
                'target_participants' => $event->target_participants,
                'target_leads' => $event->target_leads,
                'proposal_cost' => (float) $event->proposal_cost,
                'team_budget' => (float) $event->team_budget,
                'verification_status' => $event->verification_status->value,
                'verification_status_label' => $event->verification_status->label(),
                'director_decision' => $event->director_decision,
                'director_note' => $event->director_note,
                'decision_stale_flag' => $event->decision_stale_flag,
            ],
            'evaluation' => $evaluation,
        ]);
    }
}
