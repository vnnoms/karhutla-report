<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'reporter';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Report $report): bool
    {
        return $user->role === 'reporter'
            && (int) $report->reporter_id === (int) $user->getAuthIdentifier();
    }

    public function viewAsVolunteer(User $user, Report $report): bool
    {
        return $user->role === 'volunteer';
    }

    public function reviewAsVolunteer(User $user, Report $report): bool
    {
        return $user->role === 'volunteer' && $report->status === 'pending';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === 'reporter';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Report $report): bool
    {
        return $this->view($user, $report) && $report->status === 'pending';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Report $report): bool
    {
        return $this->view($user, $report)
            && $report->status === 'pending'
            && ! $report->reviews()->exists()
            && ! $report->forwardings()->exists();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Report $report): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Report $report): bool
    {
        return false;
    }
}
