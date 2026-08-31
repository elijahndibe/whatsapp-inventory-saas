<?php

namespace App\Policies;

use App\Models\Coupon;
use App\Models\User;

class CouponPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage coupons');
    }

    public function view(User $user, Coupon $coupon): bool
    {
        return $user->can('manage coupons') && $this->sameBusiness($user, $coupon);
    }

    public function create(User $user): bool
    {
        return $user->can('manage coupons');
    }

    public function update(User $user, Coupon $coupon): bool
    {
        return $user->can('manage coupons') && $this->sameBusiness($user, $coupon);
    }

    public function delete(User $user, Coupon $coupon): bool
    {
        return $user->can('manage coupons') && $this->sameBusiness($user, $coupon);
    }

    private function sameBusiness(User $user, Coupon $coupon): bool
    {
        return $user->business_id === $coupon->business_id;
    }
}
