<?php

namespace App\Http\Controllers;

use App\Services\CouponService;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function validateCoupon(Request $request, CouponService $couponService)
    {
        $data = $request->validate([
            'code' => ['required', 'string'],
            'cart' => ['required', 'array'],
        ]);

        $coupon = $couponService->findValidCoupon($data['code'], $request->user(), $data['cart']);

        return response()->json([
            'valid' => true,
            'coupon' => $coupon->only(['id', 'code', 'type', 'value', 'product_id']),
            'discount_amount' => $couponService->calculateDiscount($coupon, $data['cart']),
        ]);
    }

    public function apply(Request $request, CouponService $couponService)
    {
        return $this->validateCoupon($request, $couponService);
    }

    public function remove()
    {
        return response()->json(['removed' => true]);
    }
}
