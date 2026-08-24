<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cart) {}

    public function index(Business $business): View
    {
        abort_unless($business->isActive(), 404);

        $items = $this->cart->detailed($business);

        return view('storefront.cart', [
            'business' => $business,
            'items' => $items,
            'subtotal' => $items->sum('subtotal'),
        ]);
    }

    public function store(Request $request, Business $business): RedirectResponse
    {
        abort_unless($business->isActive(), 404);

        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $product = Product::forBusiness($business->id)->active()->findOrFail($data['product_id']);

        if (! $business->allow_overselling && $product->stock_quantity < $data['quantity']) {
            return back()->with('error', "Only {$product->stock_quantity} of \"{$product->name}\" available.");
        }

        $this->cart->add($business->id, $product->id, $data['quantity']);

        return back()->with('status', "Added \"{$product->name}\" to cart.");
    }

    public function update(Request $request, Business $business, Product $product): RedirectResponse
    {
        abort_unless($business->isActive(), 404);
        abort_unless($product->business_id === $business->id, 404);

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:999'],
        ]);

        if ($data['quantity'] > 0 && ! $business->allow_overselling && $product->stock_quantity < $data['quantity']) {
            return back()->with('error', "Only {$product->stock_quantity} of \"{$product->name}\" available.");
        }

        $this->cart->update($business->id, $product->id, $data['quantity']);

        return back()->with('status', 'Cart updated.');
    }

    public function destroy(Business $business, Product $product): RedirectResponse
    {
        $this->cart->remove($business->id, $product->id);

        return back()->with('status', 'Item removed.');
    }
}
