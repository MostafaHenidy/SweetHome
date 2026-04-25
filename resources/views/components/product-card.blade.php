@props(['product'])

<a href="{{ route('product.show', $product->slug) }}" class="group block">
    <div class="overflow-hidden bg-[#24384a] shadow-[0_12px_30px_rgba(0,0,0,0.18)] ring-1 ring-white/5 transition duration-300 group-hover:-translate-y-0.5 group-hover:ring-[#b89252]/40">
        <div class="relative aspect-[4/5] overflow-hidden">
            @if((bool) ($product->has_active_discount ?? false))
                <span class="absolute left-3 top-3 z-10 bg-brand-gold px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-brand-dark">
                    Sconto
                </span>
            @endif
            @if($product->hasMedia('images'))
                <img
                    src="{{ $product->getFirstMediaUrl('images', 'medium') }}"
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
    </div>
    <div class="space-y-2 px-1 pt-4">
        @if($product->brand)
            <p class="text-[11px] uppercase tracking-[0.35em] text-white/55">{{ $product->brand->name }}</p>
        @endif
        <h3 class="font-serif text-lg leading-snug text-white">
            {{ $product->name }}
        </h3>
        <div class="flex items-center justify-between gap-3">
            <span class="text-sm text-white/75">€{{ number_format($product->price, 2, ',', '.') }} / mq</span>
            <span class="inline-flex items-center rounded-sm bg-[#b89252] px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-[#f3ede3]">
                Aggiungi al carrello
            </span>
        </div>
    </div>
</a>
