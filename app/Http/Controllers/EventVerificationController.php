<?php

namespace App\Http\Controllers;

use App\Enums\EventPortfolio;
use App\Enums\VerificationStatus;
use App\Http\Requests\UpdateVerificationRequest;
use App\Mail\EventCorrectionRequiredMail;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class EventVerificationController extends Controller
{
    /**
     * Update the verification status of an event.
     */
    public function update(UpdateVerificationRequest $request, Event $event): RedirectResponse
    {
        $targetStatus = $request->input('verification_status');

        // CRITICAL GUARD: Never verify Demo or UAT as real operational events
        if ($targetStatus === VerificationStatus::TERVERIFIKASI->value && $event->portfolio !== EventPortfolio::OPERATIONAL) {
            throw ValidationException::withMessages([
                'verification_status' => 'Event dengan portfolio Demo atau UAT tidak dapat diverifikasi sebagai event operasional.',
            ]);
        }

        $oldStatus = $event->verification_status;
        $event->verification_status = VerificationStatus::from($targetStatus);
        $event->verification_note = $request->input('verification_note');
        $event->save();

        // Send in-app notification to PIC
        if ($event->picUser) {
            $isCorrection = $event->verification_status === VerificationStatus::PERLU_KOREKSI;
            $event->picUser->notify(new \App\Notifications\EventActivityNotification(
                title: 'Status Verifikasi Event: ' . $event->verification_status->label(),
                message: "Event [{$event->code}] {$event->name} berstatus {$event->verification_status->label()}." . ($event->verification_note ? " Catatan: {$event->verification_note}" : ''),
                url: route('events.index', ['search' => $event->code]),
                type: $isCorrection ? 'warning' : 'success'
            ));
        }

        // Send correction required email to PIC if applicable
        if ($event->verification_status === VerificationStatus::PERLU_KOREKSI) {
            $email = $event->submitter_email ?? $event->picUser?->email;
            if ($email) {
                try {
                    Mail::to($email)->send(new EventCorrectionRequiredMail($event, $event->verification_note ?? 'Mohon periksa dan perbaiki kelengkapan data event.'));
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        activity()
            ->causedBy($request->user())
            ->performedOn($event)
            ->withProperties([
                'old_status' => $oldStatus->value,
                'new_status' => $event->verification_status->value,
                'note' => $event->verification_note,
            ])
            ->log("Mengubah status verifikasi event [{$event->code}] menjadi {$event->verification_status->label()}");

        return back()->with('success', "Status verifikasi event [{$event->code}] berhasil diperbarui.");
    }
}
