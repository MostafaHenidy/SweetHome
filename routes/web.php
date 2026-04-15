<?php

use App\Helpers\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

// Storefront pages
Route::get('/auth', function () {
    return view('auth-storefront');
})->name('auth');

Route::get('/cart', function () {
    return view('cart', [
        'items' => Cart::items(),
        'subtotal' => Cart::subtotal(),
        'shipping' => Cart::subtotal() > 0 ? 15.00 : 0,
        'total' => Cart::subtotal() + (Cart::subtotal() > 0 ? 15.00 : 0),
    ]);
})->name('cart');

Route::get('/checkout', function () {
    return view('checkout', [
        'items' => Cart::items(),
        'subtotal' => Cart::subtotal(),
        'shipping' => Cart::subtotal() > 0 ? 15.00 : 0,
        'total' => Cart::subtotal() + (Cart::subtotal() > 0 ? 15.00 : 0),
    ]);
})->name('checkout');

// Cart API routes
Route::post('/cart/update', function (Request $request) {
    $request->validate(['id' => 'required', 'quantity' => 'required|integer|min:0']);
    Cart::update($request->id, $request->quantity);
    return response()->json(['success' => true]);
})->name('cart.update');

Route::post('/cart/remove', function (Request $request) {
    $request->validate(['id' => 'required']);
    Cart::remove($request->id);
    return response()->json(['success' => true]);
})->name('cart.remove');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
