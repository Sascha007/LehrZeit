<?php

namespace App\Policies;

use App\Models\TeachingSession;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TeachingSessionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TeachingSession $teachingSession): bool
    {
        return $user->isAdmin() || $teachingSession->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isLecturer();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TeachingSession $teachingSession): bool
    {
        // Only owner can update and billing period must be OPEN
        return $teachingSession->user_id === $user->id 
            && $teachingSession->billingPeriod->isEditable();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TeachingSession $teachingSession): bool
    {
        // Only owner can delete and billing period must be OPEN
        return $teachingSession->user_id === $user->id 
            && $teachingSession->billingPeriod->isEditable();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TeachingSession $teachingSession): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TeachingSession $teachingSession): bool
    {
        return false;
    }
}
