<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
        .header { background: #1A1A1A; color: #F9F9F9; padding: 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 20px; }
        .order-info { background: #f5f5f5; padding: 15px; border-radius: 4px; margin: 15px 0; }
        .items { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .items th, .items td { padding: 8px 12px; border-bottom: 1px solid #ddd; text-align: left; }
        .items th { background: #f5f5f5; }
        .total { font-size: 18px; font-weight: bold; color: #C9A46B; }
        .footer { background: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>SweetHome Milano</h1>
        <p>Conferma Ordine</p>
    </div>

    <div class="content">
        <p>Gentile {{ $order->customer_name }},</p>
        <p>Grazie per il tuo ordine! Ecco i dettagli:</p>

        <div class="order-info">
            <p><strong>Numero Ordine:</strong> {{ $order->order_number }}</p>
            <p><strong>Data:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
            <p><strong>Stato:</strong> In attesa di pagamento</p>
        </div>

        <table class="items">
            <thead>
                <tr>
                    <th>Prodotto</th>
                    <th>Qtà</th>
                    <th>Prezzo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>€{{ number_format($item->total, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="order-info">
            <p>Subtotale: €{{ number_format($order->subtotal, 2, ',', '.') }}</p>
            <p>Spedizione: €{{ number_format($order->shipping_cost, 2, ',', '.') }}</p>
            <p class="total">Totale: €{{ number_format($order->total, 2, ',', '.') }}</p>
        </div>

        <p><strong>Indirizzo di Spedizione:</strong><br>
        {{ $order->shipping_address['line1'] }}<br>
        {{ $order->shipping_address['city'] }} {{ $order->shipping_address['postal_code'] }} ({{ $order->shipping_address['province'] }})</p>

        <p>Riceverai un'altra email quando il tuo ordine verrà spedito.</p>
    </div>

    <div class="footer">
        <p>SweetHome Milano — Via Carlo Espinasse 104/a, 20156 Milano MI</p>
        <p>info@sweethome.it — 328 899 5628</p>
    </div>
</body>
</html>
