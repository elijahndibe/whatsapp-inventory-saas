<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function show(Request $request, Business $business): View
    {
        abort_unless($business->isActive(), 404);

        $categories = Category::forBusiness($business->id)->active()->orderBy('name')->get();

        $search = $request->string('search')->toString();
        $categoryId = $request->integer('category_id') ?: null;
        $showFeatured = blank($search) && ! $categoryId;

        $featured = $showFeatured
            ? Product::forBusiness($business->id)->active()->where('featured', true)->with('images')->limit(8)->get()
            : collect();

        $products = Product::forBusiness($business->id)
            ->active()
            ->search($search)
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->with('images')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('storefront.show', compact('business', 'categories', 'featured', 'products'));
    }
}
