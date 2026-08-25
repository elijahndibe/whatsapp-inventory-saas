<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'Total Businesses' => Business::count(),
            'Active Businesses' => Business::where('status', 'active')->count(),
            'Suspended Businesses' => Business::where('status', 'suspended')->count(),
            'Total Users' => User::count(),
            'Total Orders (Platform)' => Order::withoutGlobalScopes()->count(),
            'Paid Orders (Platform)' => Order::withoutGlobalScopes()->where('payment_status', 'paid')->count(),
        ];

        $orderRevenue = Payment::withoutGlobalScopes()->where('status', 'success')->sum('amount') / 100;
        $subscriptionRevenue = Subscription::whereNotNull('paid_at')->sum('amount_paid') / 100;

        $planCounts = Subscription::where('status', 'active')
            ->with('plan')
            ->get()
            ->groupBy(fn ($s) => $s->plan?->name ?? 'Unknown')
            ->map->count();

        $recentBusinesses = Business::latest('id')->limit(5)->get();
        $recentOrders = Order::withoutGlobalScopes()->with(['business', 'customer'])->latest('id')->limit(8)->get();

        return view('admin.dashboard', compact('stats', 'orderRevenue', 'subscriptionRevenue', 'planCounts', 'recentBusinesses', 'recentOrders'));
    }
}
