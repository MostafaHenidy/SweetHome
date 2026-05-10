<?php

namespace App\Services;

use App\Events\OrderStatusChanged;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Stripe;

class PaymentService
{
    public function __construct(
        protected BrandedPdfService $brandedPdfService,
        protected DocumentNumberService $documentNumberService
    ) {}

    public function processStripeCheckout(Order $order): string
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $lineItems = [];
        if ((float) $order->discount_amount > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => "Ordine {$order->order_number}",
                        'description' => 'Totale ordine comprensivo di sconti e spedizione',
                    ],
                    'unit_amount' => (int) round($order->total * 100),
                ],
                'quantity' => 1,
            ];
        } else {
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

        $purchaseUnit = [
            'reference_id' => $order->order_number,
            'amount' => [
                'currency_code' => 'EUR',
                'value' => number_format($order->total, 2, '.', ''),
            ],
        ];

        if ((float) $order->discount_amount <= 0) {
            $purchaseUnit['amount']['breakdown'] = [
                'item_total' => [
                    'currency_code' => 'EUR',
                    'value' => number_format($order->subtotal, 2, '.', ''),
                ],
                'shipping' => [
                    'currency_code' => 'EUR',
                    'value' => number_format($order->shipping_cost, 2, '.', ''),
                ],
            ];
            $purchaseUnit['items'] = $order->items->map(fn ($item) => [
                'name' => $item->product_name,
                'quantity' => $item->quantity,
                'unit_amount' => [
                    'currency_code' => 'EUR',
                    'value' => number_format($item->unit_price, 2, '.', ''),
                ],
            ])->toArray();
        }

        $orderData = [
            'intent' => 'CAPTURE',
            'purchase_units' => [$purchaseUnit],
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
        $oldStatus = $order->status;

        // Determine new status based on payment installments
        $newStatus = $this->determineOrderStatusAfterPayment($order);

        $order->update([
            'payment_status' => 'paid',
            'status' => $newStatus,
        ]);

        // Dispatch event for status change
        if ($oldStatus !== $newStatus) {
            OrderStatusChanged::dispatch($order, $oldStatus, $newStatus);
        }

        $order->refresh();
        if ($order->type === 'direct' && ! $order->invoice_pdf_path) {
            try {
                $order->load('items');
                $this->documentNumberService->ensureInvoiceDocumentAssigned($order);
                $order->refresh();
                $path = $this->brandedPdfService->generateAndStoreInvoice($order);
                $order->update(['invoice_pdf_path' => $path]);
            } catch (\Throwable $e) {
                Log::error('Invoice PDF generation failed', [
                    'order_id' => $order->id,
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Determine the appropriate order status after payment based on installments
     */
    private function determineOrderStatusAfterPayment(Order $order): string
    {
        // If order has installments, check payment progress
        if ($order->paymentInstallments()->exists()) {
            $installmentService = app(PaymentInstallmentService::class);

            // Check which installment was just paid
            $nextPending = $installmentService->getNextInstallmentToPay($order);

            if ($nextPending) {
                // There are still pending installments
                return 'deposit_paid'; // First installment paid, waiting for others
            } else {
                // All installments paid
                if ($order->service_type === 'shipping_and_construction') {
                    return 'construction_scheduled'; // Ready for construction phase
                } else {
                    return 'processing'; // Ready for shipping
                }
            }
        }

        // Legacy orders without installments
        return 'processing';
    }

    public function markOrderAwaitingPayment(Order $order): void
    {
        $oldStatus = $order->status;
        $newStatus = 'awaiting_payment';

        $order->update([
            'payment_status' => 'pending',
            'status' => $newStatus,
        ]);

        // Dispatch event for status change
        if ($oldStatus !== $newStatus) {
            OrderStatusChanged::dispatch($order, $oldStatus, $newStatus);
        }
    }

    public function markOrderFailed(Order $order): void
    {
        $order->update([
            'payment_status' => 'failed',
        ]);
    }
}
