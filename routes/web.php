<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Livewire\CartPage;
use App\Http\Livewire\CheckoutPage;
use App\Http\Livewire\ProductDetail;
use App\Http\Livewire\ShopPage;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

// Storefront pages
Route::get('/services', function () {
    return view('services');
})->name('services');

Route::get('/contact', function () {
    return view('contact');
})->name('contact');

Route::get('/terms-of-service', function () {
    return view('terms');
})->name('terms.show');

Route::get('/privacy-policy', function () {
    return view('policy');
})->name('policy.show');

Route::get('/auth', function () {
    return view('auth-storefront');
})->name('auth');

// Cart and Checkout
Route::get('/cart', CartPage::class)->name('cart');
Route::get('/checkout', CheckoutPage::class)->name('checkout');
Route::get('/checkout/success/{orderNumber}', function ($orderNumber) {
    $order = \App\Models\Order::where('order_number', $orderNumber)->firstOrFail();
    return view('checkout-success', ['order' => $order]);
})->name('checkout.success');

// Shop routes
Route::get('/shop', ShopPage::class)->name('shop');
Route::get('/shop/{category}', ShopPage::class)->name('shop.category');
Route::get('/product/{slug}', ProductDetail::class)->name('product.show');

// Payment routes
Route::post('/payment/{orderNumber}', [PaymentController::class, 'initiate'])->name('payment.initiate');
Route::get('/payment/success/{order}', [PaymentController::class, 'success'])->name('payment.success');
Route::get('/payment/cancel/{order}', [PaymentController::class, 'cancel'])->name('payment.cancel');
Route::get('/paypal/return/{order}', [PaymentController::class, 'paypalReturn'])->name('paypal.return');

Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handle'])->name('webhooks.stripe');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/account', function () {
        return view('account');
    })->name('account.orders');
});
