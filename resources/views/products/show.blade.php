@php
    use App\Helpers\ImageHelper;
@endphp

@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Product Images Section -->
        <div class="space-y-4">
            <!-- Main Image -->
            <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                @php
                    $mainImage = ImageHelper::getProductMainImage($product);
                @endphp
                @if($mainImage)
                    <img src="{{ $mainImage }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"/>
                        </svg>
                    </div>
                @endif
            </div>

            <!-- Thumbnail Gallery -->
            @if($product->variants->count() > 0)
                <div class="grid grid-cols-4 gap-2">
                    @foreach($product->variants as $variant)
                        @php
                            $variantImage = ImageHelper::getVariantMainImage($variant);
                        @endphp
                        @if($variantImage)
                            <div class="aspect-square bg-gray-100 rounded overflow-hidden cursor-pointer hover:opacity-75 transition">
                                <img src="{{ $variantImage }}" alt="{{ $variant->variant_name }}" class="w-full h-full object-cover">
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Product Info Section -->
        <div class="space-y-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $product->name }}</h1>
                <p class="text-lg text-gray-600 mt-2">{{ $product->short_description }}</p>
            </div>

            <div class="text-2xl font-semibold text-gray-900">
                €{{ number_format($product->price, 2, ',', '.') }}
            </div>

            <!-- Variants Section -->
            @if($product->variants->count() > 0)
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-900">Varianti disponibili</h3>
                    <div class="grid grid-cols-2 gap-4">
                        @foreach($product->variants as $variant)
                            @php
                                $variantImage = ImageHelper::getVariantMainImage($variant);
                            @endphp
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-brand-gold cursor-pointer transition">
                                @if($variantImage)
                                    <img src="{{ $variantImage }}" alt="{{ $variant->variant_name }}" class="w-full h-32 object-cover rounded mb-3">
                                @endif
                                <h4 class="font-medium text-gray-900">{{ $variant->variant_name }}</h4>
                                <p class="text-brand-gold font-semibold">
                                    €{{ number_format($variant->regular_price, 2, ',', '.') }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Additional Images Section -->
    @php
        $allImages = ImageHelper::getAllProductImages($product);
        $additionalImages = array_filter($allImages, function($img) {
            return strpos($img['collection'], 'additional') === 0;
        });
    @endphp

    @if(count($additionalImages) > 0)
        <div class="mt-12">
            <h3 class="text-xl font-medium text-gray-900 mb-6">Immagini aggiuntive</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($additionalImages as $image)
                    <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                        <img src="{{ $image['url'] }}" alt="{{ $image['name'] }}" class="w-full h-full object-cover hover:scale-105 transition duration-300">
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection