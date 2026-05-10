<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Conferma Ordine {{ $order->order_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .header { text-align: center; margin-bottom: 30px; }
        .company-info { text-align: center; margin-bottom: 30px; font-size: 14px; color: #666; }
        .order-details { margin-bottom: 20px; }
        .order-details table { width: 100%; border-collapse: collapse; }
        .order-details td { padding: 5px; }
        .order-details td.label { font-weight: bold; width: 150px; }
        .items-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .items-table th { background: #f0f0f0; padding: 10px; text-align: left; border-bottom: 2px solid #333; }
        .items-table td { padding: 8px; border-bottom: 1px solid #ddd; }
        .items-table .right { text-align: right; }
        .totals { margin-top: 20px; text-align: right; width: 100%; }
        .totals table { width: 300px; margin-left: auto; border-collapse: collapse; }
        .totals td { padding: 5px; }
        .totals td.label { text-align: left; font-weight: bold; }
        .totals td.value { text-align: right; }
        .totals .total-row { border-top: 2px solid #333; font-weight: bold; font-size: 18px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CONFERMA ORDINE</h1>
        <p>N° {{ $order->order_number }}</p>
    </div>

    <div class="company-info">
        <strong>SweetHome Italia</strong><br>
        Via Roma 123, 20100 Milano<br>
        Tel: +39 02 1234567 | Email: info@sweethome.it
    </div>

    <div class="order-details">
        <table>
            <tr>
                <td class="label">Data ordine:</td>
                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td class="label">Cliente:</td>
                <td>{{ $order->customer_name }}</td>
            </tr>
            <tr>
                <td class="label">Email:</td>
                <td>{{ $order->customer_email }}</td>
            </tr>
            @if($order->customer_phone)
            <tr>
                <td class="label">Telefono:</td>
                <td>{{ $order->customer_phone }}</td>
            </tr>
            @endif
            <tr>
                <td class="label">Stato:</td>
                <td><strong>{{ ucfirst($order->status) }}</strong></td>
            </tr>
            <tr>
                <td class="label">Metodo pagamento:</td>
                <td>{{ ucfirst($order->payment_method ?? 'N/A') }}</td>
            </tr>
        </table>
    </div>

    @if($order->shipping_address)
    <div class="order-details">
        <strong>Indirizzo di spedizione:</strong><br>
        {{ $order->shipping_address['line1'] ?? '' }} {{ $order->shipping_address['line2'] ?? '' }}<br>
        {{ $order->shipping_address['city'] ?? '' }} ({{ $order->shipping_address['postal_code'] ?? '' }})<br>
        {{ $order->shipping_address['province'] ?? '' }}, {{ $order->shipping_address['country'] ?? '' }}
    </div>
    @endif

    <table class="items-table">
        <thead>
            <tr>
                <th>Prodotto</th>
                <th>SKU</th>
                <th>Qta</th>
                <th class="right">Prezzo</th>
                <th class="right">Totale</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>{{ $item->product_name }}</td>
                <td>{{ $item->product_sku ?? '-' }}</td>
                <td>{{ $item->quantity }}</td>
                <td class="right">€{{ number_format($item->unit_price, 2, ',', '.') }}</td>
                <td class="right">€{{ number_format($item->total_price, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td class="label">Subtotale:</td>
                <td class="value">€{{ number_format($order->subtotal, 2, ',', '.') }}</td>
            </tr>
            @if($order->shipping_cost > 0)
            <tr>
                <td class="label">Spedizione:</td>
                <td class="value">€{{ number_format($order->shipping_cost, 2, ',', '.') }}</td>
            </tr>
            @endif
            @if($order->tax > 0)
            <tr>
                <td class="label">Tasse:</td>
                <td class="value">€{{ number_format($order->tax, 2, ',', '.') }}</td>
            </tr>
            @endif
            @if($order->discount_amount > 0)
            <tr>
                <td class="label">Sconto:</td>
                <td class="value" style="color: green;">- €{{ number_format($order->discount_amount, 2, ',', '.') }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td class="label">TOTALE:</td>
                <td class="value">€{{ number_format($order->total, 2, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    @if($order->notes)
    <div style="margin-top: 20px;">
        <strong>Note:</strong><br>
        <div style="background: #f9f9f9; padding: 10px; border: 1px solid #ddd; margin-top: 5px;">
            {{ $order->notes }}
        </div>
    </div>
    @endif

    <div style="margin-top: 30px; text-align: center; font-size: 12px; color: #666;">
        <p>Grazie per aver scelto SweetHome!</p>
        <p>Per qualsiasi domanda contattare: info@sweethome.it | +39 02 1234567</p>
    </div>
</body>
</html>