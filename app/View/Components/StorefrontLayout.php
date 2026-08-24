<?php

namespace App\View\Components;

use App\Models\Business;
use App\Services\CartService;
use Illuminate\View\Component;
use Illuminate\View\View;

class StorefrontLayout extends Component
{
    public int $cartCount;

    public function __construct(public Business $business)
    {
        $this->cartCount = app(CartService::class)->count($business->id);
    }

    public function render(): View
    {
        return view('storefront.layout');
    }
}
