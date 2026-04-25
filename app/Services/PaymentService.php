<?php

namespace App\Services;

use App\Models\Order;
use Stripe\Stripe;
use Stripe\Checkout\Session as CheckoutSession;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PaymentService
{
    public function processStripeCheckout(Order $order): string
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $lineItems = [];
        foreach ($order->items as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $item->product_name,
                        'description' => "Qty: {$item->quantity}",
                    ],
                    'unit_amount' => (int) round($item->unit_price * 100),
                ],
                'quantity' => $item->quantity,
            ];
        }

        if ($order->shipping_cost > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Spedizione',
                    ],
                    'unit_amount' => (int) round($order->shipping_cost * 100),
                ],
                'quantity' => 1,
            ];
        }

        $session = CheckoutSession::create([
            'payment_method_types' => ['card'],
            'customer_email' => $order->customer_email,
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => route('payment.success', ['order' => $order->order_number, 'session' => '{CHECKOUT_SESSION_ID}']),
            'cancel_url' => route('payment.cancel', ['order' => $order->order_number]),
            'metadata' => [
                'order_number' => $order->order_number,
            ],
        ]);

        return $session->url;
    }

    public function processPayPalCheckout(Order $order): array
    {
        $provider = new PayPalClient;
        $provider->getAccessToken();

        $orderData = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => $order->order_number,
                    'amount' => [
                        'currency_code' => 'EUR',
                        'value' => number_format($order->total, 2, '.', ''),
                        'breakdown' => [
                            'item_total' => [
                                'currency_code' => 'EUR',
                                'value' => number_format($order->subtotal, 2, '.', ''),
                            ],
                            'shipping' => [
                                'currency_code' => 'EUR',
                                'value' => number_format($order->shipping_cost, 2, '.', ''),
                            ],
                        ],
                    ],
                    'items' => $order->items->map(fn($item) => [
                        'name' => $item->product_name,
                        'quantity' => $item->quantity,
                        'unit_amount' => [
                            'currency_code' => 'EUR',
                            'value' => number_format($item->unit_price, 2, '.', ''),
                        ],
                    ])->toArray(),
                ],
            ],
            'application_context' => [
                'return_url' => route('paypal.return', ['order' => $order->order_number]),
                'cancel_url' => route('payment.cancel', ['order' => $order->order_number]),
            ],
        ];

        $response = $provider->createOrder($orderData);

        foreach ($response['links'] as $link) {
            if ($link['rel'] === 'approve') {
                return ['redirect_url' => $link['href'], 'order_id' => $response['id']];
            }
        }

        throw new \Exception('PayPal order creation failed');
    }

    public function capturePayPalOrder(string $paypalOrderId): bool
    {
        $provider = new PayPalClient;
        $provider->getAccessToken();
        $response = $provider->captureOrder($paypalOrderId);

        return isset($response['status']) && $response['status'] === 'COMPLETED';
    }

    public function markOrderPaid(Order $order): void
    {
        $order->update([
            'payment_status' => 'paid',
            'status' => 'processing',
        ]);
    }

    public function markOrderFailed(Order $order): void
    {
        $order->update([
            'payment_status' => 'failed',
        ]);
    }
}
