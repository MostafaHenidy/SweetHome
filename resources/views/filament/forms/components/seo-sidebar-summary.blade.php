@php
    $status = $get('status');
    $publishedAt = $get('published_at');
    $stock = $get('stock_quantity');
    $price = $get('price');
    $hasVariants = $get('has_variants');
    $isFeatured = $get('is_featured');
    $isNewArrival = $get('is_new_arrival');
    $isBestSeller = $get('is_best_seller');
    $isOnSale = $get('is_on_sale');
    $isLimited = $get('is_limited');
    $isExclusive = $get('is_exclusive');
    $freeShipping = $get('free_shipping');
    $images = $get('images') ?? [];

    $now = now();
    $scheduled = $publishedAt && \Carbon\Carbon::parse($publishedAt)->isFuture();
@endphp
<div class="space-y-2 text-xs">
    {{-- Publication Status --}}
    <div class="flex items-center gap-2">
        <span class="text-gray-500 w-20">Stato</span>
        @if($status)
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                Pubblicato
            </span>
        @else
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                Bozza
            </span>
        @endif
    </div>

    {{-- Scheduled --}}
    @if($scheduled)
        <div class="flex items-center gap-2">
            <span class="text-gray-500 w-20">Programmata</span>
            <span class="text-gray-700 font-medium">
                {{ \Carbon\Carbon::parse($publishedAt)->format('d/m/Y H:i') }}
            </span>
        </div>
    @endif

    {{-- Price --}}
    @if($price)
        <div class="flex items-center gap-2">
            <span class="text-gray-500 w-20">Prezzo</span>
            <span class="text-gray-900 font-semibold">€{{ number_format((float) $price, 2, ',', '.') }}</span>
        </div>
    @endif

    {{-- Stock --}}
    @if($stock !== null && $stock !== '')
        <div class="flex items-center gap-2">
            <span class="text-gray-500 w-20">Giacenza</span>
            @if((int) $stock === 0)
                <span class="text-red-600 font-medium">Esaurito</span>
            @elseif((int) $stock < 5)
                <span class="text-yellow-600 font-medium">{{ $stock }} pz</span>
            @else
                <span class="text-green-600 font-medium">{{ $stock }} pz</span>
            @endif
        </div>
    @endif

    {{-- Variants indicator --}}
    @if($hasVariants)
        <div class="flex items-center gap-2">
            <span class="text-gray-500 w-20">Tipo</span>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                Variabile
            </span>
        </div>
    @endif

    {{-- Shipping --}}
    @if($freeShipping)
        <div class="flex items-center gap-2">
            <span class="text-gray-500 w-20">Spedizione</span>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                Gratuita
            </span>
        </div>
    @endif

    {{-- Featured --}}
    @if($isFeatured)
        <div class="flex items-center gap-2">
            <span class="text-gray-500 w-20">Evidenza</span>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                In Evidenza
            </span>
        </div>
    @endif

    {{-- Labels --}}
    @php
        $labels = [];
        if ($isNewArrival) $labels[] = ['label' => 'Nuovo', 'bg' => 'bg-teal-100', 'text' => 'text-teal-800'];
        if ($isBestSeller) $labels[] = ['label' => 'Best Seller', 'bg' => 'bg-orange-100', 'text' => 'text-orange-800'];
        if ($isOnSale) $labels[] = ['label' => 'In Sconto', 'bg' => 'bg-red-100', 'text' => 'text-red-800'];
        if ($isLimited) $labels[] = ['label' => 'Limitata', 'bg' => 'bg-pink-100', 'text' => 'text-pink-800'];
        if ($isExclusive) $labels[] = ['label' => 'Esclusivo', 'bg' => 'bg-indigo-100', 'text' => 'text-indigo-800'];
    @endphp

    @if(count($labels) > 0)
        <div class="border-t border-gray-200 pt-2 mt-2">
            <span class="text-gray-500">Etichette</span>
            <div class="flex flex-wrap gap-1 mt-1">
                @foreach($labels as $l)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $l['bg'] }} {{ $l['text'] }}">
                        {{ $l['label'] }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Images count --}}
    @if(count($images) > 0)
        <div class="border-t border-gray-200 pt-2 mt-2">
            <div class="flex items-center gap-2">
                <span class="text-gray-500">Immagini</span>
                <span class="text-gray-700">{{ count($images) }} {{ count($images) === 1 ? 'foto' : 'foto' }}</span>
            </div>
        </div>
    @endif
</div>