<?php

use App\Http\Controllers\BusinessSettingsController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CheckoutController;
use App\Http\Controllers\Storefront\PaymentController;
use App\Http\Controllers\Storefront\StorefrontController;
use App\Http\Controllers\Storefront\StorefrontProductController;
use App\Http\Controllers\Webhooks\PaystackWebhookController;
use App\Http\Controllers\Webhooks\WhatsAppWebhookController;
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

    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status.update');
    Route::patch('orders/{order}/payment-status', [OrderController::class, 'updatePaymentStatus'])->name('orders.payment-status.update');
    Route::get('orders/{order}/invoice', [InvoiceController::class, 'invoice'])->name('orders.invoice');
    Route::get('orders/{order}/receipt', [InvoiceController::class, 'receipt'])->name('orders.receipt');

    Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');

    Route::get('settings', [BusinessSettingsController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [BusinessSettingsController::class, 'update'])->name('settings.update');

    Route::post('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
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

    Route::get('/payments/callback', [PaymentController::class, 'callback'])->name('payments.callback');
    Route::post('/orders/{publicToken}/pay', [PaymentController::class, 'retry'])->name('payments.retry');
});

// Platform-wide (not business-scoped): Paystack sends one webhook URL for
// the whole account. Must stay outside CSRF protection — see bootstrap/app.php.
Route::post('/webhooks/paystack', PaystackWebhookController::class)->name('webhooks.paystack');

// Same pattern for Meta/WhatsApp: one webhook URL per Meta App, shared by
// every business, routed internally by phone_number_id.
Route::get('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'verify'])->name('webhooks.whatsapp.verify');
Route::post('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'handle'])->name('webhooks.whatsapp.handle');

require __DIR__.'/auth.php';
