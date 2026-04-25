<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
        .header { background: #1A1A1A; color: #F9F9F9; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .order-info { background: #f5f5f5; padding: 15px; border-radius: 4px; }
        .footer { background: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>SweetHome Milano</h1>
        <p>Aggiornamento Ordine</p>
    </div>

    <div class="content">
        <p>Gentile {{ $order->customer_name }},</p>
        <p>Il tuo ordine <strong>#{{ $order->order_number }}</strong> è stato aggiornato.</p>

        <div class="order-info">
            <p><strong>Nuovo stato:</strong> {{ match($order->status) {
                'pending' => 'In Attesa',
                'processing' => 'In Lavorazione',
                'shipped' => 'Spedito',
                'completed' => 'Completato',
                'cancelled' => 'Annullato',
                default => $order->status,
            } }}</p>
        </div>

        @if($order->status === 'shipped')
        <p>Il tuo ordine è in viaggio! Riceverai ulteriori informazioni sulla spedizione a breve.</p>
        @endif
    </div>

    <div class="footer">
        <p>SweetHome Milano — Via Carlo Espinasse 104/a, 20156 Milano MI</p>
    </div>
</body>
</html>
