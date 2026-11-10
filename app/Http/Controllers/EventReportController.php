<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventReportRequest;
use App\Mail\PostEventReportSubmittedMail;
use App\Models\Event;
use App\Models\EventReport;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class EventReportController extends Controller
{
    /**
     * Show the post-event report form for an event.
     */
    public function edit(Event $event): Response
    {
        Gate::authorize('updateReport', $event);

        $event->load(['branch', 'report']);

        return Inertia::render('Events/Report', [
            'event' => [
                'id' => $event->id,
                'code' => $event->code,
                'name' => $event->name,
                'type_label' => $event->type->label(),
                'event_date' => $event->event_date->format('Y-m-d'),
                'event_date_formatted' => $event->event_date->format('d/m/Y'),
                'branch' => [
                    'id' => $event->branch->id,
                    'name' => $event->branch->name,
                ],
                'pic_name' => $event->pic_name,
                'goal_label' => $event->goal->label(),
                'target_participants' => $event->target_participants,
                'target_leads' => $event->target_leads,
                'proposal_cost' => (float) $event->proposal_cost,
                'team_budget' => (float) $event->team_budget,
                'verification_status' => $event->verification_status->value,
                'verification_status_label' => $event->verification_status->label(),
            ],
            'report' => $event->report ? [
                'actual_participants' => $event->report->actual_participants,
                'valid_leads' => $event->report->valid_leads,
                'actual_team_cost' => $event->report->actual_team_cost !== null ? (float) $event->report->actual_team_cost : null,
                'notes' => $event->report->notes,
            ] : null,
        ]);
    }

    /**
     * Store or update post-event report (updateOrCreate).
     */
    public function update(StoreEventReportRequest $request, Event $event): RedirectResponse
    {
        $validated = $request->validated();

        $report = EventReport::updateOrCreate(
            ['event_id' => $event->id],
            [
                'actual_participants' => $validated['actual_participants'] ?? null,
                'valid_leads' => $validated['valid_leads'] ?? null,
                'actual_team_cost' => array_key_exists('actual_team_cost', $validated) ? $validated['actual_team_cost'] : null,
                'notes' => $validated['notes'] ?? null,
            ]
        );

        // Reset verification and mark decision stale if event was previously verified
        $event->resetVerificationIfModifiedAfterVerification();

        // Notify marketing users
        try {
            $marketingUsers = User::role('marketing')->get();
            foreach ($marketingUsers as $marketing) {
                Mail::to($marketing->email)->send(new PostEventReportSubmittedMail($event));
            }
        } catch (\Throwable $e) {
            report($e);
        }

        activity()
            ->causedBy($request->user())
            ->performedOn($event)
            ->withProperties(['report_id' => $report->id])
            ->log("Mengisi laporan pasca-event: {$event->code}");

        return redirect()->route('events.index')->with('success', "Laporan pasca-event [{$event->code}] berhasil disimpan.");
    }
}
