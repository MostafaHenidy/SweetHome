<?php

use App\Http\Livewire\CartPage;
use App\Http\Livewire\CheckoutPage;
use App\Http\Livewire\ProductDetail;
use App\Http\Livewire\ShopPage;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

// Storefront pages
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

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
