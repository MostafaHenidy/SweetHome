<?php

namespace App\Http\Livewire;

use App\Models\Product;
use Livewire\Component;

class ProductDetail extends Component
{
    public $productSlug;
    public $selectedAttributes = [];
    public $quantity = 1;
    public $activeImageIndex = 0;

    public function mount($slug)
    {
        $this->productSlug = $slug;
    }

    public function addToCart()
    {
        $product = Product::where('slug', $this->productSlug)->firstOrFail();

        $cart = session()->get('cart', []);
        $cartKey = $product->id . '-' . md5(json_encode($this->selectedAttributes));

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += $this->quantity;
        } else {
            $cart[$cartKey] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'image' => $product->getFirstMediaUrl('images', 'thumb'),
                'attributes' => $this->selectedAttributes,
                'quantity' => $this->quantity,
            ];
        }

        session()->put('cart', $cart);

        $this->dispatch('cart-updated');
        $this->dispatch('notify', message: 'Prodotto aggiunto al carrello!');
    }

    public function render()
    {
        $product = Product::where('slug', $this->productSlug)
            ->published()
            ->with(['brand', 'categories', 'attributes', 'reviews' => fn($q) => $q->approved()])
            ->firstOrFail();

        $relatedProducts = Product::published()
            ->where('id', '!=', $product->id)
            ->whereHas('categories', function ($q) use ($product) {
                $q->whereIn('category_id', $product->categories->pluck('id'));
            })
            ->limit(4)
            ->get();

        return view('livewire.product-detail', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
