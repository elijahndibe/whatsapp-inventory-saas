<?php

namespace App\Policies;

use App\Models\InventoryTransaction;
use App\Models\User;

class InventoryTransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view inventory');
    }

    public function view(User $user, InventoryTransaction $transaction): bool
    {
        return $user->can('view inventory') && $user->business_id === $transaction->business_id;
    }
}
