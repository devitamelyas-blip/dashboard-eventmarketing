<?php

namespace App\Policies;

use App\Models\Respondent;
use App\Models\User;

class RespondentPolicy
{
    /**
     * Determine whether the user can view any respondents (marketing and admin only).
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['marketing', 'admin']);
    }

    /**
     * Determine whether the user can view the respondent details.
     */
    public function view(User $user, Respondent $respondent): bool
    {
        return $user->hasAnyRole(['marketing', 'admin']);
    }

    /**
     * Determine whether the user can delete the respondent (admin only).
     */
    public function delete(User $user, Respondent $respondent): bool
    {
        return $user->hasRole('admin');
    }
}
