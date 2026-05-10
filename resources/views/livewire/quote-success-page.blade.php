<div class="max-w-3xl mx-auto py-12 px-4">
    <!-- Success Checkmark -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Richiesta Inviata!</h1>
        <p class="text-gray-600">La tua richiesta di preventivo è stata registrata con successo.</p>
    </div>

    <!-- Success Message -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="text-center mb-6">
            <p class="text-lg font-medium text-gray-900">Numero Preventivo: <span class="text-blue-600">{{ $order->order_number }}</span></p>
            <p class="text-sm text-gray-600 mt-1">Data richiesta: {{ $order->created_at->format('d/m/Y H:i') }}</p>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <h3 class="font-semibold text-blue-900 mb-2">Cosa succede adesso?</h3>
            <ol class="list-decimal list-inside space-y-1 text-sm text-blue-800">
                <li>Il nostro team riceverà la tua richiesta</li>
                <li>Analizzeremo i prodotti e le tue esigenze</li>
                <li>Riceverai il preventivo dettagliato via email entro 24-48 ore</li>
                <li>Il preventivo includerà prezzi finali, tempi di consegna e condizioni</li>
            </ol>
        </div>

        <!-- Customer Info -->
        <div class="space-y-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="font-medium text-gray-600">Nome:</span>
                    <div class="text-gray-900">{{ $order->customer_name }}</div>
                </div>
                <div>
                    <span class="font-medium text-gray-600">Email:</span>
                    <div class="text-gray-900">{{ $order->customer_email }}</div>
                </div>
                @if($order->customer_phone)
                <div>
                    <span class="font-medium text-gray-600">Telefono:</span>
                    <div class="text-gray-900">{{ $order->customer_phone }}</div>
                </div>
                @endif
                @if($order->customer?->company_name)
                <div>
                    <span class="font-medium text-gray-600">Azienda:</span>
                    <div class="text-gray-900">{{ $order->customer->company_name }}</div>
                </div>
                @endif
            </div>

            <div class="text-sm">
                <span class="font-medium text-gray-600">Indirizzo:</span>
                <div class="text-gray-900">{{ $order->shipping_address['address'] ?? '' }},</div>
                <div class="text-gray-900">{{ $order->shipping_address['city'] ?? '' }} ({{ $order->shipping_address['zip_code'] ?? '' }}),</div>
                <div class="text-gray-900">{{ $order->shipping_address['state'] ?? '' }}, {{ $order->shipping_address['country'] ?? '' }}</div>
            </div>

            @if($order->notes)
            <div class="text-sm">
                <span class="font-medium text-gray-600">Note aggiuntive:</span>
                <div class="text-gray-900 bg-gray-50 p-2 rounded mt-1">{{ $order->notes }}</div>
            </div>
            @endif
        </div>

        <!-- Products Ordered -->
        <div class="border-t pt-4">
            <h3 class="font-semibold text-gray-900 mb-3">Prodotti Richiesti</h3>
            <div class="space-y-2">
                @foreach($order->items as $item)
                <div class="flex justify-between text-sm bg-gray-50 p-2 rounded">
                    <div>
                        <span class="font-medium">{{ $item->product_name }}</span>
                        <span class="text-gray-500">x{{ $item->quantity }}</span>
                        @if($item->attribute_selection)
                        <span class="text-gray-400 text-xs ml-2">({{ json_encode($item->attribute_selection) }})</span>
                        @endif
                    </div>
                    <div class="text-gray-600">€{{ number_format($item->unit_price, 2, ',', '.') }}</div>
                </div>
                @endforeach
            </div>

            <div class="flex justify-between items-center font-bold text-lg mt-3 pt-3 border-t">
                <span>Totale Stimato:</span>
                <span>€{{ number_format($order->total, 2, ',', '.') }}</span>
            </div>
            <p class="text-xs text-gray-500 text-right mt-1">* Prezzo finale da confermare nel preventivo</p>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex flex-col sm:flex-row gap-3 justify-center">
        @if($order->quote_pdf_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($order->quote_pdf_path))
        <a href="{{ route('quote.download', $order->order_number) }}" class="bg-green-600 text-white px-6 py-3 rounded-md text-center hover:bg-green-700 flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Scarica Preventivo
        </a>
        @endif
        <a href="{{ route('shop') }}" class="bg-gray-600 text-white px-6 py-3 rounded-md text-center hover:bg-gray-700">
            Continua lo Shopping
        </a>
        <a href="{{ route('home') }}" class="bg-blue-600 text-white px-6 py-3 rounded-md text-center hover:bg-blue-700">
            Torna alla Home
        </a>
    </div>

    <!-- Support Info -->
    <div class="text-center mt-8 text-sm text-gray-500">
        <p>Hai domande? Contattaci all'indirizzo: <a href="mailto:preventivi@example.com" class="text-blue-600 hover:underline">preventivi@example.com</a></p>
        <p class="mt-1">Oppure chiamaci: <span class="font-medium">+39 02 1234567</span></p>
    </div>
</div>