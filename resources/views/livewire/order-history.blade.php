<div>
    @if($orders->count() > 0)
        <div class="space-y-4">
            @foreach($orders as $order)
                <div class="bg-brand-slate/10 border border-white/5 rounded-sm p-6">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div>
                            <h3 class="text-lg font-serif text-brand-light">
                                Ordine #{{ $order->order_number }}
                            </h3>
                            <p class="text-sm text-gray-400 mt-1">
                                {{ $order->created_at->format('d/m/Y H:i') }}
                            </p>
                        </div>

                        <div class="flex items-center gap-4">
                            <span class="px-3 py-1 text-xs font-semibold rounded-sm {{ match($order->status) {
                                'pending' => 'bg-yellow-500/20 text-yellow-400',
                                'processing' => 'bg-blue-500/20 text-blue-400',
                                'shipped' => 'bg-purple-500/20 text-purple-400',
                                'completed' => 'bg-green-500/20 text-green-400',
                                'cancelled' => 'bg-red-500/20 text-red-400',
                                default => 'bg-gray-500/20 text-gray-400',
                            } }}">
                                {{ match($order->status) {
                                    'pending' => 'In Attesa',
                                    'processing' => 'In Lavorazione',
                                    'shipped' => 'Spedito',
                                    'completed' => 'Completato',
                                    'cancelled' => 'Annullato',
                                    default => ucfirst($order->status),
                                } }}
                            </span>

                            <span class="text-brand-gold font-bold">
                                €{{ number_format($order->total, 2, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-white/5">
                        <p class="text-sm text-gray-400">
                            {{ $order->items->count() }} articoli —
                            @foreach($order->items->take(3) as $item)
                                {{ $item->product_name }}@if(!$loop->last), @endif
                            @endforeach
                            @if($order->items->count() > 3) e altri {{ $order->items->count() - 3 }} @endif
                        </p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $orders->links() }}
        </div>
    @else
        <div class="text-center py-16">
            <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <h3 class="text-xl font-serif text-brand-light mb-2">Nessun ordine</h3>
            <p class="text-gray-400 mb-6">Non hai ancora effettuato ordini.</p>
            <a href="{{ route('shop') }}"
               class="inline-block px-6 py-3 bg-brand-gold text-brand-dark text-sm font-semibold tracking-widest uppercase hover:bg-opacity-90 transition rounded-sm">
                Vai allo Shop
            </a>
        </div>
    @endif
</div>
