<?php

use App\Http\Controllers\CouponController;
use App\Http\Controllers\PointsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/points', [PointsController::class, 'index']);
    Route::get('/user/points/transactions', [PointsController::class, 'transactions']);
    Route::post('/cart/validate-coupon', [CouponController::class, 'validateCoupon']);
    Route::post('/cart/apply-coupon', [CouponController::class, 'apply']);
    Route::delete('/cart/remove-coupon', [CouponController::class, 'remove']);
});
