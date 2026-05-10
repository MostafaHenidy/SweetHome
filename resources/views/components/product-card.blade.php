@props(['product'])

<div class="group relative flex h-full flex-col">
    <a href="{{ route('product.show', $product->slug) }}" class="block flex-1 min-h-0">
        <div class="flex min-h-0 flex-col overflow-hidden shadow-[0_12px_30px_rgba(0,0,0,0.18)] ring-1 ring-white/5 transition duration-300 group-hover:-translate-y-0.5 group-hover:ring-[#b89252]/40">
            <div class="relative aspect-[4/5] overflow-hidden bg-[#24384a]">
                @if((bool) ($product->has_active_discount ?? false))
                    <span class="absolute left-3 top-3 z-10 bg-brand-gold px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-brand-dark">
                        Sconto
                    </span>
                @endif
                <div class="absolute right-2 top-2 z-10">
                    @auth
                        @livewire('product-favorite-button', ['productId' => $product->id], key('fav-btn-' . $product->id))
                    @else
                        <button
                            type="button"
                            onclick="window.location.href='{{ route('login') }}'"
                            class="p-2 rounded-full bg-white/10 text-gray-400 hover:text-red-500 hover:bg-white/20 transition-colors"
                            title="Login to add favorites"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </button>
                    @endauth
                </div>
                @if($product->hasMedia('images') || $product->featured_image)
                    <img
                        src="{{ $product->getFirstMediaUrl('images', 'medium') ?: $product->featured_image }}"
                        alt="{{ $product->name }}"
                        class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                        loading="lazy"
                    >
                @else
                    <div class="flex h-full w-full items-center justify-center bg-[#223445] text-white/30">
                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"/>
                        </svg>
                    </div>
                @endif
            </div>
            <div class="flex-1 bg-[#11161b] space-y-2 px-1 pt-4 pb-4">
                @if($product->brand)
                    <p class="text-[11px] uppercase tracking-[0.35em] text-white/55">{{ $product->brand->name }}</p>
                @endif
                <h3 class="font-serif text-lg leading-snug text-white">
                    {{ $product->name }}
                </h3>
                <div class="flex items-center justify-between gap-3">
                    <span class="text-sm text-white/75">€{{ number_format($product->price, 2, ',', '.') }} / mq</span>
                </div>
            </div>
        </div>
    </a>
    <div class="mt-auto px-1 pb-1">
        @livewire('add-to-cart', ['productId' => $product->id], key('add-to-cart-' . $product->id))
    </div>
</div>
