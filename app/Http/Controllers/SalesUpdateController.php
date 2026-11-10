<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSalesUpdateRequest;
use App\Mail\SalesUpdateSubmittedMail;
use App\Models\Event;
use App\Models\SalesUpdate;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesUpdateController extends Controller
{
    /**
     * Show the H+90 sales update form.
     */
    public function edit(Event $event): Response
    {
        Gate::authorize('updateSales', $event);

        $event->load(['branch', 'salesUpdate', 'report']);

        return Inertia::render('Events/SalesUpdate', [
            'event' => [
                'id' => $event->id,
                'code' => $event->code,
                'name' => $event->name,
                'type_label' => $event->type->label(),
                'event_date' => $event->event_date->format('Y-m-d'),
                'event_date_formatted' => $event->event_date->format('d/m/Y'),
                'h90_date' => $event->event_date->copy()->addDays(90)->format('d/m/Y'),
                'branch' => [
                    'id' => $event->branch->id,
                    'name' => $event->branch->name,
                ],
                'pic_name' => $event->pic_name,
                'goal_label' => $event->goal->label(),
                'proposal_cost' => (float) $event->proposal_cost,
                'team_budget' => (float) $event->team_budget,
                'verification_status' => $event->verification_status->value,
                'verification_status_label' => $event->verification_status->label(),
            ],
            'salesUpdate' => $event->salesUpdate ? [
                'revenue' => $event->salesUpdate->revenue !== null ? (float) $event->salesUpdate->revenue : null,
                'invoice_path' => $event->salesUpdate->invoice_path ? true : false,
                'notes' => $event->salesUpdate->notes,
            ] : null,
        ]);
    }

    /**
     * Store or update H+90 sales update (updateOrCreate).
     */
    public function update(StoreSalesUpdateRequest $request, Event $event): RedirectResponse
    {
        $validated = $request->validated();
        $salesUpdate = $event->salesUpdate;

        $invoicePath = $salesUpdate?->invoice_path;

        if ($request->hasFile('invoice')) {
            // Delete old file if present
            if ($invoicePath && Storage::disk('private')->exists($invoicePath)) {
                Storage::disk('private')->delete($invoicePath);
            }
            $invoicePath = $request->file('invoice')->store('invoices', 'private');
        }

        $savedSales = SalesUpdate::updateOrCreate(
            ['event_id' => $event->id],
            [
                'revenue' => array_key_exists('revenue', $validated) ? $validated['revenue'] : null,
                'invoice_path' => $invoicePath,
                'notes' => $validated['notes'] ?? null,
            ]
        );

        // Reset verification and mark decision stale if event was previously verified
        $event->resetVerificationIfModifiedAfterVerification();

        // Notify marketing users
        try {
            $marketingUsers = User::role('marketing')->get();
            foreach ($marketingUsers as $marketing) {
                Mail::to($marketing->email)->send(new SalesUpdateSubmittedMail($event));
            }
        } catch (\Throwable $e) {
            report($e);
        }

        activity()
            ->causedBy($request->user())
            ->performedOn($event)
            ->withProperties(['sales_update_id' => $savedSales->id])
            ->log("Mengisi pembaruan omzet H+90: {$event->code}");

        return redirect()->route('events.index')->with('success', "Pembaruan omzet H+90 untuk [{$event->code}] berhasil disimpan.");
    }

    /**
     * Download or view uploaded invoice proof from private disk.
     */
    public function downloadInvoice(Event $event): StreamedResponse
    {
        Gate::authorize('viewInvoice', $event);

        $salesUpdate = $event->salesUpdate;

        if (!$salesUpdate || !$salesUpdate->invoice_path || !Storage::disk('private')->exists($salesUpdate->invoice_path)) {
            abort(404, 'Bukti invoice tidak ditemukan pada penyimpanan privat.');
        }

        return Storage::disk('private')->response($salesUpdate->invoice_path);
    }
}
