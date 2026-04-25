<div class="bg-[#11161b] text-[#f3ede3]">
    <section class="relative overflow-hidden border-b border-white/5 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=1920&q=80');">
        <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/55 to-black/20"></div>
        <div class="relative mx-auto flex min-h-[460px] max-w-7xl items-center px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
            <div class="max-w-2xl">
                <p class="text-xs uppercase tracking-[0.45em] text-[#d8b36a]">SweetHome Milano</p>
                <h1 class="mt-5 font-serif text-5xl leading-[0.92] text-white sm:text-6xl lg:text-7xl">
                    IL LUSSO
                    <br>
                    DI ABITARE.
                </h1>
                <p class="mt-5 max-w-lg text-sm leading-7 text-white/78 sm:text-base">
                    Lo showroom per la tua casa del sogno. Materiali esclusivi e design su misura.
                </p>
                <a href="#catalog" class="mt-8 inline-flex items-center rounded-sm bg-[#b89252] px-5 py-3 text-xs font-semibold uppercase tracking-[0.3em] text-[#f3ede3] transition hover:bg-[#c8a35f]">
                    Scopri la collezione
                </a>
            </div>
        </div>
    </section>

    <section id="catalog" class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
        <div class="grid gap-8 lg:grid-cols-[260px_minmax(0,1fr)]">
            <aside class="border border-white/5 bg-[#24384a] p-6 lg:p-8">
                <div class="space-y-8 lg:sticky lg:top-28">
                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-[0.3em] text-[#d8b36a]">Cerca</h3>
                        <input type="text" wire:model.live.debounce.300ms="search"
                               placeholder="Cerca prodotti..."
                               class="mt-3 w-full rounded-sm border border-white/10 bg-black/15 px-3 py-2 text-sm text-[#f3ede3] placeholder:text-white/35 focus:border-[#d8b36a] focus:outline-none">
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-[0.3em] text-[#d8b36a]">Categorie</h3>
                        <ul class="mt-3 space-y-2 text-sm">
                            <li>
                                <button wire:click="$set('category', '')" class="text-left text-[#f3ede3] transition hover:text-[#d8b36a] {{ !$category ? 'font-semibold text-[#d8b36a]' : '' }}">
                                    Tutte
                                </button>
                            </li>
                            @foreach($categories as $cat)
                                <li>
                                    <button wire:click="$set('category', '{{ $cat->slug }}')" class="text-left transition hover:text-[#d8b36a] {{ $category === $cat->slug ? 'font-semibold text-[#d8b36a]' : 'text-white/70' }}">
                                        {{ $cat->name }}
                                    </button>
                                    @if($cat->children->count() > 0 && $category === $cat->slug)
                                        <ul class="mt-2 space-y-1 pl-4 text-xs">
                                            @foreach($cat->children as $child)
                                                <li>
                                                    <button wire:click="$set('category', '{{ $child->slug }}')" class="text-left transition hover:text-[#d8b36a] {{ $category === $child->slug ? 'font-semibold text-[#d8b36a]' : 'text-white/50' }}">
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
                        <h3 class="text-sm font-semibold uppercase tracking-[0.3em] text-[#d8b36a]">Marchi</h3>
                        <ul class="mt-3 max-h-48 space-y-2 overflow-y-auto text-sm">
                            @foreach($brands as $b)
                                <li>
                                    <button wire:click="$set('brand', {{ $b->id }})" class="text-left transition hover:text-[#d8b36a] {{ $b->id == $brand ? 'font-semibold text-[#d8b36a]' : 'text-white/70' }}">
                                        {{ $b->name }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-[0.3em] text-[#d8b36a]">Prezzo</h3>
                        <div class="mt-3 flex gap-2">
                            <input type="number" wire:model.live.debounce.300ms="minPrice"
                                   placeholder="Min"
                                   class="w-full rounded-sm border border-white/10 bg-black/15 px-3 py-2 text-sm text-[#f3ede3] placeholder:text-white/35 focus:border-[#d8b36a] focus:outline-none">
                            <input type="number" wire:model.live.debounce.300ms="maxPrice"
                                   placeholder="Max"
                                   class="w-full rounded-sm border border-white/10 bg-black/15 px-3 py-2 text-sm text-[#f3ede3] placeholder:text-white/35 focus:border-[#d8b36a] focus:outline-none">
                        </div>
                    </div>

                    @if($search || $category || $brand || $minPrice || $maxPrice)
                        <button wire:click="clearFilters" class="text-left text-sm text-[#d8b36a] underline underline-offset-4 transition hover:text-[#f3ede3]">
                            Cancella filtri
                        </button>
                    @endif
                </div>
            </aside>

            <div class="min-w-0">
                <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-white/55">{{ $products->total() }} prodotti trovati</p>
                    <select wire:model.live="sort" class="w-full rounded-sm border border-white/10 bg-black/15 px-3 py-2 text-sm text-[#f3ede3] focus:border-[#d8b36a] focus:outline-none sm:w-64">
                        <option value="newest">Più recenti</option>
                        <option value="price_asc">Prezzo: dal più economico</option>
                        <option value="price_desc">Prezzo: dal più caro</option>
                        <option value="name_asc">Nome: A-Z</option>
                        <option value="name_desc">Nome: Z-A</option>
                    </select>
                </div>

                @if($products->count() > 0)
                    <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach($products as $product)
                            <x-product-card :product="$product" />
                        @endforeach
                    </div>
                    <div class="mt-12">
                        {{ $products->links() }}
                    </div>
                @else
                    <div class="rounded-sm border border-white/5 bg-black/15 px-6 py-16 text-center">
                        <svg class="mx-auto mb-4 h-16 w-16 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                        </svg>
                        <h3 class="font-serif text-xl text-[#f3ede3]">Nessun prodotto trovato</h3>
                        <p class="mt-2 text-sm text-white/55">Prova a modificare i filtri di ricerca.</p>
                        <button wire:click="clearFilters" class="mt-6 inline-flex items-center rounded-sm bg-[#b89252] px-6 py-3 text-xs font-semibold uppercase tracking-[0.3em] text-[#f3ede3]">
                            Cancella filtri
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>
