<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Http\Requests\Product\AdjustStockRequest;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function __construct(private readonly InventoryService $inventory) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $lowStock = Product::lowStock()->orderBy('stock_quantity')->get();
        $outOfStock = Product::outOfStock()->orderBy('name')->get();

        return view('inventory.index', compact('lowStock', 'outOfStock'));
    }

    public function history(Product $product): View
    {
        $this->authorize('view', $product);

        $transactions = $product->inventoryTransactions()
            ->with('creator')
            ->latest('id')
            ->paginate(20);

        return view('inventory.history', compact('product', 'transactions'));
    }

    public function adjust(AdjustStockRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();

        try {
            match ($data['mode']) {
                'increase' => $this->inventory->increase($product, $data['quantity'], $data['type'], ['notes' => $data['notes'] ?? null]),
                'decrease' => $this->inventory->decrease($product, $data['quantity'], $data['type'], ['notes' => $data['notes'] ?? null]),
                'set' => $this->inventory->adjustTo($product, $data['quantity'], ['notes' => $data['notes'] ?? null]),
            };
        } catch (InsufficientStockException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Stock updated.');
    }
}
