<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-10">
        <h1 class="text-3xl md:text-4xl font-serif font-bold text-brand-light">Richiedi Preventivo</h1>
        <p class="text-gray-400 mt-3">Compila il form per ricevere un preventivo personalizzato per i prodotti nel carrello.</p>
    </div>

    <div class="flex items-center mb-10">
        <div class="flex items-center {{ $step >= 1 ? 'text-brand-gold' : 'text-gray-600' }}">
            <span class="w-8 h-8 rounded-full border-2 flex items-center justify-center text-sm font-bold {{ $step >= 1 ? 'border-brand-gold bg-brand-gold text-brand-dark' : 'border-gray-600' }}">1</span>
            <span class="ml-2 text-sm hidden sm:inline">Contatti</span>
        </div>
        <div class="flex-1 h-px mx-4 {{ $step >= 2 ? 'bg-brand-gold' : 'bg-gray-700' }}"></div>
        <div class="flex items-center {{ $step >= 2 ? 'text-brand-gold' : 'text-gray-600' }}">
            <span class="w-8 h-8 rounded-full border-2 flex items-center justify-center text-sm font-bold {{ $step >= 2 ? 'border-brand-gold bg-brand-gold text-brand-dark' : 'border-gray-600' }}">2</span>
            <span class="ml-2 text-sm hidden sm:inline">Indirizzo</span>
        </div>
        <div class="flex-1 h-px mx-4 {{ $step >= 3 ? 'bg-brand-gold' : 'bg-gray-700' }}"></div>
        <div class="flex items-center {{ $step >= 3 ? 'text-brand-gold' : 'text-gray-600' }}">
            <span class="w-8 h-8 rounded-full border-2 flex items-center justify-center text-sm font-bold {{ $step >= 3 ? 'border-brand-gold bg-brand-gold text-brand-dark' : 'border-gray-600' }}">3</span>
            <span class="ml-2 text-sm hidden sm:inline">Riepilogo</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 bg-brand-slate/10 border border-white/5 rounded-sm p-6">

        <!-- Step 1: Contact Information -->
        @if($step === 1)
        <div class="space-y-4">
            <h2 class="text-xl font-serif text-brand-light mb-4">Informazioni di Contatto</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Nome Completo *</label>
                    <input type="text" wire:model="customer_name" class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light rounded-sm focus:outline-none focus:border-brand-gold transition @error('customer_name') border-red-500 @enderror">
                    @error('customer_name') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-1">Email *</label>
                    <input type="email" wire:model="customer_email" class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light rounded-sm focus:outline-none focus:border-brand-gold transition @error('customer_email') border-red-500 @enderror">
                    @error('customer_email') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-1">Telefono</label>
                    <input type="tel" wire:model="customer_phone" class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light rounded-sm focus:outline-none focus:border-brand-gold transition @error('customer_phone') border-red-500 @enderror">
                    @error('customer_phone') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-1">Azienda (Opzionale)</label>
                    <input type="text" wire:model="company_name" class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light rounded-sm focus:outline-none focus:border-brand-gold transition">
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button wire:click="nextStep" class="px-6 py-3 bg-brand-gold text-brand-dark text-sm font-semibold tracking-widest uppercase hover:bg-opacity-90 transition rounded-sm disabled:opacity-50" wire:loading.attr="disabled">
                    Avanti
                </button>
            </div>
        </div>

        <!-- Step 2: Address Information -->
        @elseif($step === 2)
        <div class="space-y-4">
            <h2 class="text-xl font-serif text-brand-light mb-4">Indirizzo di Spedizione</h2>

            <div>
                <label class="block text-sm text-gray-400 mb-1">Indirizzo *</label>
                <textarea wire:model="address" rows="2" class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light rounded-sm focus:outline-none focus:border-brand-gold transition @error('address') border-red-500 @enderror"></textarea>
                @error('address') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Città *</label>
                    <input type="text" wire:model="city" class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light rounded-sm focus:outline-none focus:border-brand-gold transition @error('city') border-red-500 @enderror">
                    @error('city') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-1">Provincia *</label>
                    <input type="text" wire:model="state" class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light rounded-sm focus:outline-none focus:border-brand-gold transition @error('state') border-red-500 @enderror">
                    @error('state') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-1">CAP *</label>
                    <input type="text" wire:model="zip_code" class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light rounded-sm focus:outline-none focus:border-brand-gold transition @error('zip_code') border-red-500 @enderror">
                    @error('zip_code') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm text-gray-400 mb-1">Nazione *</label>
                <select wire:model="country" class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light rounded-sm focus:outline-none focus:border-brand-gold transition @error('country') border-red-500 @enderror">
                    <option value="IT">Italia</option>
                    <option value="US">Stati Uniti</option>
                    <option value="GB">Regno Unito</option>
                    <option value="DE">Germania</option>
                    <option value="FR">Francia</option>
                </select>
                @error('country') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm text-gray-400 mb-1">Note Aggiuntive</label>
                <textarea wire:model="notes" rows="3" placeholder="Specifica requisiti speciali, preferenze, ecc." class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light placeholder-gray-500 rounded-sm focus:outline-none focus:border-brand-gold transition"></textarea>
            </div>

            <div class="flex justify-between pt-4">
                <button wire:click="previousStep" class="px-6 py-3 border border-white/20 text-brand-light text-sm font-semibold tracking-widest uppercase hover:border-brand-gold transition rounded-sm">
                    Indietro
                </button>
                <button wire:click="nextStep" class="px-6 py-3 bg-brand-gold text-brand-dark text-sm font-semibold tracking-widest uppercase hover:bg-opacity-90 transition rounded-sm disabled:opacity-50" wire:loading.attr="disabled">
                    Avanti
                </button>
            </div>
        </div>

        <!-- Step 3: Review & Submit -->
        @elseif($step === 3)
        <div class="space-y-6">
            <h2 class="text-xl font-serif text-brand-light mb-4">Riepilogo della Richiesta</h2>

            <!-- Cart Items Summary -->
            <div class="bg-brand-dark/40 border border-white/10 rounded-sm p-4">
                <h3 class="font-medium text-brand-light mb-3">Prodotti nel Preventivo</h3>
                <div class="space-y-2">
                    @foreach($cartItems as $item)
                    <div class="flex justify-between text-sm">
                        <div>
                            <span class="font-medium">{{ data_get($item, 'product.name', data_get($item, 'name', 'Prodotto')) }}</span>
                            <span class="text-gray-400">x{{ data_get($item, 'quantity', 0) }}</span>
                            @if(!empty(data_get($item, 'attributes')))
                            <span class="text-gray-500 text-xs">({{ json_encode(data_get($item, 'attributes')) }})</span>
                            @endif
                        </div>
                        <div class="font-medium text-brand-gold">€{{ number_format((float) data_get($item, 'total_price', 0), 2, ',', '.') }}</div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Customer Info Summary -->
            <div class="bg-brand-dark/40 border border-white/10 rounded-sm p-4">
                <h3 class="font-medium text-brand-light mb-3">Informazioni Cliente</h3>
                <div class="text-sm space-y-1">
                    <p><span class="text-gray-400">Nome:</span> {{ $customer_name }}</p>
                    <p><span class="text-gray-400">Email:</span> {{ $customer_email }}</p>
                    @if($customer_phone)<p><span class="text-gray-400">Telefono:</span> {{ $customer_phone }}</p>@endif
                    @if($company_name)<p><span class="text-gray-400">Azienda:</span> {{ $company_name }}</p>@endif
                    <p><span class="text-gray-400">Indirizzo:</span> {{ $address }}, {{ $city }} ({{ $zip_code }}), {{ $state }}, {{ $country }}</p>
                    @if($notes)<p><span class="text-gray-400">Note:</span> {{ $notes }}</p>@endif
                </div>
            </div>

            <!-- Totals -->
            <div class="bg-brand-slate/20 border border-white/10 rounded-sm p-4">
                <h3 class="font-medium text-brand-light mb-2">Totale Stimato</h3>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-400">Subtotale:</span>
                    <span class="font-medium text-brand-light">€{{ number_format($this->subtotal, 2, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center text-sm text-gray-400">
                    <span>Spedizione:</span>
                    <span>Da definire</span>
                </div>
                <div class="flex justify-between items-center text-sm text-gray-400">
                    <span>Tasse:</span>
                    <span>Da definire</span>
                </div>
                <div class="flex justify-between items-center font-bold text-lg text-brand-gold mt-2 pt-2 border-t border-white/10">
                    <span>Totale:</span>
                    <span>€{{ number_format($this->total, 2, ',', '.') }}</span>
                </div>
                <div class="text-xs text-gray-400 mt-1 text-center">
                    * I prezzi finali saranno confermati dopo la revisione del preventivo
                </div>
            </div>

            @if($errors->has('submit'))
            <div class="bg-red-500/10 border border-red-500/30 text-red-300 px-4 py-3 rounded-sm">
                {{ $errors->first('submit') }}
            </div>
            @endif

            <div class="flex justify-between pt-4">
                <button wire:click="previousStep" class="px-6 py-3 border border-white/20 text-brand-light text-sm font-semibold tracking-widest uppercase hover:border-brand-gold transition rounded-sm">
                    Indietro
                </button>
                <button wire:click="submitQuoteRequest" class="px-6 py-3 bg-brand-gold text-brand-dark text-sm font-semibold tracking-widest uppercase hover:bg-opacity-90 transition rounded-sm disabled:opacity-50" wire:loading.attr="disabled">
                    Invia Richiesta Preventivo
                </button>
            </div>
        </div>
        @endif
        </div>

        <div class="lg:col-span-1">
            <div class="bg-brand-slate/10 border border-white/5 rounded-sm p-6 sticky top-24">
                <h3 class="text-lg font-serif text-brand-light mb-4">Riepilogo Carrello</h3>
                @if(empty($cartItems))
                <p class="text-gray-400">Il carrello è vuoto.</p>
                <a href="{{ route('cart') }}" class="text-sm text-brand-gold hover:text-brand-light mt-2 inline-block">Vai al carrello</a>
                @else
                <div class="space-y-2 mb-4 max-h-64 overflow-y-auto">
                    @foreach($cartItems as $item)
                    <div class="flex justify-between text-sm">
                        <span class="text-brand-light">{{ data_get($item, 'product.name', data_get($item, 'name', 'Prodotto')) }} (x{{ data_get($item, 'quantity', 0) }})</span>
                        <span class="text-brand-gold">€{{ number_format((float) data_get($item, 'total_price', 0), 2, ',', '.') }}</span>
                    </div>
                    @endforeach
                </div>
                <div class="border-t border-white/10 pt-3 flex justify-between font-semibold">
                    <span class="text-brand-light">Totale:</span>
                    <span class="text-brand-gold">€{{ number_format($this->subtotal, 2, ',', '.') }}</span>
                </div>
                <a href="{{ route('cart') }}" class="text-sm text-brand-gold hover:text-brand-light mt-3 inline-block">Modifica carrello</a>
                @endif
            </div>
        </div>
    </div>
</div>