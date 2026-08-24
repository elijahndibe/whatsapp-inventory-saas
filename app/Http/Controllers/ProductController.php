<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly InventoryService $inventory) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $products = Product::with(['category', 'images'])
            ->search($request->string('search')->toString())
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->integer('category_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->string('stock')->toString() === 'low', fn ($q) => $q->lowStock())
            ->when($request->string('stock')->toString() === 'out', fn ($q) => $q->outOfStock())
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        $this->authorize('create', Product::class);

        $categories = Category::active()->orderBy('name')->get();

        return view('products.create', compact('categories'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $initialStock = $data['stock_quantity'] ?? 0;
        unset($data['stock_quantity'], $data['images']);

        $product = DB::transaction(function () use ($data, $request, $initialStock) {
            $product = Product::create($data + ['stock_quantity' => 0]);

            $this->storeImages($product, $request);

            if ($initialStock > 0) {
                $this->inventory->increase($product, $initialStock, 'purchase', [
                    'notes' => 'Initial stock on product creation',
                ]);
            }

            return $product;
        });

        return redirect()->route('products.edit', $product)->with('status', 'Product created.');
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        $categories = Category::orderBy('name')->get();
        $product->load('images');

        return view('products.edit', compact('product', 'categories'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();
        unset($data['images']);

        DB::transaction(function () use ($data, $request, $product) {
            $product->update($data);
            $this->storeImages($product, $request);
        });

        return redirect()->route('products.edit', $product)->with('status', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        $product->delete();

        return redirect()->route('products.index')->with('status', 'Product deleted.');
    }

    public function destroyImage(Product $product, \App\Models\ProductImage $image): RedirectResponse
    {
        $this->authorize('update', $product);
        abort_unless($image->product_id === $product->id, 404);

        Storage::disk('public')->delete($image->path);
        $image->delete();

        return back()->with('status', 'Image removed.');
    }

    private function storeImages(Product $product, Request $request): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        $hasPrimary = $product->images()->where('is_primary', true)->exists();
        $nextSortOrder = (int) $product->images()->max('sort_order');

        foreach ($request->file('images') as $file) {
            $nextSortOrder++;

            $product->images()->create([
                'business_id' => $product->business_id,
                'path' => $file->store('products', 'public'),
                'is_primary' => ! $hasPrimary && $nextSortOrder === 1,
                'sort_order' => $nextSortOrder,
            ]);

            $hasPrimary = true;
        }
    }
}
