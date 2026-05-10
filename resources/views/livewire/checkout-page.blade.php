<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-3xl md:text-4xl font-serif font-bold text-brand-light mb-8">Checkout</h1>

        <div class="flex items-center mb-12">
            <div class="flex items-center {{ $step >= 1 ? 'text-brand-gold' : 'text-gray-600' }}">
                <span class="w-8 h-8 rounded-full border-2 flex items-center justify-center text-sm font-bold {{ $step >= 1 ? 'border-brand-gold bg-brand-gold text-brand-dark' : 'border-gray-600' }}">1</span>
                <span class="ml-2 text-sm hidden sm:inline">Dati</span>
            </div>
            <div class="flex-1 h-px mx-4 {{ $step >= 2 ? 'bg-brand-gold' : 'bg-gray-700' }}"></div>
            <div class="flex items-center {{ $step >= 2 ? 'text-brand-gold' : 'text-gray-600' }}">
                <span class="w-8 h-8 rounded-full border-2 flex items-center justify-center text-sm font-bold {{ $step >= 2 ? 'border-brand-gold bg-brand-gold text-brand-dark' : 'border-gray-600' }}">2</span>
                <span class="ml-2 text-sm hidden sm:inline">Spedizione</span>
            </div>
            <div class="flex-1 h-px mx-4 {{ $step >= 3 ? 'bg-brand-gold' : 'bg-gray-700' }}"></div>
            <div class="flex items-center {{ $step >= 3 ? 'text-brand-gold' : 'text-gray-600' }}">
                <span class="w-8 h-8 rounded-full border-2 flex items-center justify-center text-sm font-bold {{ $step >= 3 ? 'border-brand-gold bg-brand-gold text-brand-dark' : 'border-gray-600' }}">3</span>
                <span class="ml-2 text-sm hidden sm:inline">Pagamento</span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                @if($step === 1)
                    <div class="bg-brand-slate/10 border border-white/5 rounded-sm p-6">
                        <h2 class="text-xl font-serif text-brand-light mb-6">Informazioni Cliente</h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm text-gray-400 mb-1">Nome e Cognome *</label>
                                <input type="text" wire:model="customer_name" class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light rounded-sm focus:outline-none focus:border-brand-gold transition @error('customer_name') border-red-500 @enderror">
                                @error('customer_name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm text-gray-400 mb-1">Email *</label>
                                    <input type="email" wire:model="customer_email" class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light rounded-sm focus:outline-none focus:border-brand-gold transition @error('customer_email') border-red-500 @enderror">
                                    @error('customer_email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-400 mb-1">Telefono</label>
                                    <input type="tel" wire:model="customer_phone" class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light rounded-sm focus:outline-none focus:border-brand-gold transition">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-400 mb-1">Ragione sociale (opzionale)</label>
                                <input type="text" wire:model="customer_company_name" class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light rounded-sm focus:outline-none focus:border-brand-gold transition @error('customer_company_name') border-red-500 @enderror">
                                @error('customer_company_name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm text-gray-400 mb-1">P.IVA (opzionale)</label>
                                    <input type="text" wire:model="customer_vat_number" class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light rounded-sm focus:outline-none focus:border-brand-gold transition @error('customer_vat_number') border-red-500 @enderror">
                                    @error('customer_vat_number')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-400 mb-1">Codice fiscale (opzionale)</label>
                                    <input type="text" wire:model="customer_tax_code" class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light rounded-sm focus:outline-none focus:border-brand-gold transition @error('customer_tax_code') border-red-500 @enderror">
                                    @error('customer_tax_code')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>
                        <button wire:click="nextStep" class="mt-6 w-full px-6 py-3 bg-brand-gold text-brand-dark text-sm font-semibold tracking-widest uppercase hover:bg-opacity-90 transition rounded-sm">Continua</button>
                    </div>
                @endif

                @if($step === 2)
                    <div class="bg-brand-slate/10 border border-white/5 rounded-sm p-6">
                        <h2 class="text-xl font-serif text-brand-light mb-6">Metodo di Consegna</h2>

                        {{-- Shipping Method Selection --}}
                        <div class="space-y-3 mb-6">
                            <label class="flex items-center gap-3 p-4 border border-white/10 rounded-sm cursor-pointer hover:border-brand-gold/50 transition {{ $shipping_method === 'delivery' ? 'border-brand-gold/50 bg-brand-gold/5' : '' }}">
                                <input type="radio" wire:model="shipping_method" value="delivery" class="text-brand-gold">
                                <div>
                                    <span class="text-brand-light text-sm font-medium">Consegna al mio indirizzo</span>
                                    <p class="text-xs text-gray-500">Spedizione presso il tuo indirizzo</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-4 border border-white/10 rounded-sm cursor-pointer hover:border-brand-gold/50 transition {{ $shipping_method === 'pickup' ? 'border-brand-gold/50 bg-brand-gold/5' : '' }}">
                                <input type="radio" wire:model="shipping_method" value="pickup" class="text-brand-gold">
                                <div>
                                    <span class="text-brand-light text-sm font-medium">Ritiro in sede</span>
                                    <p class="text-xs text-gray-500">Ritira l'ordine presso la nostra sede</p>
                                </div>
                            </label>
                        </div>

                        {{-- Service Type Selection (only for delivery) --}}
                        @if($shipping_method === 'delivery')
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-brand-light mb-3">Tipo di Servizio</h3>
                            <div class="space-y-3">
                                <label class="flex items-center gap-3 p-4 border border-white/10 rounded-sm cursor-pointer hover:border-brand-gold/50 transition {{ $service_type === 'shipping_only' ? 'border-brand-gold/50 bg-brand-gold/5' : '' }}">
                                    <input type="radio" wire:model="service_type" value="shipping_only" class="text-brand-gold">
                                    <div>
                                        <span class="text-brand-light text-sm font-medium">Solo Spedizione</span>
                                        <p class="text-xs text-gray-500">Prodotti consegnati, montaggio fai-da-te</p>
                                    </div>
                                </label>
                                <label class="flex items-center gap-3 p-4 border border-white/10 rounded-sm cursor-pointer hover:border-brand-gold/50 transition {{ $service_type === 'shipping_and_construction' ? 'border-brand-gold/50 bg-brand-gold/5' : '' }}">
                                    <input type="radio" wire:model="service_type" value="shipping_and_construction" class="text-brand-gold">
                                    <div>
                                        <span class="text-brand-light text-sm font-medium">Spedizione + Montaggio</span>
                                        <p class="text-xs text-gray-500">Consegna e montaggio professionale inclusi</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                        @endif

                        {{-- Shipping Address (only for delivery) --}}
                        @if($shipping_method === 'delivery')
                        <div class="space-y-4 mb-6">
                            <h3 class="text-lg font-medium text-brand-light">Indirizzo di Spedizione</h3>
                            <div>
                                <label class="block text-sm text-gray-400 mb-1">Indirizzo *</label>
                                <input type="text" wire:model="shipping_address_line1" class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light rounded-sm focus:outline-none focus:border-brand-gold transition @error('shipping_address_line1') border-red-500 @enderror">
                                @error('shipping_address_line1')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm text-gray-400 mb-1">Indirizzo (opzionale)</label>
                                <input type="text" wire:model="shipping_address_line2" class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light rounded-sm focus:outline-none focus:border-brand-gold transition">
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm text-gray-400 mb-1">Città *</label>
                                    <input type="text" wire:model="shipping_city" class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light rounded-sm focus:outline-none focus:border-brand-gold transition @error('shipping_city') border-red-500 @enderror">
                                    @error('shipping_city')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-400 mb-1">Provincia *</label>
                                    <input type="text" wire:model="shipping_province" class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light rounded-sm focus:outline-none focus:border-brand-gold transition @error('shipping_province') border-red-500 @enderror">
                                    @error('shipping_province')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-400 mb-1">CAP *</label>
                                    <input type="text" wire:model="shipping_postal_code" class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light rounded-sm focus:outline-none focus:border-brand-gold transition @error('shipping_postal_code') border-red-500 @enderror">
                                    @error('shipping_postal_code')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Pickup Location (only for pickup) --}}
                        @if($shipping_method === 'pickup')
                        <div class="space-y-4 mb-6">
                            <h3 class="text-lg font-medium text-brand-light">Sede di Ritiro</h3>
                            <div>
                                <label class="block text-sm text-gray-400 mb-1">Seleziona sede *</label>
                                <select wire:model="pickup_location" class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light rounded-sm focus:outline-none focus:border-brand-gold transition @error('pickup_location') border-red-500 @enderror">
                                    <option value="">Seleziona una sede</option>
                                    <option value="main_warehouse">Magazzino Principale - Milano</option>
                                    <option value="rome_showroom">Showroom Roma</option>
                                    <option value="naples_warehouse">Magazzino Napoli</option>
                                </select>
                                @error('pickup_location')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div class="bg-brand-dark/30 rounded-sm p-4 text-sm text-gray-400">
                                <p>📍 Magazzino Principale - Milano</p>
                                <p>Via delle Industrie, 123</p>
                                <p>20138 Milano (MI)</p>
                                <p class="mt-2">🕐 Lun-Ven: 9:00-18:00, Sab: 9:00-13:00</p>
                            </div>
                        </div>
                        @endif

                        <div class="flex gap-4 mt-6">
                            <button wire:click="$set('step', 1)" class="px-6 py-3 border border-white/20 text-brand-light text-sm font-semibold tracking-widest uppercase hover:border-brand-gold transition rounded-sm">Indietro</button>
                            <button wire:click="nextStep" class="flex-1 px-6 py-3 bg-brand-gold text-brand-dark text-sm font-semibold tracking-widest uppercase hover:bg-opacity-90 transition rounded-sm">Continua</button>
                        </div>
                    </div>
                @endif

                @if($step === 3)
                    <div class="bg-brand-slate/10 border border-white/5 rounded-sm p-6">
                        <h2 class="text-xl font-serif text-brand-light mb-6">Metodo di Pagamento</h2>

                        {{-- Payment Plan Summary --}}
                        <div class="bg-brand-dark/30 rounded-sm p-4 mb-6">
                            <h3 class="text-sm font-medium text-brand-gold mb-3">Piano di Pagamento</h3>
                            <div class="space-y-2 text-sm">
                                @foreach($this->paymentPlanInstallments as $installment)
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center space-x-2">
                                        <span class="w-5 h-5 rounded-full bg-brand-gold text-brand-dark text-xs flex items-center justify-center font-bold">
                                            {{ $installment['number'] }}
                                        </span>
                                        <span class="text-gray-300">{{ $installment['stage_label'] }} ({{ $installment['percentage'] }}%)</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-gray-300">€{{ number_format($installment['amount'], 2, ',', '.') }}</span>
                                        @if($installment['pay_now'])
                                            <span class="ml-2 text-xs text-green-400">- Pagare ora</span>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div class="border-t border-white/10 mt-3 pt-3 flex justify-between">
                                <span class="text-gray-400">Totale Ordine:</span>
                                <span class="text-white font-semibold">€{{ number_format($this->total, 2, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <label class="flex items-center gap-3 p-4 border border-white/10 rounded-sm cursor-pointer hover:border-brand-gold/50 transition {{ $payment_method === 'fawaterak' ? 'border-brand-gold/50 bg-brand-gold/5' : '' }}">
                                <input type="radio" wire:model="payment_method" value="fawaterak" class="text-brand-gold">
                                <div>
                                    <span class="text-brand-light text-sm font-medium">Fawaterak</span>
                                    <p class="text-xs text-gray-500">Pagamento sicuro via Fawaterak</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-4 border border-white/10 rounded-sm cursor-pointer hover:border-brand-gold/50 transition {{ $payment_method === 'stripe' ? 'border-brand-gold/50 bg-brand-gold/5' : '' }}">
                                <input type="radio" wire:model="payment_method" value="stripe" class="text-brand-gold">
                                <div>
                                    <span class="text-brand-light text-sm font-medium">Carta di Credito (Stripe)</span>
                                    <p class="text-xs text-gray-500">Visa, Mastercard, American Express</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-4 border border-white/10 rounded-sm cursor-pointer hover:border-brand-gold/50 transition {{ $payment_method === 'paypal' ? 'border-brand-gold/50 bg-brand-gold/5' : '' }}">
                                <input type="radio" wire:model="payment_method" value="paypal" class="text-brand-gold">
                                <div>
                                    <span class="text-brand-light text-sm font-medium">PayPal</span>
                                    <p class="text-xs text-gray-500">Paga con il tuo account PayPal</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-4 border border-white/10 rounded-sm cursor-pointer hover:border-brand-gold/50 transition {{ $payment_method === 'bank_transfer' ? 'border-brand-gold/50 bg-brand-gold/5' : '' }}">
                                <input type="radio" wire:model="payment_method" value="bank_transfer" class="text-brand-gold">
                                <div>
                                    <span class="text-brand-light text-sm font-medium">Bonifico Bancario</span>
                                    <p class="text-xs text-gray-500">Pagamento anticipato tramite bonifico</p>
                                </div>
                            </label>
                        </div>
                        <div class="flex gap-4 mt-6">
                            <button wire:click="$set('step', 2)" class="px-6 py-3 border border-white/20 text-brand-light text-sm font-semibold tracking-widest uppercase hover:border-brand-gold transition rounded-sm">Indietro</button>
                            <button wire:click="placeOrder" class="flex-1 px-6 py-3 bg-brand-gold text-brand-dark text-sm font-semibold tracking-widest uppercase hover:bg-opacity-90 transition rounded-sm">Conferma Ordine</button>
                        </div>
                    </div>
                @endif
            </div>

            <div class="lg:col-span-1">
                <div class="bg-brand-slate/10 border border-white/5 rounded-sm p-6 sticky top-24">
                    <h2 class="text-lg font-serif text-brand-light mb-6">Il Tuo Ordine</h2>
                    <div class="space-y-3 mb-6 max-h-64 overflow-y-auto">
                        @foreach($cart as $item)
                            <div class="flex justify-between text-sm">
                                <div>
                                    <span class="text-brand-light">{{ $item['name'] }}</span>
                                    <span class="text-gray-500"> × {{ $item['quantity'] }}</span>
                                </div>
                                <span class="text-brand-gold">€{{ number_format($item['price'] * $item['quantity'], 2, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="border-t border-white/10 pt-4 space-y-2">
                        <div class="space-y-2 pb-3">
                            @if($applied_coupon)
                                <div class="flex items-center justify-between rounded-sm border border-brand-gold/30 bg-brand-gold/5 px-3 py-2 text-sm">
                                    <span class="text-brand-light">{{ $applied_coupon['code'] }}</span>
                                    <button wire:click="removeCoupon" class="text-xs uppercase tracking-wider text-brand-gold hover:text-brand-light">Rimuovi</button>
                                </div>
                            @else
                                <div class="flex gap-2">
                                    <input type="text" wire:model.defer="coupon_code" placeholder="Coupon"
                                           class="min-w-0 flex-1 px-3 py-2 bg-brand-dark/50 border border-white/10 text-brand-light placeholder-gray-500 text-sm rounded-sm focus:outline-none focus:border-brand-gold transition">
                                    <button wire:click="applyCoupon" class="px-4 py-2 bg-brand-gold text-brand-dark text-xs font-semibold uppercase tracking-wider rounded-sm">Applica</button>
                                </div>
                                @if($coupon_error)
                                    <p class="text-xs text-red-400">{{ $coupon_error }}</p>
                                @endif
                            @endif
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400">Subtotale</span>
                            <span class="text-brand-light">€{{ number_format($this->subtotal, 2, ',', '.') }}</span>
                        </div>
                        @if($this->discountAmount > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-400">Sconto</span>
                                <span class="text-green-400">-€{{ number_format($this->discountAmount, 2, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400">Spedizione</span>
                            <span class="text-brand-light">
                                @if($this->shippingCost == 0)<span class="text-green-400">Gratuita</span>@else
                                    €{{ number_format($this->shippingCost, 2, ',', '.') }}
                                @endif
                            </span>
                        </div>
                        <div class="border-t border-white/10 pt-3 flex justify-between">
                            <span class="text-base font-semibold text-brand-light">Totale</span>
                            <span class="text-xl font-bold text-brand-gold">€{{ number_format($this->total, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
