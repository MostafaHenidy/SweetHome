<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentInstallment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FawaterakService
{
    protected string $apiKey;
    protected string $secretKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.fawaterak.api_key');
        $this->secretKey = config('services.fawaterak.secret_key');
        $this->baseUrl = config('services.fawaterak.base_url');
    }

    /**
     * Create a payment invoice for Fawaterak
     */
    public function createPaymentInvoice(Order $order, PaymentInstallment $installment): ?array
    {
        try {
            $payload = [
                'customer' => [
                    'name' => $order->customer_name,
                    'email' => $order->customer_email,
                    'phone' => $order->customer_phone,
                ],
                'items' => [
                    [
                        'name' => "Pagamento Installment {$installment->installment_number} - Ordine {$order->order_number}",
                        'quantity' => 1,
                        'unit_price' => $installment->amount,
                    ],
                ],
                'currency' => 'EUR',
                'due_date' => now()->addDays(30)->format('Y-m-d'),
                'webhook_url' => route('webhook.fawaterak'),
                'redirect_url' => route('payment.success', ['order' => $order->order_number]),
            ];

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/invoices", $payload);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('Fawaterak invoice created', [
                    'order_id' => $order->id,
                    'installment_id' => $installment->id,
                    'invoice_id' => $data['id'] ?? null,
                ]);

                return $data;
            }

            Log::error('Fawaterak API error', [
                'order_id' => $order->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Fawaterak exception', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Verify payment status from webhook
     */
    public function verifyPayment(string $transactionId): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
            ])->get("{$this->baseUrl}/invoices/{$transactionId}");

            if ($response->successful()) {
                $data = $response->json();
                return ($data['status'] ?? '') === 'paid';
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Fawaterak verification failed', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Process webhook callback from Fawaterak
     */
    public function handleWebhook(array $payload): bool
    {
        try {
            // Verify webhook signature if needed
            // $this->verifyWebhookSignature($payload);

            $transactionId = $payload['invoice_id'] ?? null;
            $status = $payload['status'] ?? null;
            $orderNumber = $payload['reference'] ?? null;

            if (!$transactionId || !$status) {
                Log::error('Invalid Fawaterak webhook payload', ['payload' => $payload]);
                return false;
            }

            // Find the installment by transaction ID
            $installment = PaymentInstallment::where('payment_transaction_id', $transactionId)->first();

            if (!$installment) {
                // Try to find by order number
                $order = Order::where('order_number', $orderNumber)->first();
                if ($order) {
                    $installment = $order->paymentInstallments()->where('status', 'pending')->first();
                }
            }

            if ($installment && $status === 'paid') {
                $installmentService = app(PaymentInstallmentService::class);
                $installmentService->markInstallmentPaid($installment, $transactionId);

                Log::info('Fawaterak webhook processed successfully', [
                    'installment_id' => $installment->id,
                    'transaction_id' => $transactionId,
                ]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Fawaterak webhook processing failed', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return false;
        }
    }

    /**
     * Get payment URL for redirect
     */
    public function getPaymentUrl(string $invoiceId): string
    {
        return "{$this->baseUrl}/invoice/{$invoiceId}/pay";
    }
}