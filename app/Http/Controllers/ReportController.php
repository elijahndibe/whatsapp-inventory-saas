<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\FeatureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(private readonly FeatureService $features) {}

    public function index(Request $request): View
    {
        $this->authorize('view reports');

        $business = $request->user()->business;
        $days = 30;
        $since = now()->subDays($days - 1)->startOfDay();

        // Order/OrderItem money columns are stored as integer minor units;
        // these are raw SQL aggregates, not Eloquent attribute reads, so
        // they bypass the model's money accessor and need /100 by hand.

        $salesByDay = Order::where('payment_status', 'paid')
            ->where('created_at', '>=', $since)
            ->selectRaw('DATE(created_at) as date, SUM(total) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $ordersByDay = Order::where('created_at', '>=', $since)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $timeline = collect(range(0, $days - 1))->map(function (int $offset) use ($days, $salesByDay, $ordersByDay) {
            $date = now()->subDays($days - 1 - $offset)->format('Y-m-d');

            return [
                'date' => $date,
                'sales' => (float) ($salesByDay[$date]->total ?? 0) / 100,
                'orders' => (int) ($ordersByDay[$date]->count ?? 0),
            ];
        });

        $bestSellers = OrderItem::join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.business_id', $business->id)
            ->selectRaw('order_items.product_name, SUM(order_items.quantity) as units_sold, SUM(order_items.subtotal) as revenue')
            ->groupBy('order_items.product_name')
            ->orderByDesc('units_sold')
            ->limit(10)
            ->get()
            ->map(fn ($row) => ['name' => $row->product_name, 'units' => (int) $row->units_sold, 'revenue' => $row->revenue / 100]);

        $canUseAdvanced = $this->features->enabled($business, 'advanced_analytics');

        $salesByCategory = collect();
        $paymentMethods = collect();

        if ($canUseAdvanced) {
            $salesByCategory = OrderItem::join('orders', 'orders.id', '=', 'order_items.order_id')
                ->join('products', 'products.id', '=', 'order_items.product_id')
                ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
                ->where('orders.business_id', $business->id)
                ->selectRaw("COALESCE(categories.name, 'Uncategorized') as category, SUM(order_items.subtotal) as revenue")
                ->groupBy('category')
                ->orderByDesc('revenue')
                ->get()
                ->map(fn ($row) => ['category' => $row->category, 'revenue' => $row->revenue / 100]);

            $paymentMethods = Order::where('business_id', $business->id)
                ->whereNotNull('payment_method')
                ->selectRaw('payment_method, COUNT(*) as count')
                ->groupBy('payment_method')
                ->get()
                ->map(fn ($row) => ['method' => ucfirst($row->payment_method), 'count' => (int) $row->count]);
        }

        return view('reports.index', compact('timeline', 'bestSellers', 'salesByCategory', 'paymentMethods', 'canUseAdvanced'));
    }
}
