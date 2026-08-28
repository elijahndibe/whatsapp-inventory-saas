<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * A super admin has no business_id and belongs on the platform admin
     * panel, not this business-scoped dashboard — but every post-login/
     * post-verification redirect in the Breeze scaffolding points here
     * unconditionally (route('dashboard')). Centralizing the redirect
     * here, rather than special-casing every one of those call sites,
     * means there's exactly one place this ever needs to be handled.
     */
    public function __invoke(Request $request): View|RedirectResponse
    {
        if ($request->user()->isSuperAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $business = $request->user()->business;

        $stats = [
            "Today's Sales" => Order::whereDate('created_at', today())->sum('total') / 100,
            'Pending Orders' => Order::where('order_status', 'pending')->count(),
            'Completed Orders' => Order::where('order_status', 'completed')->count(),
            'Total Orders' => Order::count(),
            'Low Stock' => Product::lowStock()->count(),
            'Out of Stock' => Product::outOfStock()->count(),
            'Total Customers' => Customer::count(),
        ];

        // Transparency panel: the seller should never be surprised by what
        // commission deducted from their sales — sums are business-scoped
        // automatically via Payment's BelongsToTenant trait.
        $successfulPayments = Payment::where('status', 'success');
        $earnings = [
            'total_sales' => (clone $successfulPayments)->sum('amount') / 100,
            'platform_fees' => (clone $successfulPayments)->sum('commission_amount') / 100,
            'net_sales' => (clone $successfulPayments)->sum('seller_amount') / 100,
        ];

        $recentOrders = Order::with('customer')->latest('id')->limit(5)->get();
        $lowStockProducts = Product::where(fn ($q) => $q->lowStock()->orWhere(fn ($q) => $q->outOfStock()))
            ->orderBy('stock_quantity')
            ->limit(5)
            ->get();

        return view('dashboard', [
            'business' => $business,
            'stats' => $stats,
            'earnings' => $earnings,
            'recentOrders' => $recentOrders,
            'lowStockProducts' => $lowStockProducts,
        ]);
    }
}
