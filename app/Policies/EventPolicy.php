<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    /**
     * Determine whether the user can view any events.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'direksi', 'marketing', 'pic']);
    }

    /**
     * Determine whether the user can view the specific event.
     */
    public function view(User $user, Event $event): bool
    {
        if ($user->hasAnyRole(['admin', 'direksi', 'marketing'])) {
            return true;
        }

        if ($user->hasRole('pic')) {
            return (int) $user->branch_id === (int) $event->branch_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create events.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'pic']);
    }

    /**
     * Determine whether the user can update the event basic info.
     */
    public function update(User $user, Event $event): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('pic')) {
            return (int) $user->branch_id === (int) $event->branch_id;
        }

        return false;
    }

    /**
     * Determine whether the user can submit/update the post-event report.
     */
    public function updateReport(User $user, Event $event): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('pic') && (int) $user->branch_id === (int) $event->branch_id) {
            return $event->isEligibleForReport();
        }

        return false;
    }

    /**
     * Determine whether the user can submit/update H+90 sales update.
     */
    public function updateSales(User $user, Event $event): bool
    {
        if ($event->goal !== \App\Enums\Goal::SALES) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('pic') && (int) $user->branch_id === (int) $event->branch_id) {
            return $event->isEligibleForSalesUpdate();
        }

        return false;
    }

    /**
     * Determine whether the user can view/download uploaded invoice proof.
     */
    public function viewInvoice(User $user, Event $event): bool
    {
        if ($user->hasAnyRole(['admin', 'marketing', 'direksi'])) {
            return true;
        }

        if ($user->hasRole('pic')) {
            return (int) $user->branch_id === (int) $event->branch_id;
        }

        return false;
    }

    /**
     * Determine whether the user can verify the event.
     */
    public function verify(User $user, Event $event): bool
    {
        return $user->hasPermissionTo('verify-event');
    }

    /**
     * Determine whether the user can record director decision.
     */
    public function recordDecision(User $user, Event $event): bool
    {
        return $user->hasPermissionTo('record-director-decision');
    }

    /**
     * Determine whether the user can delete the event.
     */
    public function delete(User $user, Event $event): bool
    {
        return $user->hasRole('admin');
    }
}
