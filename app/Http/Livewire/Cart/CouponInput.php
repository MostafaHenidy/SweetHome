<?php

namespace App\Http\Livewire\Cart;

use App\Services\CartService;
use App\Services\CouponService;
use Livewire\Component;

class CouponInput extends Component
{
    public string $code = '';

    public ?array $appliedCoupon = null;

    public string $error = '';

    public array $cart = [];

    public function mount(CartService $cartService): void
    {
        // Get cart using the same service as CartPage
        $cartItems = $cartService->getCartItems();
        $this->cart = $cartItems->mapWithKeys(function ($item) {
            return [$item->id => [
                'product_id' => $item->product_id,
                'name' => $item->name,
                'price' => $item->price,
                'image' => $item->image,
                'attributes' => $item->attributes ?? [],
                'quantity' => $item->quantity,
            ]];
        })->toArray();

        $this->appliedCoupon = session()->get('coupon');
        $this->code = $this->appliedCoupon['code'] ?? '';
    }

    public function apply(CouponService $couponService, CartService $cartService): void
    {
        $this->error = '';

        // Get cart using the same service as CartPage
        $cartItems = $cartService->getCartItems();
        $this->cart = $cartItems->mapWithKeys(function ($item) {
            return [$item->id => [
                'product_id' => $item->product_id,
                'name' => $item->name,
                'price' => $item->price,
                'image' => $item->image,
                'attributes' => $item->attributes ?? [],
                'quantity' => $item->quantity,
            ]];
        })->toArray();

        // Debug: Log cart contents and auth status
        // \Log::info('Coupon application attempt:', [
        //     'code' => $this->code,
        //     'user_logged_in' => auth()->check(),
        //     'user_id' => auth()->id(),
        //     'cart_items' => count($this->cart),
        //     'cart_subtotal' => collect($this->cart)->sum(fn($item) => $item['price'] * $item['quantity']),
        // ]);

        try {
            $coupon = $couponService->findValidCoupon($this->code, auth()->user(), $this->cart);
            $discount = $couponService->calculateDiscount($coupon, $this->cart);

            // \Log::info('Coupon calculation:', [
            //     'coupon_id' => $coupon->id,
            //     'coupon_type' => $coupon->type,
            //     'coupon_value' => $coupon->value,
            //     'discount_amount' => $discount,
            // ]);

            $this->appliedCoupon = [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'discount' => $discount,
            ];

            session()->put('coupon', $this->appliedCoupon);

            // Debug: Log the coupon application
            // \Log::info('Coupon applied:', [
            //     'coupon_code' => $coupon->code,
            //     'discount_amount' => $discount,
            //     'session_data' => session('coupon')
            // ]);

            // Emit the coupon data along with the event
            $this->dispatch('coupon-updated', $this->appliedCoupon);
        } catch (\Exception $exception) {
            // \Log::error('Coupon application failed:', [
            //     'code' => $this->code,
            //     'error' => $exception->getMessage(),
            //     'trace' => $exception->getTraceAsString(),
            // ]);
            $this->error = $exception->getMessage();
            $this->appliedCoupon = null;
            session()->forget('coupon');
        }
    }

    public function remove(): void
    {
        $this->code = '';
        $this->error = '';
        $this->appliedCoupon = null;
        session()->forget('coupon');
        $this->dispatch('coupon-updated');
    }

    public function render()
    {
        return view('livewire.cart.coupon-input');
    }
}
