<?php

namespace App\Http\Livewire;

use App\Events\OrderCreated;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
use App\Services\BrandedPdfService;
use App\Services\CartService;
use App\Services\CouponService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class CheckoutPage extends Component
{
    public $step = 1;

    public $customer_name = '';

    public $customer_email = '';

    public $customer_phone = '';

    public $customer_company_name = '';

    public $customer_vat_number = '';

    public $customer_tax_code = '';

    public $address_line1 = '';

    public $address_line2 = '';

    public $city = '';

    public $province = '';

    public $postal_code = '';

    public $country = 'IT';

    public $payment_method = 'stripe';

    // New fields for enhanced order system
    public $shipping_method = 'delivery';
    public $service_type = 'shipping_only';
    public $pickup_location = '';

    // Shipping address fields (now separate from customer address)
    public $shipping_address_line1 = '';
    public $shipping_address_line2 = '';
    public $shipping_city = '';
    public $shipping_province = '';
    public $shipping_postal_code = '';
    public $shipping_country = 'IT';

    public $cart = [];

    public $coupon_code = '';

    public $applied_coupon = null;

    public $coupon_error = '';

    protected CartService $cartService;
    protected PaymentInstallmentService $installmentService;

    protected $rules = [
        'customer_name' => 'required|string|min:2|max:255',
        'customer_email' => 'required|email|max:255',
        'customer_phone' => 'nullable|string|max:20',
        'customer_company_name' => 'nullable|string|max:255',
        'customer_vat_number' => 'nullable|string|max:32',
        'customer_tax_code' => 'nullable|string|max:32',
        'address_line1' => 'required|string|max:255',
        'address_line2' => 'nullable|string|max:255',
        'city' => 'required|string|max:100',
        'province' => 'required|string|max:100',
        'postal_code' => 'required|string|max:10',
        'country' => 'required|string|max:2',
        'payment_method' => 'required|in:stripe,paypal,bank_transfer,fawaterak',
        'shipping_method' => 'required|in:delivery,pickup',
        'service_type' => 'required_if:shipping_method,delivery|in:shipping_only,shipping_and_construction',
        'pickup_location' => 'required_if:shipping_method,pickup|string|max:255',
        'shipping_address_line1' => 'required_if:shipping_method,delivery|string|max:255',
        'shipping_address_line2' => 'nullable|string|max:255',
        'shipping_city' => 'required_if:shipping_method,delivery|string|max:100',
        'shipping_province' => 'required_if:shipping_method,delivery|string|max:100',
        'shipping_postal_code' => 'required_if:shipping_method,delivery|string|max:10',
        'shipping_country' => 'required_if:shipping_method,delivery|string|max:2',
    ];

    public function boot(
        CartService $cartService,
        PaymentInstallmentService $installmentService
    ): void {
        $this->cartService = $cartService;
        $this->installmentService = $installmentService;
    }

    public function mount()
    {
        if (auth()->check()) {
            $this->cart = $this->cartService->getCartItems()->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'name' => $item->name,
                    'price' => $item->price,
                    'image' => $item->image,
                    'attributes' => $item->attributes ?? [],
                    'quantity' => $item->quantity,
                ];
            })->values()->toArray();
        } else {
            $this->cart = session()->get('cart', []);
        }

        $this->applied_coupon = session()->get('coupon');
        if (empty($this->cart)) {
            redirect()->route('cart');
        }
        if (auth()->check()) {
            $this->customer_name = auth()->user()->name;
            $this->customer_email = auth()->user()->email;
            $this->customer_phone = auth()->user()->phone ?? '';

            // Pre-fill shipping address from user profile if available
            if (auth()->user()->address_line1) {
                $this->shipping_address_line1 = auth()->user()->address_line1;
                $this->shipping_address_line2 = auth()->user()->address_line2 ?? '';
                $this->shipping_city = auth()->user()->city ?? '';
                $this->shipping_province = auth()->user()->province ?? '';
                $this->shipping_postal_code = auth()->user()->postal_code ?? '';
                $this->shipping_country = auth()->user()->country ?? 'IT';
            }
        }
    }

    public function nextStep()
    {
        if ($this->step === 1) {
            $this->validateOnly('customer_name');
            $this->validateOnly('customer_email');
        }
        if ($this->step === 2) {
            $this->validateOnly('shipping_method');
            if ($this->shipping_method === 'delivery') {
                $this->validateOnly('service_type');
                $this->validateOnly('shipping_address_line1');
                $this->validateOnly('shipping_city');
                $this->validateOnly('shipping_province');
                $this->validateOnly('shipping_postal_code');
                $this->validateOnly('shipping_country');
            } else {
                $this->validateOnly('pickup_location');
            }
        }
        if ($this->step === 3) {
            $this->validateOnly('payment_method');
        }
        $this->step++;
    }

    public function applyCoupon(CouponService $couponService): void
    {
        $this->coupon_error = '';

        try {
            $coupon = $couponService->findValidCoupon($this->coupon_code, auth()->user(), $this->cart);
            $discount = $couponService->calculateDiscount($coupon, $this->cart);

            $this->applied_coupon = [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'discount' => $discount,
            ];

            session()->put('coupon', $this->applied_coupon);
        } catch (\InvalidArgumentException $exception) {
            $this->coupon_error = $exception->getMessage();
            $this->applied_coupon = null;
            session()->forget('coupon');
        }
    }

    public function removeCoupon(): void
    {
        $this->coupon_code = '';
        $this->coupon_error = '';
        $this->applied_coupon = null;
        session()->forget('coupon');
    }

    public function placeOrder()
    {
        $this->validate([
            'customer_name' => 'required|string|min:2|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_company_name' => 'nullable|string|max:255',
            'customer_vat_number' => 'nullable|string|max:32',
            'customer_tax_code' => 'nullable|string|max:32',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'country' => 'required|string|max:2',
            'payment_method' => 'required|in:stripe,paypal,bank_transfer,fawaterak',
            'shipping_method' => 'required|in:delivery,pickup',
            'service_type' => 'required_if:shipping_method,delivery|in:shipping_only,shipping_and_construction',
            'pickup_location' => 'required_if:shipping_method,pickup|string|max:255',
            'shipping_address_line1' => 'required_if:shipping_method,delivery|string|max:255',
            'shipping_address_line2' => 'nullable|string|max:255',
            'shipping_city' => 'required_if:shipping_method,delivery|string|max:100',
            'shipping_province' => 'required_if:shipping_method,delivery|string|max:100',
            'shipping_postal_code' => 'required_if:shipping_method,delivery|string|max:10',
            'shipping_country' => 'required_if:shipping_method,delivery|string|max:2',
        ]);

        $coupon = null;
        $discount = 0;

        if (! empty($this->applied_coupon['code'])) {
            try {
                $coupon = app(CouponService::class)->findValidCoupon($this->applied_coupon['code'], auth()->user(), $this->cart);
                $discount = app(CouponService::class)->calculateDiscount($coupon, $this->cart);
            } catch (\InvalidArgumentException) {
                $this->removeCoupon();
            }
        }

        // Determine shipping address based on method
        $shippingAddress = $this->shipping_method === 'delivery' ? [
            'line1' => $this->shipping_address_line1,
            'line2' => $this->shipping_address_line2,
            'city' => $this->shipping_city,
            'province' => $this->shipping_province,
            'postal_code' => $this->shipping_postal_code,
            'country' => $this->shipping_country,
        ] : [
            'type' => 'pickup',
            'location' => $this->pickup_location,
        ];

        $order = Order::create([
            'order_number' => 'SH-'.date('Ymd').'-'.str_pad(Order::count() + 1, 4, '0', STR_PAD_LEFT),
            'type' => 'direct',
            'user_id' => auth()->id(),
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'customer_phone' => $this->customer_phone,
            'customer_company_name' => $this->customer_company_name ?: null,
            'customer_vat_number' => $this->customer_vat_number ?: null,
            'customer_tax_code' => $this->customer_tax_code ?: null,
            'shipping_address' => $shippingAddress,
            // Store billing address as customer address
            'billing_address' => [
                'line1' => $this->address_line1,
                'line2' => $this->address_line2,
                'city' => $this->city,
                'province' => $this->province,
                'postal_code' => $this->postal_code,
                'country' => $this->country,
            ],
            'subtotal' => $this->subtotal,
            'shipping_cost' => $this->shippingCost,
            'tax' => 0,
            'coupon_id' => $coupon?->id,
            'coupon_code' => $coupon?->code,
            'discount_amount' => $discount,
            'total' => max(0, $this->subtotal + $this->shippingCost - $discount),
            'payment_method' => $this->payment_method,
            'payment_status' => 'pending',
            'status' => 'pending_deposit',
            // New fields for enhanced order system
            'shipping_method' => $this->shipping_method,
            'service_type' => $this->shipping_method === 'delivery' ? $this->service_type : 'shipping_only',
        ]);

        $defaultVat = (float) Setting::get('default_vat_rate', 22);

        foreach ($this->cart as $item) {
            $product = Product::find($item['product_id']);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'product_name' => $product?->name ?? $item['name'],
                'product_sku' => $product?->sku ?? '',
                'line_description' => $product ? $product->quoteLineDescription() : $item['name'],
                'attribute_selection' => $item['attributes'] ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'],
                'total_price' => $item['price'] * $item['quantity'],
                'vat_rate' => $product && $product->vat_rate !== null ? (float) $product->vat_rate : $defaultVat,
            ]);
        }

        if ($coupon) {
            app(CouponService::class)->applyCoupon($coupon, $order);
        }

        // Create payment installments
        $this->installmentService->createInstallments($order);

        $order->load('items', 'paymentInstallments');
        try {
            $path = app(BrandedPdfService::class)->generateAndStoreOrderConfirmation($order);
            $order->update(['order_confirmation_pdf_path' => $path]);
        } catch (\Throwable $e) {
            Log::error('Order confirmation PDF failed', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);
        }

        // Dispatch event for order creation
        OrderCreated::dispatch($order);

        if (auth()->check()) {
            $this->cartService->clearCart();
        } else {
            session()->forget('cart');
        }

        session()->forget('coupon');

        return redirect()->route('payment.initiate', $order->order_number);
    }

    public function getSubtotalProperty()
    {
        return collect($this->cart)->sum(fn ($item) => $item['price'] * $item['quantity']);
    }

    public function getShippingCostProperty()
    {
        $threshold = Setting::get('free_shipping_threshold', 150);

        return $this->subtotal >= $threshold ? 0 : (Setting::get('shipping_cost', 9.90));
    }

    public function getTotalProperty()
    {
        return max(0, $this->subtotal + $this->shippingCost - $this->discountAmount);
    }

    public function getDiscountAmountProperty()
    {
        return (float) ($this->applied_coupon['discount'] ?? 0);
    }

    public function getDepositAmountProperty()
    {
        return round($this->total * 0.50, 2);
    }

    public function getRemainingAmountProperty()
    {
        return $this->total - $this->depositAmount;
    }

    public function getPaymentPlanInstallmentsProperty()
    {
        $installments = [];

        if ($this->shipping_method === 'delivery' && $this->service_type === 'shipping_and_construction') {
            // 50-30-20 plan
            $installments = [
                [
                    'number' => 1,
                    'percentage' => 50,
                    'amount' => round($this->total * 0.50, 2),
                    'stage' => 'deposit',
                    'stage_label' => 'Deposit',
                    'pay_now' => true,
                ],
                [
                    'number' => 2,
                    'percentage' => 30,
                    'amount' => round($this->total * 0.30, 2),
                    'stage' => 'on_delivery',
                    'stage_label' => 'On Delivery',
                    'pay_now' => false,
                ],
                [
                    'number' => 3,
                    'percentage' => 20,
                    'amount' => round($this->total * 0.20, 2),
                    'stage' => 'on_construction',
                    'stage_label' => 'After Construction',
                    'pay_now' => false,
                ],
            ];
        } else {
            // 50-50 plan
            $installments = [
                [
                    'number' => 1,
                    'percentage' => 50,
                    'amount' => round($this->total * 0.50, 2),
                    'stage' => 'deposit',
                    'stage_label' => 'Deposit',
                    'pay_now' => true,
                ],
                [
                    'number' => 2,
                    'percentage' => 50,
                    'amount' => round($this->total * 0.50, 2),
                    'stage' => 'on_delivery',
                    'stage_label' => 'On Delivery',
                    'pay_now' => false,
                ],
            ];
        }

        return $installments;
    }

    public function getIsPickupProperty()
    {
        return $this->shipping_method === 'pickup';
    }

    public function getIsDeliveryProperty()
    {
        return $this->shipping_method === 'delivery';
    }

    public function render()
    {
        return view('livewire.checkout-page');
    }
}
