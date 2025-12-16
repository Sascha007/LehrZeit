<?php

namespace App\Policies;

use App\Models\BillingPeriod;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BillingPeriodPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view billing periods
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BillingPeriod $billingPeriod): bool
    {
        // Admins can view all, lecturers can view their own
        return $user->isAdmin() || $billingPeriod->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isLecturer(); // Only lecturers can create billing periods
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BillingPeriod $billingPeriod): bool
    {
        // Only owner and period must be OPEN
        return $billingPeriod->user_id === $user->id && $billingPeriod->isEditable();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BillingPeriod $billingPeriod): bool
    {
        // Can only delete if OPEN and is owner
        return $billingPeriod->user_id === $user->id && $billingPeriod->status === 'OPEN';
    }

    /**
     * Determine whether the user can submit the billing period.
     */
    public function submit(User $user, BillingPeriod $billingPeriod): bool
    {
        return $billingPeriod->user_id === $user->id && $billingPeriod->canBeSubmitted();
    }

    /**
     * Determine whether the user can approve the billing period.
     */
    public function approve(User $user, BillingPeriod $billingPeriod): bool
    {
        return $user->isAdmin() && $billingPeriod->canBeApproved();
    }

    /**
     * Determine whether the user can reopen the billing period.
     */
    public function reopen(User $user, BillingPeriod $billingPeriod): bool
    {
        return $user->isAdmin() && $billingPeriod->canBeReopened();
    }

    /**
     * Determine whether the user can export the billing period.
     */
    public function export(User $user, BillingPeriod $billingPeriod): bool
    {
        return $user->isAdmin() && $billingPeriod->status === 'APPROVED';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, BillingPeriod $billingPeriod): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, BillingPeriod $billingPeriod): bool
    {
        return false;
    }
}
