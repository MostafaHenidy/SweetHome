<div>
    <!-- Breadcrumb -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <nav class="flex text-sm text-gray-400">
            <a href="/" class="hover:text-brand-gold transition">Home</a>
            <span class="mx-2">/</span>
            @if($currentCategory)
                @foreach($currentCategory->breadcrumb() as $cat)
                    <a href="{{ route('shop.category', $cat->slug) }}" class="hover:text-brand-gold transition">{{ $cat->name }}</a>
                    @if(!$loop->last)<span class="mx-2">/</span>@endif
                @endforeach
            @else
                <span class="text-brand-light">Shop</span>
            @endif
        </nav>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
        <div class="mb-8">
            <h1 class="text-3xl md:text-4xl font-serif font-bold text-brand-light">
                {{ $currentCategory ? $currentCategory->name : 'Tutti i Prodotti' }}
            </h1>
            @if($currentCategory?->description)
                <p class="mt-2 text-gray-400">{{ $currentCategory->description }}</p>
            @endif
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
            <aside class="lg:w-64 flex-shrink-0">
                <div class="sticky top-24 space-y-8">
                    <div>
                        <h3 class="text-sm font-semibold tracking-wider uppercase text-brand-gold mb-3">Cerca</h3>
                        <input type="text" wire:model.live.debounce.300ms="search"
                               placeholder="Cerca prodotti..."
                               class="w-full px-3 py-2 bg-brand-slate/20 border border-white/10 text-brand-light placeholder-gray-500 text-sm rounded-sm focus:outline-none focus:border-brand-gold transition-colors">
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold tracking-wider uppercase text-brand-gold mb-3">Categorie</h3>
                        <ul class="space-y-2">
                            <li>
                                <button wire:click="$set('category', '')"
                                        class="text-sm {{ !$category ? 'text-brand-gold font-semibold' : 'text-gray-400 hover:text-brand-light' }} transition">
                                    Tutte
                                </button>
                            </li>
                            @foreach($categories as $cat)
                                <li>
                                    <button wire:click="$set('category', '{{ $cat->slug }}')"
                                            class="text-sm {{ $category === $cat->slug ? 'text-brand-gold font-semibold' : 'text-gray-400 hover:text-brand-light' }} transition">
                                        {{ $cat->name }}
                                    </button>
                                    @if($cat->children->count() > 0 && $category === $cat->slug)
                                        <ul class="ml-4 mt-1 space-y-1">
                                            @foreach($cat->children as $child)
                                                <li>
                                                    <button wire:click="$set('category', '{{ $child->slug }}')"
                                                            class="text-xs {{ $category === $child->slug ? 'text-brand-gold font-semibold' : 'text-gray-500 hover:text-brand-light' }} transition">
                                                        {{ $child->name }}
                                                    </button>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold tracking-wider uppercase text-brand-gold mb-3">Marchi</h3>
                        <ul class="space-y-2 max-h-48 overflow-y-auto">
                            @foreach($brands as $b)
                                <li>
                                    <button wire:click="$set('brand', {{ $b->id }})"
                                            class="text-sm {{ $b->id == $brand ? 'text-brand-gold font-semibold' : 'text-gray-400 hover:text-brand-light' }} transition">
                                        {{ $b->name }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold tracking-wider uppercase text-brand-gold mb-3">Prezzo</h3>
                        <div class="flex gap-2">
                            <input type="number" wire:model.live.debounce.300ms="minPrice"
                                   placeholder="Min"
                                   class="w-full px-3 py-2 bg-brand-slate/20 border border-white/10 text-brand-light placeholder-gray-500 text-sm rounded-sm focus:outline-none focus:border-brand-gold transition-colors">
                            <input type="number" wire:model.live.debounce.300ms="maxPrice"
                                   placeholder="Max"
                                   class="w-full px-3 py-2 bg-brand-slate/20 border border-white/10 text-brand-light placeholder-gray-500 text-sm rounded-sm focus:outline-none focus:border-brand-gold transition-colors">
                        </div>
                    </div>

                    @if($search || $category || $brand || $minPrice || $maxPrice)
                        <button wire:click="clearFilters"
                                class="text-sm text-brand-gold hover:text-brand-light transition underline">
                            Cancella filtri
                        </button>
                    @endif
                </div>
            </aside>

            <div class="flex-1">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                    <p class="text-sm text-gray-400">{{ $products->total() }} prodotti trovati</p>
                    <select wire:model.live="sort"
                            class="px-3 py-2 bg-brand-slate/20 border border-white/10 text-brand-light text-sm rounded-sm focus:outline-none focus:border-brand-gold transition-colors appearance-none cursor-pointer">
                        <option value="newest">Più recenti</option>
                        <option value="price_asc">Prezzo: dal più economico</option>
                        <option value="price_desc">Prezzo: dal più caro</option>
                        <option value="name_asc">Nome: A-Z</option>
                        <option value="name_desc">Nome: Z-A</option>
                    </select>
                </div>

                @if($products->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($products as $product)
                            <x-product-card :product="$product" />
                        @endforeach
                    </div>
                    <div class="mt-12">{{ $products->links() }}</div>
                @else
                    <div class="text-center py-20">
                        <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                        </svg>
                        <h3 class="text-xl font-serif text-brand-light mb-2">Nessun prodotto trovato</h3>
                        <p class="text-gray-400 mb-6">Prova a modificare i filtri di ricerca.</p>
                        <button wire:click="clearFilters"
                                class="px-6 py-3 bg-brand-gold text-brand-dark text-sm font-semibold tracking-widest uppercase hover:bg-opacity-90 transition rounded-sm">
                            Cancella filtri
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
