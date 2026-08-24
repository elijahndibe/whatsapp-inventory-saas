<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view products');
    }

    public function view(User $user, Product $product): bool
    {
        return $user->can('view products') && $this->sameBusiness($user, $product);
    }

    public function create(User $user): bool
    {
        return $user->can('create products');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->can('edit products') && $this->sameBusiness($user, $product);
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->can('delete products') && $this->sameBusiness($user, $product);
    }

    public function adjustStock(User $user, Product $product): bool
    {
        return $user->can('adjust inventory') && $this->sameBusiness($user, $product);
    }

    /**
     * Belt-and-braces check: even though BusinessScope already prevents a
     * product from another business being loaded in the first place, a
     * policy that only checked the permission name would silently allow
     * cross-tenant access if that scope were ever bypassed by mistake.
     */
    private function sameBusiness(User $user, Product $product): bool
    {
        return $user->business_id === $product->business_id;
    }
}
