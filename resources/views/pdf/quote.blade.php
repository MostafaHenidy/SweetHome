PREVENTIVO / QUOTATION
═══════════════════════════════════════════════════════════════════════════

Numero Preventivo: {{ $order->order_number }}
Data: {{ $order->created_at->format('d/m/Y H:i') }}
@if($order->quote_expires_at)
Valido fino a: {{ $order->quote_expires_at->format('d/m/Y H:i') }}
@endif

SweetHome Italia
Via Roma 123, 20100 Milano
Tel: +39 02 1234567 | Email: info@sweethome.it
P.IVA: IT12345678901

───────────────────────────────────────────────────────────────────────────
INFORMAZIONI CLIENTE
───────────────────────────────────────────────────────────────────────────
Nome: {{ $order->customer_name ?? $customer->name ?? '' }}
Email: {{ $order->customer_email ?? $customer->email ?? '' }}
Telefono: {{ $order->customer_phone ?? $customer->phone ?? '' }}
@if($customer && $customer->company_name)
Azienda: {{ $customer->company_name }}
@endif

───────────────────────────────────────────────────────────────────────────
INDIRIZZO DI SPEDIZIONE
───────────────────────────────────────────────────────────────────────────
@if(is_array($order->shipping_address))
{{ $order->shipping_address['address'] ?? '' }}, {{ $order->shipping_address['city'] ?? '' }}
{{ $order->shipping_address['state'] ?? '' }} {{ $order->shipping_address['zip_code'] ?? '' }}
{{ $order->shipping_address['country'] ?? '' }}
@else
{{ $order->shipping_address }}
@endif

───────────────────────────────────────────────────────────────────────────
DETTAGLI PRODOTTI
───────────────────────────────────────────────────────────────────────────
PRODOTTO                         QTA      PREZZO       TOTALE
───────────────────────────────────────────────────────────────────────────
@foreach($items as $item)
{{ str_pad(substr($item->product_name ?? 'Prodotto', 0, 30), 30) }}{{ str_pad((string)($item->quantity ?? 1), 8) }}€{{ str_pad(number_format($item->unit_price ?? 0, 2, ',', '.'), 10) }}€{{ str_pad(number_format(($item->total_price ?? (($item->quantity ?? 1) * ($item->unit_price ?? 0))), 2, ',', '.'), 10) }}
@endforeach

───────────────────────────────────────────────────────────────────────────

RIEPILOGO TOTALE
───────────────────────────────────────────────────────────────────────────
Subtotale:                                                                    €{{ number_format($order->subtotal ?? $items->sum('total_price'), 2, ',', '.') }}
@if($order->shipping_cost > 0)
Spese di spedizione:                                                           €{{ number_format($order->shipping_cost, 2, ',', '.') }}
@endif
@if($order->tax > 0)
IVA:                                                                          €{{ number_format($order->tax, 2, ',', '.') }}
@endif
@if($order->discount_amount > 0)
Sconto:                                                                      -€{{ number_format($order->discount_amount, 2, ',', '.') }}
@endif
TOTALE:                                                                       €{{ number_format($order->total, 2, ',', '.') }}

@if($order->notes)
───────────────────────────────────────────────────────────────────────────
NOTE
───────────────────────────────────────────────────────────────────────────
{{ $order->notes }}
@endif

═══════════════════════════════════════════════════════════════════════════
⚠️ Valido per 30 giorni dalla data di emissione
Questo preventivo è soggetto a termini e condizioni.
Per accettare questo preventivo, rispondi a questa email o contattaci.

TERMINI E CONDIZIONI:
• Prezzi validi per 30 giorni
• Pagamento: 50% all'ordine, 50% alla consegna
• Tempi di consegna: 3-4 settimane dalla conferma ordine
• Spese di trasporto a carico del cliente (salvo diverso accordo)

Per accettare il preventivo o fare domande:
Email: preventivi@sweethome.it
Tel: +39 02 1234567

Generato automaticamente da SweetHome Milano
═══════════════════════════════════════════════════════════════════════════