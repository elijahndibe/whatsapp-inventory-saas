<?php

use App\Http\Controllers\Admin\BusinessController as AdminBusinessController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FailedJobController as AdminFailedJobController;
use App\Http\Controllers\Admin\LogController as AdminLogController;
use App\Http\Controllers\Admin\PlanController as AdminPlanController;
use App\Http\Controllers\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\BusinessSettingsController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StaffController;
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

    Route::resource('products', ProductController::class)->except(['show'])
        ->middlewareFor('store', 'plan.limit:products');
    Route::delete('products/{product}/images/{image}', [ProductController::class, 'destroyImage'])->name('products.images.destroy');

    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('products/{product}/inventory', [InventoryController::class, 'history'])->name('products.inventory.history');
    Route::post('products/{product}/inventory/adjust', [InventoryController::class, 'adjust'])->name('products.inventory.adjust');
    Route::post('products/{product}/inventory/transfer', [InventoryController::class, 'transfer'])->name('products.inventory.transfer');
    Route::post('products/{product}/inventory/location-stock', [InventoryController::class, 'setLocationStock'])->name('products.inventory.location-stock');

    Route::resource('locations', LocationController::class)->except(['show']);

    Route::get('staff', [StaffController::class, 'index'])->name('staff.index');
    Route::get('staff/create', [StaffController::class, 'create'])->name('staff.create');
    Route::post('staff', [StaffController::class, 'store'])->name('staff.store');
    Route::get('staff/{user}/edit', [StaffController::class, 'edit'])->name('staff.edit');
    Route::put('staff/{user}', [StaffController::class, 'update'])->name('staff.update');

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

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

    Route::get('billing', [BillingController::class, 'index'])->name('billing.index');
    Route::post('billing/subscribe/{plan}', [BillingController::class, 'subscribe'])->name('billing.subscribe');
    Route::get('billing/callback', [BillingController::class, 'callback'])->name('billing.callback');

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
    Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('plan.limit:orders')->name('checkout.store');
    Route::get('/orders/{publicToken}', [CheckoutController::class, 'confirmation'])->name('orders.confirmation');

    Route::get('/payments/callback', [PaymentController::class, 'callback'])->name('payments.callback');
    Route::post('/orders/{publicToken}/pay', [PaymentController::class, 'retry'])->name('payments.retry');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'super_admin'])->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');

    Route::get('businesses', [AdminBusinessController::class, 'index'])->name('businesses.index');
    Route::get('businesses/{business}', [AdminBusinessController::class, 'show'])->name('businesses.show');
    Route::post('businesses/{business}/suspend', [AdminBusinessController::class, 'suspend'])->name('businesses.suspend');
    Route::post('businesses/{business}/activate', [AdminBusinessController::class, 'activate'])->name('businesses.activate');

    Route::resource('plans', AdminPlanController::class)->except(['show', 'destroy']);

    Route::get('subscriptions', [AdminSubscriptionController::class, 'index'])->name('subscriptions.index');

    Route::get('users', [AdminUserController::class, 'index'])->name('users.index');

    Route::get('failed-jobs', [AdminFailedJobController::class, 'index'])->name('failed-jobs.index');
    Route::post('failed-jobs/{uuid}/retry', [AdminFailedJobController::class, 'retry'])->name('failed-jobs.retry');
    Route::delete('failed-jobs/{uuid}', [AdminFailedJobController::class, 'destroy'])->name('failed-jobs.destroy');

    Route::get('logs', [AdminLogController::class, 'index'])->name('logs.index');
});

// Platform-wide (not business-scoped): Paystack sends one webhook URL for
// the whole account. Must stay outside CSRF protection — see bootstrap/app.php.
Route::post('/webhooks/paystack', PaystackWebhookController::class)->name('webhooks.paystack');

// Same pattern for Meta/WhatsApp: one webhook URL per Meta App, shared by
// every business, routed internally by phone_number_id.
Route::get('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'verify'])->name('webhooks.whatsapp.verify');
Route::post('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'handle'])->name('webhooks.whatsapp.handle');

require __DIR__.'/auth.php';
