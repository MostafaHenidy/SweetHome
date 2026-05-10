<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-3xl md:text-4xl font-serif font-bold text-brand-light mb-8">Carrello</h1>

        @if(count($cart) > 0)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-4">
                    @foreach($cart as $key => $item)
                        <div class="flex gap-4 p-4 bg-brand-slate/10 rounded-sm border border-white/5">
                            <div class="w-24 h-24 flex-shrink-0 rounded-sm overflow-hidden bg-brand-slate/20">
                                @if(!empty($item['image']))
                                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <h3 class="text-base font-serif text-brand-light">{{ $item['name'] }}</h3>
                                @if(!empty($item['attributes']) && array_filter($item['attributes']))
                                    <p class="text-xs text-gray-500 mt-1">
                                        @foreach($item['attributes'] as $name => $value)
                                            {{ $name }}: {{ $value }}@if(!$loop->last), @endif
                                        @endforeach
                                    </p>
                                @endif
                                <p class="text-brand-gold mt-2">€{{ number_format($item['price'], 2, ',', '.') }}</p>
                            </div>
                            <div class="flex flex-col items-end justify-between">
                                <button wire:click="removeItem('{{ $key }}')" class="text-gray-500 hover:text-red-400 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                                <div class="flex items-center border border-white/10 rounded-sm">
                                    <button wire:click="updateQuantity('{{ $key }}', {{ $item['quantity'] - 1 }})" class="px-3 py-1 text-brand-light hover:text-brand-gold transition text-sm">−</button>
                                    <span class="px-3 py-1 text-brand-light text-sm min-w-[2rem] text-center">{{ $item['quantity'] }}</span>
                                    <button wire:click="updateQuantity('{{ $key }}', {{ $item['quantity'] + 1 }})" class="px-3 py-1 text-brand-light hover:text-brand-gold transition text-sm">+</button>
                                </div>
                                <p class="text-sm text-brand-gold font-medium">€{{ number_format($item['price'] * $item['quantity'], 2, ',', '.') }}</p>
                            </div>
                        </div>
                    @endforeach

                    <div class="flex justify-between pt-4">
                        <button wire:click="clearCart" class="text-sm text-gray-400 hover:text-red-400 transition underline">Svuota carrello</button>
                        <a href="{{ route('shop') }}" class="text-sm text-brand-gold hover:text-brand-light transition">← Continua lo shopping</a>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <div class="bg-brand-slate/10 border border-white/5 rounded-sm p-6 sticky top-24">
                        <h2 class="text-lg font-serif text-brand-light mb-6">Riepilogo Ordine</h2>
                        <div class="mb-6">
                            @livewire('cart.coupon-input')
                        </div>
                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-400">Subtotale</span>
                                <span class="text-brand-light">€{{ number_format($this->subtotal, 2, ',', '.') }}</span>
                            </div>
                            @if($this->discountAmount > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-400">Sconto</span>
                                    <span class="text-green-400">-€{{ number_format($this->discountAmount, 2, ',', '.') }}</span>
                                </div>
                            @else
                                {{-- Debug: Show when discount is 0 --}}
                                {{-- @if(config('app.debug'))
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-400">Sconto (debug)</span>
                                        <span class="text-red-400">{{ $this->discountAmount }}</span>
                                    </div>
                                @endif --}}
                            @endif
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-400">Spedizione</span>
                                <span class="text-brand-light">Calcolata al checkout</span>
                            </div>
                        </div>
                        <div class="border-t border-white/10 pt-4 mb-6">
                            <div class="flex justify-between">
                                <span class="text-base font-semibold text-brand-light">Totale</span>
                                <span class="text-xl font-bold text-brand-gold">€{{ number_format($this->total, 2, ',', '.') }}</span>
                            </div>
                        </div>
                        <a href="{{ route('checkout') }}" class="block w-full text-center px-6 py-3 bg-brand-gold text-brand-dark text-sm font-semibold tracking-widest uppercase hover:bg-opacity-90 transition rounded-sm mb-3">Procedi al Checkout</a>
                        <a href="{{ route('request-quote') }}" class="block w-full text-center px-6 py-3 bg-blue-600 text-white text-sm font-semibold tracking-widest uppercase hover:bg-blue-700 transition rounded-sm">Richiedi Preventivo</a>
                        <p class="text-xs text-gray-400 text-center mt-3">Checkout per acquisto diretto, Preventivo per preventivo personalizzato</p>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-20">
                <svg class="w-20 h-20 mx-auto text-gray-600 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                </svg>
                <h3 class="text-xl font-serif text-brand-light mb-2">Il tuo carrello è vuoto</h3>
                <p class="text-gray-400 mb-8">Aggiungi qualche prodotto per iniziare.</p>
                <a href="{{ route('shop') }}" class="inline-block px-8 py-3 bg-brand-gold text-brand-dark text-sm font-semibold tracking-widest uppercase hover:bg-opacity-90 transition rounded-sm">Vai allo Shop</a>
            </div>
        @endif
    </div>
</div>
