<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view customers');
    }

    public function view(User $user, Customer $customer): bool
    {
        return $user->can('view customers') && $this->sameBusiness($user, $customer);
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->can('manage customers') && $this->sameBusiness($user, $customer);
    }

    private function sameBusiness(User $user, Customer $customer): bool
    {
        return $user->business_id === $customer->business_id;
    }
}
