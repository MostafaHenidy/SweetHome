<div>
    @if($quoteRequests->count() > 0)
        <div class="space-y-4">
            @foreach($quoteRequests as $quote)
                <div class="bg-brand-slate/10 border border-white/5 rounded-sm p-6">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div>
                            <h3 class="text-lg font-serif text-brand-light">
                                Preventivo #{{ $quote->order_number }}
                            </h3>
                            <p class="text-sm text-gray-400 mt-1">
                                {{ $quote->created_at->format('d/m/Y H:i') }}
                                @if($quote->quote_expires_at)
                                    — Scadenza: {{ $quote->quote_expires_at->format('d/m/Y') }}
                                @endif
                            </p>
                        </div>

                        <div class="flex items-center gap-4">
                            <span class="px-3 py-1 text-xs font-semibold rounded-sm {{ match($quote->status) {
                                'pending' => 'bg-yellow-500/20 text-yellow-400',
                                'quote_sent' => 'bg-blue-500/20 text-blue-400',
                                'quote_accepted' => 'bg-green-500/20 text-green-400',
                                'quote_rejected' => 'bg-red-500/20 text-red-400',
                                'expired' => 'bg-gray-500/20 text-gray-400',
                                default => 'bg-gray-500/20 text-gray-400',
                            } }}">
                                {{ match($quote->status) {
                                    'pending' => 'In Attesa',
                                    'quote_sent' => 'Preventivo Inviato',
                                    'quote_accepted' => 'Accettato',
                                    'quote_rejected' => 'Rifiutato',
                                    'expired' => 'Scaduto',
                                    default => ucfirst($quote->status),
                                } }}
                            </span>

                            @if($quote->total > 0)
                                <span class="text-brand-gold font-bold">
                                    €{{ number_format($quote->total, 2, ',', '.') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-white/5">
                        <p class="text-sm text-gray-400">
                            {{ $quote->items->count() }} articoli —
                            @foreach($quote->items->take(3) as $item)
                                {{ $item->product_name }}@if(!$loop->last), @endif
                            @endforeach
                            @if($quote->items->count() > 3) e altri {{ $quote->items->count() - 3 }} @endif
                        </p>

                        <div class="mt-3 flex gap-3">
                            @if($quote->quote_pdf_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($quote->quote_pdf_path))
                                <a href="{{ route('quote.download', $quote->order_number) }}"
                                   class="text-sm text-brand-gold hover:underline">
                                    📄 Scarica Preventivo PDF
                                </a>
                            @endif

                            @if($quote->canBeAccepted())
                                <a href="{{ route('checkout', ['quote' => $quote->order_number]) }}"
                                   class="text-sm text-green-400 hover:underline">
                                    ✅ Accetta e Procedi all'ordine
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $quoteRequests->links() }}
        </div>
    @else
        <div class="text-center py-16">
            <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="text-xl font-serif text-brand-light mb-2">Nessun preventivo richiesto</h3>
            <p class="text-gray-400 mb-6">Non hai ancora richiesto preventivi.</p>
            <a href="{{ route('request-quote') }}"
               class="inline-block px-6 py-3 bg-brand-gold text-brand-dark text-sm font-semibold tracking-widest uppercase hover:bg-opacity-90 transition rounded-sm">
                Richiedi un Preventivo
            </a>
        </div>
    @endif
</div>