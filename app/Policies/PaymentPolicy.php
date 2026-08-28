<?php

namespace App\Policies;

use App\Models\User;

/**
 * Rides on the existing "view orders" permission rather than introducing
 * a new one — a payment record only ever exists tied to an order, so
 * anyone who can see orders can see how they were paid.
 */
class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view orders');
    }
}
