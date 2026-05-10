<div class="space-y-4">
    <div class="text-center">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $coupon->code }}</h3>
        <p class="text-sm text-gray-500">
            {{ $coupon->type === 'fixed_points' ? $coupon->value . ' punti' : 'Coupon per prodotto' }}
        </p>
        <p class="text-xs text-gray-400 mt-1">
            Max uses: {{ $coupon->max_uses ?? '∞' }} | Usato: {{ $coupon->used_count }}
        </p>
    </div>

    <div class="bg-white p-4 rounded-lg border border-gray-200">
        <img src="{{ $qrCode }}" alt="QR Code for {{ $coupon->code }}" class="w-full max-w-xs mx-auto">
    </div>

    <div class="bg-gray-50 p-4 rounded-lg">
        <h4 class="font-medium text-gray-900 mb-2">Come usare questo QR code:</h4>
        <ol class="text-sm text-gray-600 space-y-1 list-decimal list-inside">
            <li>Scarica o stampa questo QR code</li>
            <li>Condividi con i tuoi clienti</li>
            <li>I clienti scansionano con il telefono</li>
            <li>I punti vengono aggiunti automaticamente</li>
        </ol>
    </div>

    <div class="flex justify-center space-x-3">
        <a href="{{ $qrCode }}" download="qr-{{ $coupon->code }}.svg"
           class="px-4 py-2 bg-brand-gold text-brand-dark rounded hover:bg-brand-gold/90 transition">
            Download QR Code
        </a>
    </div>

    @if($coupon->type !== 'fixed_points')
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <p class="text-yellow-800 text-sm">
                <strong>Attenzione:</strong> QR codes sono disponibili solo per i coupon di tipo "Punti fissi".
                Questo coupon è di tipo "{{ $coupon->type }}" e non può essere usato con QR code.
            </p>
        </div>
    @endif
</div>