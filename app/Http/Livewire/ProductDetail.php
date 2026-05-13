<?php

namespace App\Http\Livewire;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Services\CartService;
use App\Services\ProductRecommendationService;
use Livewire\Component;

class ProductDetail extends Component
{
    public $productSlug;

    public $selectedAttributes = [];

    public $quantity = 1;

    public $activeImageIndex = 0;

    public ?int $selectedVariantId = null;

    public $reviewRating = 5;

    public $reviewComment = '';

    public function mount($slug)
    {
        $this->productSlug = $slug;

        $product = Product::query()
            ->where('slug', $slug)
            ->published()
            ->with([
                'variants' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
            ])
            ->first();

        if ($product && $product->variants->count() === 1) {
            $this->selectedVariantId = (int) $product->variants->first()->id;
        }
    }

    public function addToCart(CartService $cartService)
    {
        $this->resetErrorBag('variant');

        $product = Product::where('slug', $this->productSlug)->firstOrFail();

        $activeVariants = $product->variants()->where('is_active', true)->get();
        if ($activeVariants->isNotEmpty()) {
            $valid = $this->selectedVariantId
                && $activeVariants->contains(fn ($v) => (int) $v->id === (int) $this->selectedVariantId);
            if (! $valid) {
                $this->addError('variant', 'Seleziona una variante.');

                return;
            }
        }

        $attributes = collect($this->selectedAttributes)
            ->filter(fn ($v) => $v !== null && $v !== '')
            ->all();

        if ($this->selectedVariantId) {
            $attributes['variant_id'] = $this->selectedVariantId;
        }

        $cartService->addToCart($product->id, $this->quantity, $attributes);

        $this->dispatch('cart-updated');
        $this->dispatch('notify', message: 'Prodotto aggiunto al carrello!');
    }

    public function submitReview(): void
    {
        $product = Product::where('slug', $this->productSlug)->firstOrFail();

        if (! auth()->check()) {
            $this->addError('review', 'Devi accedere per lasciare una recensione.');

            return;
        }

        if (! $product->enable_reviews) {
            $this->addError('review', 'Le recensioni non sono abilitate per questo prodotto.');

            return;
        }

        if (! $this->customerCanReview($product)) {
            $this->addError('review', 'Puoi recensire solo prodotti acquistati con un ordine pagato.');

            return;
        }

        if ($this->customerAlreadyReviewed($product)) {
            $this->addError('review', 'Hai gia inviato una recensione per questo prodotto.');

            return;
        }

        $this->validate([
            'reviewRating' => ['required', 'integer', 'min:1', 'max:5'],
            'reviewComment' => ['nullable', 'string', 'max:1200'],
        ]);

        Review::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'user_email' => auth()->user()->email,
            'rating' => $this->reviewRating,
            'comment' => $this->reviewComment,
            'status' => 'pending',
            'show_on_home' => false,
        ]);

        $this->reset(['reviewRating', 'reviewComment']);
        $this->reviewRating = 5;
        session()->flash('review_submitted', 'Grazie! La recensione sara pubblicata dopo l approvazione.');
    }

    private function customerCanReview(Product $product): bool
    {
        if (! auth()->check()) {
            return false;
        }

        $user = auth()->user();

        return Order::query()
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('customer_email', $user->email);
            })
            ->where('payment_status', 'paid')
            ->where('status', '!=', 'cancelled')
            ->whereHas('items', fn ($query) => $query->where('product_id', $product->id))
            ->exists();
    }

    private function customerAlreadyReviewed(Product $product): bool
    {
        if (! auth()->check()) {
            return false;
        }

        $user = auth()->user();

        return Review::query()
            ->where('product_id', $product->id)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('user_email', $user->email);
            })
            ->exists();
    }

    public function render(ProductRecommendationService $recommendations)
    {
        $product = Product::where('slug', $this->productSlug)
            ->published()
            ->with([
                'brand',
                'categories',
                'attributes',
                'images',
                'variants' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')->with('media'),
                'reviews' => fn ($q) => $q->approved(),
            ])
            ->firstOrFail();

        return view('livewire.product-detail', [
            'product' => $product,
            'relatedProducts' => $recommendations->forProduct($product),
            'canReviewProduct' => $this->customerCanReview($product),
            'alreadyReviewedProduct' => $this->customerAlreadyReviewed($product),
        ]);
    }

    public function updatedSelectedVariantId(?int $value): void
    {
        if (! $value) {
            return;
        }

        $product = Product::where('slug', $this->productSlug)
            ->published()
            ->with(['variants' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->first();

        if (! $product || $product->getMedia('images')->isNotEmpty() || $product->featured_image) {
            return;
        }

        $index = $product->variants->search(fn ($v) => (int) $v->id === (int) $value);
        if ($index !== false) {
            $this->activeImageIndex = (int) $index;
        }
    }
}
