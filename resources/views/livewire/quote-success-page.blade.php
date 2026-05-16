<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
    <div class="bg-[#11161b] border border-white/10 shadow-2xl rounded-sm overflow-hidden">
        <div class="px-6 py-10 sm:px-12 sm:py-12">
            <div class="max-w-2xl mx-auto text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-brand-gold/10 text-brand-gold mx-auto mb-6">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h1 class="text-4xl font-serif font-bold text-brand-light tracking-tight mb-3">Richiesta Inviata!</h1>
                <p class="text-gray-400 text-base sm:text-lg">La tua richiesta di preventivo è stata ricevuta. Il nostro team la sta elaborando.</p>
            </div>

            <div class="mt-12 grid gap-6 lg:grid-cols-[1fr_320px]">
                <div class="space-y-6">
                    <div class="bg-brand-slate/10 border border-white/10 rounded-sm p-6">
                        <p class="text-sm uppercase tracking-[0.24em] text-brand-gold mb-3">Dettagli Richiesta</p>
                        <div class="space-y-2 text-sm text-white/80">
                            <div class="flex justify-between gap-4">
                                <span class="font-medium text-white/90">Numero Preventivo</span>
                                <span class="text-brand-gold font-semibold">{{ $order->order_number }}</span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="font-medium text-white/90">Data richiesta</span>
                                <span>{{ $order->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="rounded-sm bg-white/5 p-4 text-sm text-white/70">
                                <p class="font-medium text-white/90 mb-2">Cosa succede adesso?</p>
                                <ol class="list-decimal list-inside space-y-1 text-sm text-white/70">
                                    <li>Il nostro team riceverà la tua richiesta</li>
                                    <li>Analizzeremo i prodotti e le tue esigenze</li>
                                    <li>Riceverai il preventivo via email entro 24-48 ore</li>
                                    <li>Il preventivo includerà prezzi, tempi di consegna e condizioni</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white/5 border border-white/10 rounded-sm p-6">
                        <h2 class="text-lg font-semibold text-brand-light mb-4">Informazioni Cliente</h2>
                        <div class="grid gap-4 sm:grid-cols-2 text-sm text-white/80">
                            <div>
                                <p class="text-gray-400">Nome</p>
                                <p class="mt-1 text-white">{{ $order->customer_name }}</p>
                            </div>
                            <div>
                                <p class="text-gray-400">Email</p>
                                <p class="mt-1 text-white">{{ $order->customer_email }}</p>
                            </div>
                            @if($order->customer_phone)
                            <div>
                                <p class="text-gray-400">Telefono</p>
                                <p class="mt-1 text-white">{{ $order->customer_phone }}</p>
                            </div>
                            @endif
                            @if($order->customer?->company_name)
                            <div>
                                <p class="text-gray-400">Azienda</p>
                                <p class="mt-1 text-white">{{ $order->customer->company_name }}</p>
                            </div>
                            @endif
                        </div>

                        <div class="mt-5 rounded-sm bg-[#11161b] border border-white/10 p-4 text-sm text-white/80">
                            <p class="text-gray-400">Indirizzo</p>
                            <p class="mt-1 text-white">{{ $order->shipping_address['address'] ?? '' }},</p>
                            <p class="text-white">{{ $order->shipping_address['city'] ?? '' }} ({{ $order->shipping_address['zip_code'] ?? '' }}),</p>
                            <p class="text-white">{{ $order->shipping_address['state'] ?? '' }}, {{ $order->shipping_address['country'] ?? '' }}</p>
                        </div>

                        @if($order->notes)
                        <div class="mt-4 rounded-sm bg-white/5 border border-white/10 p-4 text-sm text-white/80">
                            <p class="text-gray-400">Note aggiuntive</p>
                            <p class="mt-1 text-white">{{ $order->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white/5 border border-white/10 rounded-sm p-6">
                        <h2 class="text-lg font-semibold text-brand-light mb-4">Prodotti Richiesti</h2>
                        <div class="space-y-3">
                            @foreach($order->items as $item)
                            <div class="flex flex-col gap-2 rounded-sm border border-white/10 bg-[#0f1418] p-4 text-sm text-white/80">
                                <div class="flex flex-wrap justify-between gap-3">
                                    <div>
                                        <p class="font-medium text-white">{{ $item->product_name }}</p>
                                        @if($item->attribute_selection)
                                        <p class="text-xs text-white/60">{{ json_encode($item->attribute_selection) }}</p>
                                        @endif
                                    </div>
                                    <p class="font-semibold text-brand-gold">€{{ number_format($item->unit_price, 2, ',', '.') }}</p>
                                </div>
                                <p class="text-gray-400">Quantità: {{ $item->quantity }}</p>
                            </div>
                            @endforeach
                        </div>

                        <div class="mt-6 border-t border-white/10 pt-4 flex items-center justify-between text-white/90 text-lg font-semibold">
                            <span>Totale Stimato</span>
                            <span class="text-brand-gold">€{{ number_format($order->total, 2, ',', '.') }}</span>
                        </div>
                        <p class="text-xs text-white/50 text-right mt-2">* Prezzo finale da confermare nel preventivo</p>
                    </div>

                    <div class="bg-[#0f1418] border border-white/10 rounded-sm p-6 text-sm text-white/80">
                        <h3 class="font-semibold text-brand-light mb-3">Supporto</h3>
                        <p>Hai domande? Scrivici a <a href="mailto:preventivi@example.com" class="text-brand-gold hover:text-white">preventivi@example.com</a></p>
                        <p class="mt-2">Oppure chiamaci al <span class="text-white">+39 02 1234567</span></p>
                    </div>
                </div>
            </div>

            <div class="mt-10 flex flex-col gap-3 sm:flex-row sm:justify-center">
                @if($order->quote_pdf_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($order->quote_pdf_path))
                <a href="{{ route('quote.download', $order->order_number) }}" class="inline-flex items-center justify-center rounded-sm bg-brand-gold px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-brand-dark hover:bg-opacity-90 transition">
                    Scarica Preventivo
                </a>
                @endif

                <a href="{{ route('shop') }}" class="inline-flex items-center justify-center rounded-sm border border-white/10 bg-white/5 px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-white hover:border-brand-gold transition">
                    Continua lo Shopping
                </a>
                <a href="{{ route('home') }}" class="inline-flex items-center justify-center rounded-sm border border-white/10 bg-white/5 px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-white hover:border-brand-gold transition">
                    Torna alla Home
                </a>
            </div>
        </div>
    </div>
</div>
