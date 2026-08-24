<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CheckoutController;
use App\Http\Controllers\Storefront\StorefrontController;
use App\Http\Controllers\Storefront\StorefrontProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('categories', CategoryController::class)->except(['show']);

    Route::resource('products', ProductController::class)->except(['show']);
    Route::delete('products/{product}/images/{image}', [ProductController::class, 'destroyImage'])->name('products.images.destroy');

    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('products/{product}/inventory', [InventoryController::class, 'history'])->name('products.inventory.history');
    Route::post('products/{product}/inventory/adjust', [InventoryController::class, 'adjust'])->name('products.inventory.adjust');
});

Route::prefix('store/{business:slug}')->name('storefront.')->group(function () {
    Route::get('/', [StorefrontController::class, 'show'])->name('show');
    Route::get('/products/{productSlug}', [StorefrontProductController::class, 'show'])->name('products.show');

    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/cart/{product}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{product}', [CartController::class, 'destroy'])->name('cart.destroy');

    Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/orders/{publicToken}', [CheckoutController::class, 'confirmation'])->name('orders.confirmation');
});

require __DIR__.'/auth.php';
