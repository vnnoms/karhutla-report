<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'coordinator';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $target): bool
    {
        return $user->role === 'coordinator';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $target): bool
    {
        return false;
    }

    public function updateRole(User $admin, User $target, ?string $role = null): bool
    {
        $canManageTarget = $admin->role === 'coordinator'
            && (int) $admin->getAuthIdentifier() !== (int) $target->getAuthIdentifier()
            && $target->role !== 'coordinator';

        return $canManageTarget
            && ($role === null || in_array($role, ['reporter', 'volunteer'], true));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $target): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $target): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $target): bool
    {
        return false;
    }
}
