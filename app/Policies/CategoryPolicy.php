<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

/**
 * Categories are organizational metadata for products, so they ride on
 * the same "products" permissions rather than having their own — there
 * is no separate "manage categories" permission in the spec's list.
 */
class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view products');
    }

    public function view(User $user, Category $category): bool
    {
        return $user->can('view products') && $this->sameBusiness($user, $category);
    }

    public function create(User $user): bool
    {
        return $user->can('create products');
    }

    public function update(User $user, Category $category): bool
    {
        return $user->can('edit products') && $this->sameBusiness($user, $category);
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->can('delete products') && $this->sameBusiness($user, $category);
    }

    private function sameBusiness(User $user, Category $category): bool
    {
        return $user->business_id === $category->business_id;
    }
}
