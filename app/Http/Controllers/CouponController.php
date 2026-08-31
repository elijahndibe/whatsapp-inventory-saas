<?php

namespace App\Http\Controllers;

use App\Http\Requests\Coupon\StoreCouponRequest;
use App\Http\Requests\Coupon\UpdateCouponRequest;
use App\Models\Coupon;
use App\Services\FeatureService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function __construct(private readonly FeatureService $features) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Coupon::class);

        $coupons = Coupon::when($request->string('search')->toString(), fn ($q, $search) => $q->where('code', 'like', '%'.strtoupper($search).'%'))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('coupons.index', [
            'coupons' => $coupons,
            'couponsEnabled' => $this->features->enabled($request->user()->business, 'coupons'),
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $this->authorize('create', Coupon::class);
        $this->guardFeatureEnabled($request);

        return view('coupons.create');
    }

    public function store(StoreCouponRequest $request): RedirectResponse
    {
        $this->guardFeatureEnabled($request);

        Coupon::create($request->validated() + ['is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('coupons.index')->with('status', 'Coupon created.');
    }

    public function edit(Coupon $coupon): View
    {
        $this->authorize('update', $coupon);

        return view('coupons.edit', compact('coupon'));
    }

    public function update(UpdateCouponRequest $request, Coupon $coupon): RedirectResponse
    {
        $coupon->update($request->validated() + ['is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('coupons.index')->with('status', 'Coupon updated.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $this->authorize('delete', $coupon);

        $coupon->delete();

        return redirect()->route('coupons.index')->with('status', 'Coupon deleted.');
    }

    /**
     * Existing coupons stay manageable (index/edit/destroy) even if the
     * feature is later disabled for this business's plan — only creating
     * new ones is blocked, so nothing is stranded unmanageable.
     */
    private function guardFeatureEnabled(Request $request): void
    {
        abort_unless(
            $this->features->enabled($request->user()->business, 'coupons'),
            403,
            'Coupon codes are not available on your current plan.'
        );
    }
}
