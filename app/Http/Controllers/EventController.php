<?php

namespace App\Http\Controllers;

use App\Enums\EventPortfolio;
use App\Enums\EventType;
use App\Enums\Goal;
use App\Enums\VerificationStatus;
use App\Http\Requests\StoreEventRequest;
use App\Mail\NewEventSubmittedMail;
use App\Models\Branch;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventController extends Controller
{
    /**
     * Build base query for events list and export with all filters applied.
     */
    protected function buildEventsQuery(Request $request)
    {
        $user = $request->user();
        $query = Event::with(['branch', 'picUser', 'report', 'salesUpdate']);

        // Scope to user branch if PIC
        if ($user->hasRole('pic')) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }

        // Search by code or name
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('pic_name', 'like', "%{$search}%");
            });
        }

        // Filter by Goal
        if ($request->filled('goal')) {
            $query->where('goal', $request->input('goal'));
        }

        // Filter by Verification Status
        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->input('verification_status'));
        }

        // Filter by Portfolio
        if ($request->filled('portfolio')) {
            $portfolio = $request->input('portfolio');
            if ($portfolio === EventPortfolio::DEMO->value) {
                $query->where('code', 'like', 'DEMO-%');
            } elseif ($portfolio === EventPortfolio::UAT->value) {
                $query->where('code', 'like', 'UAT-%');
            } elseif ($portfolio === EventPortfolio::OPERATIONAL->value) {
                $query->where('code', 'not like', 'DEMO-%')
                      ->where('code', 'not like', 'UAT-%');
            }
        }

        return $query;
    }

    /**
     * Display a listing of the events.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Event::class);

        $user = $request->user();
        $query = $this->buildEventsQuery($request);

        $events = $query->latest('event_date')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Event $event) => [
                'id' => $event->id,
                'code' => $event->code,
                'name' => $event->name,
                'type' => $event->type->value,
                'type_label' => $event->type->label(),
                'event_date' => $event->event_date->format('Y-m-d'),
                'event_date_formatted' => $event->event_date->format('d/m/Y'),
                'branch' => [
                    'id' => $event->branch->id,
                    'name' => $event->branch->name,
                    'code' => $event->branch->code,
                ],
                'location' => $event->location,
                'pic_name' => $event->pic_name,
                'goal' => $event->goal->value,
                'goal_label' => $event->goal->label(),
                'portfolio' => $event->portfolio->value,
                'portfolio_label' => $event->portfolio->label(),
                'proposal_cost' => (float) $event->proposal_cost,
                'team_budget' => (float) $event->team_budget,
                'verification_status' => $event->verification_status->value,
                'verification_status_label' => $event->verification_status->label(),
                'verification_note' => $event->verification_note,
                'director_decision' => $event->director_decision,
                'director_note' => $event->director_note,
                'decision_stale_flag' => $event->decision_stale_flag,
                'survey_token' => $event->survey_token,
                'is_eligible_for_report' => $event->isEligibleForReport(),
                'is_eligible_for_sales' => $event->isEligibleForSalesUpdate(),
                'days_until_sales' => $event->daysUntilSalesUpdate(),
                'has_report' => $event->report !== null,
                'has_sales' => $event->salesUpdate !== null,
                'has_invoice' => $event->salesUpdate?->invoice_path !== null,
                'can' => [
                    'view' => $user->can('view', $event),
                    'update' => $user->can('update', $event),
                    'updateReport' => $user->can('updateReport', $event),
                    'updateSales' => $user->can('updateSales', $event),
                    'viewInvoice' => $user->can('viewInvoice', $event),
                    'verify' => $user->can('verify', $event),
                    'recordDecision' => $user->can('recordDecision', $event),
                ],
            ]);

        $branches = Branch::orderBy('name')->get(['id', 'name', 'code']);

        return Inertia::render('Events/Index', [
            'events' => $events,
            'branches' => $branches,
            'filters' => $request->only(['search', 'branch_id', 'goal', 'verification_status', 'portfolio']),
            'goals' => array_map(fn ($g) => ['value' => $g->value, 'label' => $g->label()], Goal::cases()),
            'verificationStatuses' => array_map(fn ($v) => ['value' => $v->value, 'label' => $v->label()], VerificationStatus::cases()),
            'portfolios' => array_map(fn ($p) => ['value' => $p->value, 'label' => $p->label()], EventPortfolio::cases()),
        ]);
    }

    /**
     * Export events list as CSV compatible with Excel.
     */
    public function export(Request $request): StreamedResponse
    {
        Gate::authorize('viewAny', Event::class);

        $query = $this->buildEventsQuery($request);
        $events = $query->latest('event_date')->get();

        $filename = 'rekap_event_marketing_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($events) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Microsoft Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header columns
            fputcsv($handle, [
                'Kode Event',
                'Nama Event',
                'Jenis Event',
                'Tanggal Event',
                'Cabang',
                'Lokasi',
                'PIC',
                'Email PIC',
                'Sasaran Utama (Goal)',
                'Portofolio',
                'Tema / Produk',
                'Target Peserta',
                'Target Lead',
                'Biaya Proposal (Rp)',
                'Budget Tim (Rp)',
                'Total Rencana Anggaran (Rp)',
                'Biaya Aktual Tim (Rp)',
                'Total Biaya Aktual (Rp)',
                'Peserta Aktual',
                'Lead Valid',
                'Omzet H+90 (Rp)',
                'Status Verifikasi',
                'Catatan Verifikasi',
                'Keputusan Direksi',
                'Catatan Direksi',
            ]);

            foreach ($events as $event) {
                $actualTeamCost = $event->report?->actual_team_cost !== null ? (float) $event->report->actual_team_cost : null;
                $totalActualCost = $actualTeamCost !== null ? ((float) $event->proposal_cost + $actualTeamCost) : null;
                $totalBudget = (float) $event->proposal_cost + (float) $event->team_budget;
                $revenue = $event->salesUpdate?->revenue !== null ? (float) $event->salesUpdate->revenue : null;

                fputcsv($handle, [
                    $event->code,
                    $event->name,
                    $event->type->label(),
                    $event->event_date->format('d/m/Y'),
                    $event->branch?->name ?? '-',
                    $event->location,
                    $event->pic_name,
                    $event->submitter_email ?? $event->picUser?->email ?? '-',
                    $event->goal->label(),
                    $event->portfolio->label(),
                    $event->theme_product ?? '-',
                    $event->target_participants,
                    $event->target_leads,
                    $event->proposal_cost,
                    $event->team_budget,
                    $totalBudget,
                    $actualTeamCost ?? '',
                    $totalActualCost ?? '',
                    $event->report?->actual_participants ?? '',
                    $event->report?->valid_leads ?? '',
                    $revenue ?? '',
                    $event->verification_status->label(),
                    $event->verification_note ?? '',
                    $event->director_decision ? ucfirst($event->director_decision) : 'Belum Diputuskan',
                    $event->director_note ?? '',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Show the form for creating a new event.
     */
    public function create(Request $request): Response
    {
        Gate::authorize('create', Event::class);

        $user = $request->user();
        $branches = Branch::orderBy('name')->get(['id', 'name', 'code']);

        return Inertia::render('Events/Create', [
            'branches' => $branches,
            'userBranchId' => $user->branch_id,
            'isPic' => $user->hasRole('pic'),
            'userName' => $user->name,
            'eventTypes' => array_map(fn ($t) => ['value' => $t->value, 'label' => $t->label()], EventType::cases()),
            'goals' => array_map(fn ($g) => ['value' => $g->value, 'label' => $g->label()], Goal::cases()),
        ]);
    }

    /**
     * Store a newly created event in storage.
     */
    public function store(StoreEventRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $event = Event::create([
            ...$validated,
            'pic_user_id' => $user->id,
            'submitter_email' => $user->email,
            'verification_status' => VerificationStatus::BELUM_DIPERIKSA,
            'survey_token' => Str::random(40),
            'decision_stale_flag' => false,
        ]);

        // Send email notification to marketing users
        try {
            $marketingUsers = User::role('marketing')->get();
            foreach ($marketingUsers as $marketing) {
                Mail::to($marketing->email)->send(new NewEventSubmittedMail($event));
            }
        } catch (\Throwable $e) {
            // Silently log or continue if mail server is temporarily unreachable in dev
            report($e);
        }

        return redirect()->route('events.index')->with('success', "Event [{$event->code}] {$event->name} berhasil diajukan dan menunggu verifikasi Marketing.");
    }
}
