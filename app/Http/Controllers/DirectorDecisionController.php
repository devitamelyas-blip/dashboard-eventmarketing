<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateDirectorDecisionRequest;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;

class DirectorDecisionController extends Controller
{
    /**
     * Update the director decision for an event.
     */
    public function update(UpdateDirectorDecisionRequest $request, Event $event): RedirectResponse
    {
        $oldDecision = $event->director_decision;

        $event->director_decision = $request->input('director_decision');
        $event->director_note = $request->input('director_note');
        $event->decision_stale_flag = false;
        $event->save();

        activity()
            ->causedBy($request->user())
            ->performedOn($event)
            ->withProperties([
                'old_decision' => $oldDecision,
                'new_decision' => $event->director_decision,
                'note' => $event->director_note,
            ])
            ->log("Mencatat keputusan Direksi untuk event [{$event->code}]: " . strtoupper($event->director_decision));

        return back()->with('success', "Keputusan Direksi untuk event [{$event->code}] berhasil disimpan.");
    }
}
