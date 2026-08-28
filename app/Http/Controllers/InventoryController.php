<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Http\Requests\Product\AdjustStockRequest;
use App\Http\Requests\Product\SetLocationStockRequest;
use App\Http\Requests\Product\TransferStockRequest;
use App\Models\BusinessLocation;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use InvalidArgumentException;

class InventoryController extends Controller
{
    public function __construct(private readonly InventoryService $inventory) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $lowStock = Product::lowStock()->orderBy('stock_quantity')->get();
        $outOfStock = Product::outOfStock()->orderBy('name')->get();

        $totalProducts = Product::count();
        $inventoryValue = (Product::query()->sum(DB::raw('price * stock_quantity')) ?? 0) / 100;

        return view('inventory.index', compact('lowStock', 'outOfStock', 'totalProducts', 'inventoryValue'));
    }

    public function history(Request $request, Product $product): View
    {
        $this->authorize('view', $product);

        $transactions = $product->inventoryTransactions()
            ->with(['creator', 'fromLocation', 'toLocation'])
            ->latest('id')
            ->paginate(20);

        $locationStock = $product->locationStock()->with('location')->get();
        $businessLocations = $request->user()->business->locations()->where('status', 'active')->get();

        return view('inventory.history', compact('product', 'transactions', 'locationStock', 'businessLocations'));
    }

    public function transfer(TransferStockRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();

        $from = BusinessLocation::findOrFail($data['from_location_id']);
        $to = BusinessLocation::findOrFail($data['to_location_id']);

        try {
            $this->inventory->transferStock($product, $from, $to, $data['quantity'], ['notes' => $data['notes'] ?? null]);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Stock transferred.');
    }

    public function setLocationStock(SetLocationStockRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();
        $location = BusinessLocation::findOrFail($data['location_id']);

        $this->inventory->setLocationStock($product, $location, $data['quantity']);

        return back()->with('status', 'Location stock updated.');
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
