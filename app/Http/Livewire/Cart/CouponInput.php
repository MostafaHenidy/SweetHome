<?php

namespace App\Http\Livewire\Cart;

use App\Services\CouponService;
use Livewire\Component;

class CouponInput extends Component
{
    public string $code = '';
    public ?array $appliedCoupon = null;
    public string $error = '';
    public array $cart = [];

    public function mount(): void
    {
        $this->cart = session()->get('cart', []);
        $this->appliedCoupon = session()->get('coupon');
        $this->code = $this->appliedCoupon['code'] ?? '';
    }

    public function apply(CouponService $couponService): void
    {
        $this->error = '';
        $this->cart = session()->get('cart', []);

        try {
            $coupon = $couponService->findValidCoupon($this->code, auth()->user(), $this->cart);
            $discount = $couponService->calculateDiscount($coupon, $this->cart);

            $this->appliedCoupon = [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'discount' => $discount,
            ];

            session()->put('coupon', $this->appliedCoupon);
            $this->dispatch('coupon-updated');
        } catch (\InvalidArgumentException $exception) {
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
