@php
    use App\Helpers\ImageHelper;

    $productMedia = $product->getMedia('images');
    $galleryItems = [];

    foreach ($productMedia as $media) {
        $galleryItems[] = [
            'url' => $media->hasGeneratedConversion('medium') ? $media->getUrl('medium') : $media->getUrl(),
            'thumb' => $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : $media->getUrl(),
        ];
    }

    if (count($galleryItems) === 0 && $product->featured_image) {
        $galleryItems[] = ['url' => $product->featured_image, 'thumb' => $product->featured_image];
    }

    if (count($galleryItems) === 0) {
        foreach ($product->variants->where('is_active', true)->sortBy('sort_order') as $variant) {
            $u = ImageHelper::getVariantMainImage($variant);
            if ($u) {
                $galleryItems[] = ['url' => $u, 'thumb' => $u];
            }
        }
    }

    $safeImageIndex = count($galleryItems) > 0
        ? max(0, min($activeImageIndex, count($galleryItems) - 1))
        : 0;
@endphp
<div>
    <!-- Breadcrumb -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <nav class="flex text-sm text-gray-400">
            <a href="/" class="hover:text-brand-gold transition">Home</a>
            <span class="mx-2">/</span>
            <a href="{{ route('shop') }}" class="hover:text-brand-gold transition">Shop</a>
            @foreach($product->categories->take(2) as $cat)
                <span class="mx-2">/</span>
                <a href="{{ route('shop.category', $cat->slug) }}" class="hover:text-brand-gold transition">{{ $cat->name }}</a>
            @endforeach
            <span class="mx-2">/</span>
            <span class="text-brand-light">{{ $product->name }}</span>
        </nav>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- Image Gallery (product media, featured_image, or variant Spatie "main") -->
            <div>
                <div class="aspect-square overflow-hidden rounded-sm bg-brand-slate/10 mb-4">
                    @if(count($galleryItems) > 0)
                        <img src="{{ $galleryItems[$safeImageIndex]['url'] }}"
                             alt="{{ $product->name }}"
                             class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-24 h-24 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"/>
                            </svg>
                        </div>
                    @endif
                </div>
                @if(count($galleryItems) > 1)
                    <div class="flex gap-3 overflow-x-auto">
                        @foreach($galleryItems as $index => $item)
                            <button type="button" wire:click="$set('activeImageIndex', {{ $index }})"
                                    class="flex-shrink-0 w-20 h-20 rounded-sm overflow-hidden border-2 {{ $safeImageIndex === $index ? 'border-brand-gold' : 'border-transparent' }} transition">
                                <img src="{{ $item['thumb'] }}" alt="" class="w-full h-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Product Info -->
            <div>
                @php
                    $listedVariants = $product->variants->where('is_active', true)->sortBy('sort_order')->values();
                    $activeVariant = $this->selectedVariantId
                        ? $listedVariants->firstWhere('id', (int) $this->selectedVariantId)
                        : null;
                    if ($activeVariant) {
                        $isVariantSale = $activeVariant->sale_price !== null
                            && (float) $activeVariant->sale_price > 0
                            && (float) $activeVariant->sale_price < (float) $activeVariant->regular_price;
                        $priceShown = $isVariantSale ? $activeVariant->sale_price : $activeVariant->regular_price;
                        $compareShown = $isVariantSale ? $activeVariant->regular_price : null;
                    } else {
                        $priceShown = $product->price;
                        $compareShown = $product->compare_price && (float) $product->compare_price > (float) $product->price
                            ? $product->compare_price
                            : null;
                    }
                    $stockQty = $product->track_stock
                        ? ($activeVariant ? $activeVariant->stock_quantity : $product->stock_quantity)
                        : null;
                @endphp
                @if($product->brand)
                    <p class="text-sm text-brand-gold tracking-wider uppercase mb-2">{{ $product->brand->name }}</p>
                @endif
                <h1 class="text-3xl md:text-4xl font-serif font-bold text-brand-light mb-4">{{ $product->name }}</h1>

                <div class="flex items-center gap-3 mb-6">
                    <span class="text-2xl font-medium text-brand-gold">
                        €{{ number_format((float) $priceShown, 2, ',', '.') }}
                    </span>
                    @if($compareShown)
                        <span class="text-lg text-gray-500 line-through">
                            €{{ number_format((float) $compareShown, 2, ',', '.') }}
                        </span>
                    @endif
                </div>

                <p class="text-gray-400 mb-6">{{ $product->short_description }}</p>

                <p class="text-xs text-gray-500 mb-6">SKU: {{ $activeVariant->sku ?? $product->sku }}</p>

                @if($listedVariants->count() > 0)
                    <div class="mb-8">
                        <h3 class="text-sm font-semibold tracking-wider uppercase text-gray-400 mb-3">Varianti</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($listedVariants as $variant)
                                @php $vImg = ImageHelper::getVariantMainImage($variant); @endphp
                                <label class="relative flex cursor-pointer gap-3 rounded-sm border p-3 transition {{ (int) $selectedVariantId === (int) $variant->id ? 'border-brand-gold bg-brand-gold/5' : 'border-white/10 hover:border-white/20' }}">
                                    <input type="radio" wire:model.live="selectedVariantId" value="{{ $variant->id }}" class="sr-only">
                                    @if($vImg)
                                        <span class="h-16 w-16 shrink-0 overflow-hidden rounded-sm bg-brand-slate/20">
                                            <img src="{{ $vImg }}" alt="" class="h-full w-full object-cover">
                                        </span>
                                    @endif
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-sm font-medium text-brand-light">{{ $variant->variant_name }}</span>
                                        @php
                                            $vSale = $variant->sale_price !== null
                                                && (float) $variant->sale_price > 0
                                                && (float) $variant->sale_price < (float) $variant->regular_price;
                                            $vPrice = $vSale ? $variant->sale_price : $variant->regular_price;
                                        @endphp
                                        <span class="mt-1 block text-sm text-brand-gold">€{{ number_format((float) $vPrice, 2, ',', '.') }}</span>
                                        @if($product->track_stock)
                                            <span class="mt-1 block text-xs text-gray-500">{{ $variant->stock_quantity > 0 ? $variant->stock_quantity.' disponibili' : 'Non disponibile' }}</span>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('variant')
                            <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                @if($product->attributes->count() > 0)
                    <div class="space-y-4 mb-8">
                        @foreach($product->attributes as $attribute)
                            <div>
                                <label class="text-sm text-gray-400 mb-2 block">{{ $attribute->name }}</label>
                                <select wire:model.live="selectedAttributes.{{ $attribute->name }}"
                                        class="w-full px-3 py-2 bg-brand-slate/20 border border-white/10 text-brand-light text-sm rounded-sm focus:outline-none focus:border-brand-gold transition-colors appearance-none cursor-pointer">
                                    <option value="">Seleziona...</option>
                                    @foreach($attribute->values as $value)
                                        <option value="{{ $value }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                        @if(array_filter($this->selectedAttributes))
                            <button wire:click="$set('selectedAttributes', [])"
                                    class="text-xs text-brand-gold hover:text-brand-light transition underline">
                                Svuota selezione
                            </button>
                        @endif
                    </div>
                @endif

                <div class="flex gap-4 mb-8">
                    <div class="flex items-center border border-white/10 rounded-sm">
                        <button wire:click="if($quantity > 1) $quantity--"
                                class="px-4 py-3 text-brand-light hover:text-brand-gold transition">-</button>
                        <span class="px-4 py-3 text-brand-light min-w-[3rem] text-center">{{ $quantity }}</span>
                        <button wire:click="$quantity++"
                                class="px-4 py-3 text-brand-light hover:text-brand-gold transition">+</button>
                    </div>
                    <button wire:click="addToCart"
                            x-data="{ adding: false }"
                            x-on:click="adding = true; setTimeout(() => adding = false, 600)"
                            :class="adding ? 'adding' : ''"
                            class="add-to-cart-btn flex-1 px-8 py-3 bg-brand-gold text-brand-dark text-sm font-semibold tracking-widest uppercase hover:bg-opacity-90 transition rounded-sm">
                        <span x-show="!adding">Aggiungi al Carrello</span>
                        <span x-show="adding" x-cloak>Aggiunto!</span>
                    </button>
                </div>

                @if($product->track_stock && $stockQty !== null)
                    <p class="text-sm {{ $stockQty > 0 ? 'text-green-400' : 'text-red-400' }}">
                        {{ $stockQty > 0 ? 'Disponibile (' . $stockQty . ' pezzi)' : 'Non disponibile' }}
                    </p>
                @endif
            </div>
        </div>

        <!-- Tabs: Description, Info, Reviews -->
        <div class="mt-16" x-data="{ tab: 'description' }">
            <div class="border-b border-white/10">
                <nav class="flex gap-8">
                    <button @click="tab = 'description'"
                            :class="tab === 'description' ? 'text-brand-gold border-brand-gold' : 'text-gray-400 border-transparent'"
                            class="pb-4 text-sm font-semibold tracking-wider uppercase border-b-2 transition">
                        Descrizione
                    </button>
                    <button @click="tab = 'info'"
                            :class="tab === 'info' ? 'text-brand-gold border-brand-gold' : 'text-gray-400 border-transparent'"
                            class="pb-4 text-sm font-semibold tracking-wider uppercase border-b-2 transition">
                        Informazioni Aggiuntive
                    </button>
                    <button @click="tab = 'reviews'"
                            :class="tab === 'reviews' ? 'text-brand-gold border-brand-gold' : 'text-gray-400 border-transparent'"
                            class="pb-4 text-sm font-semibold tracking-wider uppercase border-b-2 transition">
                        Recensioni ({{ $product->reviews->count() }})
                    </button>
                </nav>
            </div>

            <div class="py-8">
                <div x-show="tab === 'description'" class="prose prose-invert max-w-none">
                    {!! $product->description !!}
                </div>

                <div x-show="tab === 'info'" x-cloak>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="border-b border-white/5 pb-3">
                            <dt class="text-sm text-gray-500">SKU</dt>
                            <dd class="text-brand-light">{{ $product->sku }}</dd>
                        </div>
                        @if($product->brand)
                        <div class="border-b border-white/5 pb-3">
                            <dt class="text-sm text-gray-500">Marca</dt>
                            <dd class="text-brand-light">{{ $product->brand->name }}</dd>
                        </div>
                        @endif
                        @foreach($product->attributes as $attr)
                        <div class="border-b border-white/5 pb-3">
                            <dt class="text-sm text-gray-500">{{ $attr->name }}</dt>
                            <dd class="text-brand-light">{{ implode(', ', $attr->values) }}</dd>
                        </div>
                        @endforeach
                    </dl>
                </div>

                <div x-show="tab === 'reviews'" x-cloak>
                    @if(session('review_submitted'))
                        <div class="mb-6 border border-brand-gold/30 bg-brand-gold/10 px-4 py-3 text-sm text-brand-light rounded-sm">
                            {{ session('review_submitted') }}
                        </div>
                    @endif

                    @if($product->reviews->count() > 0)
                        <div class="space-y-6">
                            @foreach($product->reviews as $review)
                                <div class="border-b border-white/5 pb-6">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="flex text-brand-gold">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="w-4 h-4 {{ $i <= $review->rating ? 'fill-current' : 'text-gray-600' }}" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            @endfor
                                        </div>
                                        <span class="text-sm text-gray-400">{{ $review->user_name }}</span>
                                        <span class="text-xs text-gray-600">{{ $review->created_at->format('d/m/Y') }}</span>
                                    </div>
                                    @if($review->comment)
                                        <p class="text-sm text-gray-300">{{ $review->comment }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-400">Nessuna recensione ancora. Sii il primo!</p>
                    @endif

                    @if($product->enable_reviews)
                        <div class="mt-10 border-t border-white/10 pt-8">
                            <h3 class="text-xl font-serif text-brand-light mb-4">Lascia una recensione</h3>

                            @guest
                                <p class="text-sm text-gray-400">
                                    Devi <a href="{{ route('auth') }}" class="text-brand-gold hover:text-brand-light transition">accedere</a> e aver acquistato questo prodotto per lasciare una recensione.
                                </p>
                            @elseif($alreadyReviewedProduct)
                                <p class="text-sm text-gray-400">Hai gia inviato una recensione per questo prodotto.</p>
                            @elseif(! $canReviewProduct)
                                <p class="text-sm text-gray-400">Puoi recensire solo prodotti acquistati con un ordine pagato.</p>
                            @else
                                <form wire:submit.prevent="submitReview" class="max-w-2xl space-y-5">
                                    <div>
                                        <label class="block text-xs font-semibold tracking-widest uppercase text-gray-400 mb-2">Valutazione</label>
                                        <select wire:model="reviewRating"
                                                class="w-full sm:w-48 px-3 py-2 bg-brand-slate/20 border border-white/10 text-brand-light text-sm rounded-sm focus:outline-none focus:border-brand-gold transition-colors">
                                            @for($i = 5; $i >= 1; $i--)
                                                <option value="{{ $i }}">{{ $i }} stelle</option>
                                            @endfor
                                        </select>
                                        @error('reviewRating')<p class="text-red-400 text-xs mt-2">{{ $message }}</p>@enderror
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold tracking-widest uppercase text-gray-400 mb-2">Commento</label>
                                        <textarea wire:model.defer="reviewComment"
                                                  rows="4"
                                                  class="w-full px-4 py-3 bg-brand-slate/20 border border-white/10 text-brand-light placeholder-gray-500 text-sm rounded-sm focus:outline-none focus:border-brand-gold transition-colors resize-none"
                                                  placeholder="Racconta la tua esperienza con questo prodotto"></textarea>
                                        @error('reviewComment')<p class="text-red-400 text-xs mt-2">{{ $message }}</p>@enderror
                                        @error('review')<p class="text-red-400 text-xs mt-2">{{ $message }}</p>@enderror
                                    </div>

                                    <button type="submit"
                                            class="inline-flex items-center justify-center px-6 py-3 bg-brand-gold text-brand-dark text-xs font-semibold tracking-widest uppercase hover:bg-opacity-90 transition rounded-sm">
                                        Invia Recensione
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if($relatedProducts->count() > 0)
            <div class="mt-16">
                <h2 class="text-2xl font-serif font-bold text-brand-light mb-8">Prodotti Correlati</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($relatedProducts as $related)
                        <x-product-card :product="$related" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
