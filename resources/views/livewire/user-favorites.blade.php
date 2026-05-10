<div>
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">My Favorites</h3>
                    <p class="mt-1 text-sm text-gray-600">Products you've saved for later.</p>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
                    {{ count($favorites) }} items
                </span>
            </div>

            <div class="px-6 py-4">
                @if(count($favorites) > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($favorites as $favorite)
                            @if($favorite->product)
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h4 class="font-medium text-gray-900">
                                                <a href="{{ route('product.show', $favorite->product->slug) }}" class="hover:text-indigo-600">
                                                    {{ $favorite->product->name }}
                                                </a>
                                            </h4>
                                            @if($favorite->product->brand)
                                                <p class="text-sm text-gray-500">{{ $favorite->product->brand->name }}</p>
                                            @endif
                                            <p class="mt-2 text-lg font-semibold text-gray-900">
                                                €{{ number_format($favorite->product->price, 2) }}
                                            </p>
                                        </div>
                                        <button wire:click="removeFavorite({{ $favorite->id }})"
                                                class="ml-2 text-red-600 hover:text-red-800 transition-colors"
                                                title="Remove from favorites">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No favorites yet</h3>
                        <p class="mt-1 text-sm text-gray-500">Start adding products to your favorites list.</p>
                        <div class="mt-6">
                            <a href="{{ route('shop') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Browse Products
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>