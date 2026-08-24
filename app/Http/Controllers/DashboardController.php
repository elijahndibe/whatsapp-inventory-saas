<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
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

        return view('dashboard', [
            'business' => $business,
            'stats' => $stats,
        ]);
    }
}
