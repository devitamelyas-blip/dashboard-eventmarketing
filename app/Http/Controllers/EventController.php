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

class EventController extends Controller
{
    /**
     * Display a listing of the events (Sheet 01 equivalent).
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Event::class);

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
                $query->where('code', 'like', 'DEVON-UAT-%');
            } elseif ($portfolio === EventPortfolio::OPERATIONAL->value) {
                $query->where('code', 'not like', 'DEMO-%')
                      ->where('code', 'not like', 'DEVON-UAT-%');
            }
        }

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
