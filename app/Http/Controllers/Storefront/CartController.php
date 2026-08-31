<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Product;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\FeatureService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly CouponService $coupons,
        private readonly FeatureService $features,
    ) {}

    public function index(Business $business): View
    {
        abort_unless($business->isActive(), 404);

        $items = $this->cart->detailed($business);
        $subtotal = (float) $items->sum('subtotal');

        return view('storefront.cart', [
            'business' => $business,
            'items' => $items,
            'subtotal' => $subtotal,
            'couponsEnabled' => $this->features->enabled($business, 'coupons'),
            ...$this->couponViewData($business, $subtotal),
        ]);
    }

    public function applyCoupon(Request $request, Business $business): RedirectResponse
    {
        abort_unless($business->isActive(), 404);

        $code = (string) $request->validate(['code' => ['required', 'string', 'max:50']])['code'];
        $subtotal = $this->cart->subtotal($business);

        $result = $this->coupons->validate($business, $code, $subtotal);

        if ($result['error']) {
            return back()->with('error', $result['error']);
        }

        $this->cart->applyCoupon($business->id, $code);

        return back()->with('status', 'Coupon applied.');
    }

    public function removeCoupon(Business $business): RedirectResponse
    {
        $this->cart->removeCoupon($business->id);

        return back()->with('status', 'Coupon removed.');
    }

    /**
     * @return array{appliedCouponCode: ?string, couponDiscount: float, couponError: ?string}
     */
    private function couponViewData(Business $business, float $subtotal): array
    {
        $code = $this->cart->appliedCouponCode($business->id);

        if (! $code) {
            return ['appliedCouponCode' => null, 'couponDiscount' => 0.0, 'couponError' => null];
        }

        $result = $this->coupons->validate($business, $code, $subtotal);

        return [
            'appliedCouponCode' => $code,
            'couponDiscount' => $result['discount'],
            'couponError' => $result['error'],
        ];
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
