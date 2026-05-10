<?php

namespace App\Services;

use App\Events\QuoteRequestCreated;
use App\Events\QuoteStatusChanged;
use App\Models\Customer;
use App\Models\Order;
use Carbon\Carbon;

class QuoteService
{
    public function __construct(
        protected BrandedPdfService $brandedPdfService,
        protected DocumentNumberService $documentNumberService
    ) {}

    /**
     * Create a quote request from cart
     */
    public function createQuoteRequest($cartItems, $customerData)
    {
        if (! is_array($cartItems) || $cartItems === []) {
            throw new \InvalidArgumentException('Il carrello è vuoto: aggiungi almeno un prodotto prima di richiedere un preventivo.');
        }

        // Create or find customer
        $customer = Customer::updateOrCreate(
            ['email' => $customerData['email']],
            [
                'name' => $customerData['name'],
                'phone' => $customerData['phone'] ?? null,
                'company_name' => $customerData['company_name'] ?? null,
                'address' => $customerData['address'],
                'city' => $customerData['city'],
                'state' => $customerData['state'],
                'zip_code' => $customerData['zip_code'],
                'country' => $customerData['country'],
            ]
        );

        // Calculate totals
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += $item['total_price'];
        }

        $tax = 0; // Can be calculated based on location
        $shippingCost = 0; // Quote requests typically don't include shipping initially
        $discountAmount = 0;
        $total = $subtotal + $tax + $shippingCost - $discountAmount;

        // Create order as quote request
        $order = Order::create([
            'order_number' => $this->generateQuoteNumber(),
            'type' => 'quote_request',
            'status' => 'pending',
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone,
            'customer_company_name' => $customerData['company_name'] ?? null,
            'shipping_address' => [
                'address' => $customer->address,
                'city' => $customer->city,
                'state' => $customer->state,
                'zip_code' => $customer->zip_code,
                'country' => $customer->country,
            ],
            'billing_address' => null,
            'subtotal' => $subtotal,
            'shipping_cost' => $shippingCost,
            'tax' => $tax,
            'discount_amount' => $discountAmount,
            'total' => $total,
            'payment_method' => null,
            'payment_status' => 'pending',
            'notes' => $customerData['notes'] ?? null,
        ]);

        // Add order items
        foreach ($cartItems as $item) {
            $order->items()->create([
                'product_id' => $item['product_id'],
                'product_name' => $item['product_name'],
                'product_sku' => $item['product_sku'] ?? '',
                'line_description' => $item['line_description'] ?? $item['product_name'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => $item['total_price'],
                'attribute_selection' => $item['attribute_selection'] ?? null,
                'vat_rate' => $item['vat_rate'] ?? null,
            ]);
        }

        // Dispatch event for quote request creation
        QuoteRequestCreated::dispatch($order);

        // Refresh the order to load the items relationship
        $order->load('items');

        return $order;
    }

    /**
     * Generate PDF quote and store it
     *
     * @param  bool  $advanceWorkflow  When false, only refreshes the stored file and quote_pdf_path (regeneration).
     */
    public function generatePdf(Order $order, bool $advanceWorkflow = true)
    {
        if (! $order->isQuoteRequest()) {
            throw new \Exception('Order is not a quote request');
        }

        $this->documentNumberService->ensureQuoteDocumentAssigned($order);
        $order->refresh();
        $order->load('items');

        $filename = $this->brandedPdfService->generateAndStoreQuote($order);

        if (! $advanceWorkflow) {
            $order->update([
                'quote_pdf_path' => $filename,
            ]);

            return $order->fresh();
        }

        // Update order with PDF path and set status to quote_sent
        $oldStatus = $order->status;
        $newStatus = 'quote_sent';

        $order->update([
            'quote_pdf_path' => $filename,
            'status' => $newStatus,
            'quote_expires_at' => Carbon::now()->addDays(30), // 30 days validity
        ]);

        // Dispatch event for quote status change
        if ($oldStatus !== $newStatus) {
            QuoteStatusChanged::dispatch($order, $oldStatus, $newStatus);
        }

        return $order;
    }

    /**
     * Send quote to customer email
     */
    public function sendQuote(Order $order)
    {
        if (! $order->quote_pdf_path) {
            $this->generatePdf($order);
        }

        // Send email (you'll need to create the mailable)
        // Mail::to($order->customer_email)->send(new QuoteSent($order));

        return true;
    }

    /**
     * Accept quote
     */
    public function acceptQuote(Order $order)
    {
        if (! $order->canBeAccepted()) {
            throw new \Exception('Quote cannot be accepted');
        }

        $order->update([
            'status' => 'quote_accepted',
        ]);

        return $order;
    }

    /**
     * Reject quote
     */
    public function rejectQuote(Order $order, $reason = null)
    {
        $order->update([
            'status' => 'quote_rejected',
            'notes' => $order->notes."\nRejection reason: ".$reason,
        ]);

        return $order;
    }

    /**
     * Get all quotes for a customer
     */
    public function getCustomerQuotes($customerId)
    {
        return Order::where('customer_id', $customerId)
            ->where('type', 'quote_request')
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Generate unique quote number
     */
    private function generateQuoteNumber()
    {
        $prefix = 'Q';
        $date = Carbon::now()->format('Ymd');
        $random = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 6);

        return $prefix.$date.$random;
    }
}
