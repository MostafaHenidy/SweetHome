<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\FawaterakWebhookController;
use App\Http\Controllers\FirebaseController;
use App\Http\Controllers\OrderDocumentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\QRCodeController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Livewire\CartPage;
use App\Http\Livewire\CheckoutPage;
use App\Http\Livewire\ProductDetail;
use App\Http\Livewire\QuoteRequestPage;
use App\Http\Livewire\QuoteSuccessPage;
use App\Http\Livewire\ShopPage;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

Route::get('/', function () {
    $featuredReviews = collect();

    if (Schema::hasTable('reviews') && Schema::hasColumn('reviews', 'show_on_home')) {
        $featuredReviews = Review::query()
            ->sharedOnHome()
            ->with('product')
            ->latest()
            ->take(8)
            ->get();
    }

    return view('home', compact('featuredReviews'));
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

// Fawaterak webhook
Route::post('/webhook/fawaterak', [FawaterakWebhookController::class, 'handle'])->name('webhook.fawaterak');

Route::get('/auth', function () {
    return view('auth-storefront');
})->name('auth');

// Cart and Checkout
Route::get('/cart', CartPage::class)->name('cart');
Route::get('/checkout', CheckoutPage::class)->name('checkout');
Route::get('/checkout/success/{orderNumber}', function ($orderNumber) {
    $order = Order::where('order_number', $orderNumber)->firstOrFail();

    $confirmationUrl = $order->order_confirmation_pdf_path
        ? URL::temporarySignedRoute(
            'orders.document.signed',
            now()->addDays(14),
            ['order' => $order->id, 'document' => 'confirmation']
        )
        : null;

    $invoiceUrl = ($order->payment_status === 'paid' && $order->invoice_pdf_path)
        ? URL::temporarySignedRoute(
            'orders.document.signed',
            now()->addDays(14),
            ['order' => $order->id, 'document' => 'invoice']
        )
        : null;

    return view('checkout-success', compact('order', 'confirmationUrl', 'invoiceUrl'));
})->name('checkout.success');

// Quote Request Routes
Route::get('/request-quote', QuoteRequestPage::class)->name('request-quote');
Route::get('/quote/success/{orderNumber}', QuoteSuccessPage::class)->name('quote.success');
Route::get('/quote/download/{orderNumber}', function ($orderNumber) {
    $order = Order::where('order_number', $orderNumber)
        ->where('type', 'quote_request')
        ->firstOrFail();

    if (! $order->quote_pdf_path) {
        abort(404, 'PDF not available yet');
    }

    if (! Storage::disk('public')->exists($order->quote_pdf_path)) {
        abort(404, 'PDF file not found');
    }

    return Storage::disk('public')->download($order->quote_pdf_path, 'preventivo_'.$order->order_number.'.pdf');
})->name('quote.download');

Route::get('/documents/order/{order}/{document}', [OrderDocumentController::class, 'signed'])
    ->middleware('signed')
    ->whereIn('document', ['confirmation', 'invoice'])
    ->name('orders.document.signed');

// Shop routes
Route::get('/shop', ShopPage::class)->name('shop');
Route::get('/shop/{category}', ShopPage::class)->name('shop.category');
Route::get('/product/{slug}', ProductDetail::class)->name('product.show');

// Payment routes
Route::match(['get', 'post'], '/payment/{orderNumber}', [PaymentController::class, 'initiate'])->name('payment.initiate');
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

    Route::get('/account/quotes', function () {
        return view('account');
    })->name('account.quotes');

    Route::get('/account/profile', function () {
        return view('account', ['activeTab' => 'profile']);
    })->name('account.profile');

    Route::get('/account/favorites', function () {
        return view('account', ['activeTab' => 'favorites']);
    })->name('account.favorites');

    Route::get('/account/inquiries', function () {
        return view('account', ['activeTab' => 'inquiries']);
    })->name('account.inquiries');

    Route::get('/account/orders/{order}/document/{document}', [OrderDocumentController::class, 'authenticated'])
        ->whereIn('document', ['confirmation', 'invoice'])
        ->name('account.orders.document');

    // Firebase token management routes
    Route::post('/firebase/token', [FirebaseController::class, 'registerToken'])->name('firebase.token.register');
    Route::delete('/firebase/token', [FirebaseController::class, 'unregisterToken'])->name('firebase.token.unregister');
    Route::get('/firebase/tokens', [FirebaseController::class, 'getTokens'])->name('firebase.tokens.list');
});

// QR Code coupon redemption routes
Route::get('/r/{token}', [QRCodeController::class, 'scan'])
    ->name('qr.scan')
    ->middleware('web');

Route::get('/coupon/redeemed', [QRCodeController::class, 'handlePostLoginRedemption'])
    ->name('qr.post-login')
    ->middleware('auth');

// Cart API endpoint - needs session support
Route::post('/api/add-to-cart', [CartController::class, 'addToCart'])
    ->name('api.add-to-cart');

// API endpoint for mobile app scanning
Route::post('/api/coupon/scan', [QRCodeController::class, 'apiScan'])
    ->name('api.qr.scan')
    ->middleware(['auth:sanctum', 'throttle:10,1']); // 10 requests per minute

// Bulk QR code export route
Route::get('/admin/coupons/export-qr', [QRCodeController::class, 'bulkExport'])
    ->name('admin.coupons.export-qr')
    ->middleware(['auth']);
