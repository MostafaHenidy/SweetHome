<?php

namespace App\Http\Livewire;

use Livewire\Component;

class CartPage extends Component
{
    public $cart = [];

    protected $listeners = ['cart-updated' => 'refreshCart'];

    public function mount() { $this->refreshCart(); }

    public function refreshCart() { $this->cart = session()->get('cart', []); }

    public function updateQuantity($key, $quantity)
    {
        if ($quantity < 1) { $this->removeItem($key); return; }
        $this->cart[$key]['quantity'] = $quantity;
        session()->put('cart', $this->cart);
    }

    public function removeItem($key)
    {
        unset($this->cart[$key]);
        session()->put('cart', $this->cart);
    }

    public function clearCart()
    {
        session()->forget('cart');
        $this->cart = [];
    }

    public function getSubtotalProperty()
    {
        return collect($this->cart)->sum(fn($item) => $item['price'] * $item['quantity']);
    }

    public function render() { return view('livewire.cart-page'); }
}
