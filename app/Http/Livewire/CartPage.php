<?php

namespace App\Http\Livewire;

use App\Services\CartService;
use Livewire\Component;

class CartPage extends Component
{
    public $cart = [];

    protected CartService $cartService;

    protected $listeners = ['cart-updated' => 'refreshCart', 'coupon-updated' => 'handleCouponUpdated'];

    public function handleCouponUpdated($couponData = null)
    {
        // Debug: Log the event data
        \Log::info('CartPage received coupon-updated event:', ['coupon_data' => $couponData]);

        // The coupon should be in session, but let's also refresh the cart
        $this->refreshCart();
    }

    public function boot(CartService $cartService): void
    {
        $this->cartService = $cartService;
    }

    public function mount()
    {
        $this->refreshCart();
    }

    public function refreshCart()
    {
        if (auth()->check()) {
            $items = $this->cartService->getCartItems();
            $this->cart = $items->mapWithKeys(function ($item) {
                return [$item->id => [
                    'product_id' => $item->product_id,
                    'name' => $item->name,
                    'price' => $item->price,
                    'image' => $item->image,
                    'attributes' => $item->attributes ?? [],
                    'quantity' => $item->quantity,
                ]];
            })->toArray();

            return;
        }

        $this->cart = session()->get('cart', []);

        // Force refresh the discount calculation by checking session directly
        $coupon = session()->get('coupon');
        if ($coupon && config('app.debug')) {
            \Log::info('CartPage refreshCart - coupon session:', ['coupon' => $coupon]);
        }
    }

    public function updateQuantity($key, $quantity)
    {
        if ($quantity < 1) {
            $this->removeItem($key);

            return;
        }

        if (auth()->check()) {
            $this->cartService->updateQuantity($key, $quantity);
            $this->refreshCart();

            return;
        }

        $this->cart[$key]['quantity'] = $quantity;
        session()->put('cart', $this->cart);
    }

    public function removeItem($key)
    {
        if (auth()->check()) {
            $this->cartService->removeItem($key);
            $this->refreshCart();

            return;
        }

        unset($this->cart[$key]);
        session()->put('cart', $this->cart);
    }

    public function clearCart()
    {
        if (auth()->check()) {
            $this->cartService->clearCart();
            $this->cart = [];

            return;
        }

        session()->forget('cart');
        $this->cart = [];
    }

    public function getSubtotalProperty()
    {
        return collect($this->cart)->sum(fn ($item) => $item['price'] * $item['quantity']);
    }

    public function getDiscountAmountProperty()
    {
        // Get coupon from session directly
        $coupon = session('coupon');

        // If coupon exists but cart is empty or user is not logged in, clear invalid coupon
        if ($coupon && (empty($this->cart) || ! auth()->check())) {
            session()->forget('coupon');
            \Log::info('CartPage cleared invalid coupon:', [
                'reason' => empty($this->cart) ? 'empty_cart' : 'user_not_logged_in',
                'coupon_code' => $coupon['code'] ?? 'unknown',
            ]);

            return 0;
        }

        // Debug: Always log to see what's happening
        \Log::info('CartPage getDiscountAmountProperty:', [
            'coupon_from_session' => $coupon,
            'discount_value' => $coupon['discount'] ?? 0,
            'coupon_code' => $coupon['code'] ?? 'none',
            'session_id' => session()->getId(),
            'request_id' => request()->header('X-Livewire-Id', 'web-request'),
        ]);

        return $coupon['discount'] ?? 0;
    }

    public function getTotalProperty()
    {
        return max(0, $this->subtotal - $this->discountAmount);
    }

    public function render()
    {
        return view('livewire.cart-page');
    }
}
