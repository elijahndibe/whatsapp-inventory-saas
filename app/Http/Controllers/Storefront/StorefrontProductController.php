<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Product;
use App\Services\WhatsAppMessageFormatter;
use Illuminate\View\View;

class StorefrontProductController extends Controller
{
    public function show(Business $business, string $productSlug, WhatsAppMessageFormatter $formatter): View
    {
        abort_unless($business->isActive(), 404);

        // Resolved explicitly (not via implicit route-model-binding): product
        // slugs are only unique per business, not globally, so binding on
        // slug alone could match a different business's product.
        $product = Product::forBusiness($business->id)
            ->active()
            ->where('slug', $productSlug)
            ->with(['images', 'category'])
            ->firstOrFail();

        $whatsappUrl = $formatter->productInquiryUrl($product, $business);

        return view('storefront.product', compact('business', 'product', 'whatsappUrl'));
    }
}
