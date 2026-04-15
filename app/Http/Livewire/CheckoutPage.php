<?php

namespace App\Http\Livewire;

use App\Models\Order;
use App\Models\OrderItem;
use Livewire\Component;

class CheckoutPage extends Component
{
    public $step = 1;
    public $customer_name = '';
    public $customer_email = '';
    public $customer_phone = '';
    public $address_line1 = '';
    public $address_line2 = '';
    public $city = '';
    public $province = '';
    public $postal_code = '';
    public $country = 'IT';
    public $payment_method = 'stripe';
    public $cart = [];

    public function mount()
    {
        $this->cart = session()->get('cart', []);
        if (empty($this->cart)) { redirect()->route('cart'); }
        if (auth()->check()) {
            $this->customer_name = auth()->user()->name;
            $this->customer_email = auth()->user()->email;
            $this->customer_phone = auth()->user()->phone ?? '';
        }
    }

    public function nextStep()
    {
        if ($this->step === 1) {
            $this->validateOnly('customer_name');
            $this->validateOnly('customer_email');
        }
        if ($this->step === 2) {
            $this->validateOnly('address_line1');
            $this->validateOnly('city');
            $this->validateOnly('province');
            $this->validateOnly('postal_code');
        }
        $this->step++;
    }

    public function placeOrder()
    {
        $this->validate([
            'customer_name' => 'required|string|min:2|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'country' => 'required|string|max:2',
            'payment_method' => 'required|in:stripe,paypal,bank_transfer',
        ]);

        $order = Order::create([
            'order_number' => 'SH-' . date('Ymd') . '-' . str_pad(Order::count() + 1, 4, '0', STR_PAD_LEFT),
            'user_id' => auth()->id(),
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'customer_phone' => $this->customer_phone,
            'shipping_address' => [
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
            'total' => $this->total,
            'payment_method' => $this->payment_method,
            'payment_status' => 'pending',
        ]);

        foreach ($this->cart as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'product_name' => $item['name'],
                'product_sku' => '',
                'attribute_selection' => $item['attributes'] ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'],
                'total' => $item['price'] * $item['quantity'],
            ]);
        }

        session()->forget('cart');
        redirect()->route('checkout.success', $order->order_number);
    }

    public function getSubtotalProperty()
    {
        return collect($this->cart)->sum(fn($item) => $item['price'] * $item['quantity']);
    }

    public function getShippingCostProperty()
    {
        $threshold = \App\Models\Setting::get('free_shipping_threshold', 150);
        return $this->subtotal >= $threshold ? 0 : (\App\Models\Setting::get('shipping_cost', 9.90));
    }

    public function getTotalProperty()
    {
        return $this->subtotal + $this->shippingCost;
    }

    public function render() { return view('livewire.checkout-page'); }
}
