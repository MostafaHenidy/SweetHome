<?php

namespace App\Http\Livewire;

use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class AddToCart extends Component
{
    public $productId;

    public $added = false;

    public function mount($productId = null)
    {
        $this->productId = $productId;
    }

    public function addToCart()
    {
        if (! $this->productId) {
            $this->dispatch('notify', message: 'Prodotto non valido', type: 'error');

            return;
        }

        try {
            $cartService = app(CartService::class);
            $cartService->addToCart($this->productId, 1);
            $this->added = true;

            // Dispatch notification event
            $this->dispatch('cart-added', message: 'Prodotto aggiunto al carrello!');
            $this->dispatch('cart-updated');

            // Reset added state after 2 seconds
            $this->js('setTimeout(() => { $wire.set("added", false); }, 2000)');

        } catch (\Throwable $e) {
            Log::error('AddToCart failed', [
                'product_id' => $this->productId,
                'user_id' => Auth::id(),
                'message' => $e->getMessage(),
            ]);
            $this->dispatch('notify', message: 'Errore nell\'aggiunta al carrello', type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.add-to-cart');
    }
}
