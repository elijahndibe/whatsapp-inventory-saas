<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view orders');
    }

    public function view(User $user, Order $order): bool
    {
        return $user->can('view orders') && $this->sameBusiness($user, $order);
    }

    public function update(User $user, Order $order): bool
    {
        return $user->can('update orders') && $this->sameBusiness($user, $order);
    }

    private function sameBusiness(User $user, Order $order): bool
    {
        return $user->business_id === $order->business_id;
    }
}
