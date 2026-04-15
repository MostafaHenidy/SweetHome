@props(['product'])

<a href="{{ route('product.show', $product->slug) }}" class="group block">
    <div class="relative overflow-hidden rounded-sm bg-brand-slate/10">
        <div class="aspect-square overflow-hidden">
            @if($product->hasMedia('images'))
                <img src="{{ $product->getFirstMediaUrl('images', 'medium') }}"
                     alt="{{ $product->name }}"
                     loading="lazy"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
            @else
                <div class="w-full h-full bg-brand-slate/20 flex items-center justify-center">
                    <svg class="w-16 h-16 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"/>
                    </svg>
                </div>
            @endif
        </div>
        @if($product->compare_price && $product->compare_price > $product->price)
            <span class="absolute top-3 left-3 bg-brand-gold text-brand-dark text-xs font-bold px-2 py-1 rounded-sm">
                -{{ round((1 - $product->price / $product->compare_price) * 100) }}%
            </span>
        @endif
    </div>
    <div class="mt-4">
        @if($product->brand)
            <p class="text-xs text-gray-500 tracking-wider uppercase">{{ $product->brand->name }}</p>
        @endif
        <h3 class="text-base font-serif text-brand-light group-hover:text-brand-gold transition-colors mt-1">
            {{ $product->name }}
        </h3>
        <div class="mt-2 flex items-center gap-2">
            <span class="text-lg font-medium text-brand-gold">
                €{{ number_format($product->price, 2, ',', '.') }}
            </span>
            @if($product->compare_price && $product->compare_price > $product->price)
                <span class="text-sm text-gray-500 line-through">
                    €{{ number_format($product->compare_price, 2, ',', '.') }}
                </span>
            @endif
        </div>
    </div>
</a>
