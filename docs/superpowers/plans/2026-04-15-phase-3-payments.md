# Phase 3: Payments & Order Management Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Integrate Stripe and PayPal payment processing, add order management (status updates, order history for users), and email notification system for order lifecycle events.

**Architecture:** Stripe via `stripe/stripe-php` (official Laravel Cashier not needed — we only need one-time charges, not subscriptions). PayPal via `srmklive/paypal` package. Orders are created in Phase 2's checkout, but payment status is updated via webhook/callback. Mailables for order confirmation emails. User dashboard for order history.

**Tech Stack:** Stripe API, PayPal REST API, Laravel Mailables, Laravel Queues, Blade emails

**Prerequisites:** Phase 1 (models, migrations) and Phase 2 (checkout flow, order creation) complete.

---

### Task 1: Install Payment Packages

**Files:**
- Modify: `composer.json` (new dependencies)
- Modify: `.env` (payment credentials)

- [ ] **Step 1: Install Stripe package**

Run:
```bash
composer require stripe/stripe-php
```

- [ ] **Step 2: Install PayPal package**

Run:
```bash
composer require srmklive/paypal:"~3.0"
```

- [ ] **Step 3: Publish PayPal config**

Run:
```bash
php artisan vendor:publish --provider "Srmklive\PayPal\Providers\PayPalServiceProvider" --tag="config"
```

- [ ] **Step 4: Add environment variables**

Add to `.env`:
```env
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...

PAYPAL_MODE=sandbox
PAYPAL_SANDBOX_CLIENT_ID=...
PAYPAL_SANDBOX_CLIENT_SECRET=...
PAYPAL_LIVE_CLIENT_ID=
PAYPAL_LIVE_CLIENT_SECRET=
```

- [ ] **Step 5: Commit**

```bash
git add composer.json composer.lock config/paypal.php .env.example
git commit -m "feat: install Stripe and PayPal packages"
```

---

### Task 2: Create Stripe Payment Service

**Files:**
- Create: `app/Services/PaymentService.php`
- Create: `app/Http/Controllers/PaymentController.php`
- Create: `app/Http/Controllers/StripeWebhookController.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Create PaymentService**

Create `app/Services/PaymentService.php`:
```php
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
```

- [ ] **Step 2: Create PaymentController**

Create `app/Http/Controllers/PaymentController.php`:
```php
<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function initiate(Request $request, string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        $request->validate([
            'method' => 'required|in:stripe,paypal,bank_transfer',
        ]);

        if ($request->method === 'bank_transfer') {
            $this->paymentService->markOrderPaid($order);
            return redirect()->route('checkout.success', $order->order_number);
        }

        if ($request->method === 'stripe') {
            $checkoutUrl = $this->paymentService->processStripeCheckout($order);
            return redirect($checkoutUrl);
        }

        if ($request->method === 'paypal') {
            $result = $this->paymentService->processPayPalCheckout($order);
            Session::put('paypal_order_id', $result['order_id']);
            return redirect($result['redirect_url']);
        }

        return redirect()->route('checkout');
    }

    public function success(Request $request, string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        if ($request->has('session')) {
            $this->paymentService->markOrderPaid($order);
        }

        return redirect()->route('checkout.success', $order->order_number);
    }

    public function cancel(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        $this->paymentService->markOrderFailed($order);

        return redirect()->route('checkout')
            ->with('error', 'Pagamento annullato. Riprova.');
    }

    public function paypalReturn(Request $request, string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        $paypalOrderId = Session::get('paypal_order_id');

        if ($paypalOrderId && $this->paymentService->capturePayPalOrder($paypalOrderId)) {
            $this->paymentService->markOrderPaid($order);
            Session::forget('paypal_order_id');
            return redirect()->route('checkout.success', $order->order_number);
        }

        $this->paymentService->markOrderFailed($order);
        return redirect()->route('checkout')
            ->with('error', 'Pagamento PayPal non completato.');
    }
}
```

- [ ] **Step 3: Create StripeWebhookController**

Create `app/Http/Controllers/StripeWebhookController.php`:
```php
<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Stripe\Webhook;
use Stripe\Stripe;

class StripeWebhookController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function handle(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $orderNumber = $session->metadata->order_number;
            $order = Order::where('order_number', $orderNumber)->first();

            if ($order) {
                $this->paymentService->markOrderPaid($order);
            }
        }

        return response()->json(['received' => true]);
    }
}
```

- [ ] **Step 4: Add payment routes**

Modify `routes/web.php` — add:
```php
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StripeWebhookController;

Route::post('/payment/{orderNumber}', [PaymentController::class, 'initiate'])->name('payment.initiate');
Route::get('/payment/success/{order}', [PaymentController::class, 'success'])->name('payment.success');
Route::get('/payment/cancel/{order}', [PaymentController::class, 'cancel'])->name('payment.cancel');
Route::get('/paypal/return/{order}', [PaymentController::class, 'paypalReturn'])->name('paypal.return');

Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handle'])->name('webhooks.stripe');
```

- [ ] **Step 5: Add Stripe config**

Modify `config/services.php`:
```php
'stripe' => [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
],
```

- [ ] **Step 6: Commit**

```bash
git add app/Services/PaymentService.php app/Http/Controllers/PaymentController.php app/Http/Controllers/StripeWebhookController.php routes/web.php config/services.php
git commit -m "feat: create Stripe and PayPal payment services with webhook handling"
```

---

### Task 3: Update Checkout to Redirect to Payment

**Files:**
- Modify: `app/Http/Livewire/CheckoutPage.php`
- Modify: `resources/views/livewire/checkout-page.blade.php`

- [ ] **Step 1: Update CheckoutPage placeOrder**

Modify `app/Http/Livewire/CheckoutPage.php` — change the `placeOrder()` method to redirect to payment initiation instead of directly to success:

```php
// Replace the placeOrder() method with:
public function placeOrder()
{
    $this->validate();

    $order = Order::create([
        'order_number' => 'SH-' . date('Ymd') . '-' . str_pad(Order::count() + 1, 4, '0', STR_PAD_LEFT),
        'user_id' => auth()->id(),
        'customer_name' => $this->customer_name,
        'customer_email' => $this->customer_email,
        'customer_phone' => $this->customer_phone,
        'shipping_address' => [
            'line1' => $this->address_line1,
            'line2' => $this->address_line2,
            'city' => $this->city,
            'province' => $this->province,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
        ],
        'subtotal' => $this->subtotal,
        'shipping_cost' => $this->shippingCost,
        'tax' => 0,
        'total' => $this->total,
        'payment_method' => $this->payment_method,
        'payment_status' => 'pending',
    ]);

    foreach ($this->cart as $item) {
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $item['product_id'],
            'product_name' => $item['name'],
            'product_sku' => '',
            'attribute_selection' => $item['attributes'] ?? null,
            'quantity' => $item['quantity'],
            'unit_price' => $item['price'],
            'total' => $item['price'] * $item['quantity'],
        ]);
    }

    session()->forget('cart');

    // Redirect to payment initiation
    redirect()->route('payment.initiate', $order->order_number)
        ->with('payment_method', $this->payment_method);
}
```

- [ ] **Step 2: Update checkout view for payment step**

Modify `resources/views/livewire/checkout-page.blade.php` — update the payment step (Step 3) to note that bank transfer is immediate while Stripe/PayPal redirect to external payment:

In the payment method radio section, keep the same three options. The controller handles the redirect logic.

- [ ] **Step 3: Commit**

```bash
git add app/Http/Livewire/CheckoutPage.php resources/views/livewire/checkout-page.blade.php
git commit -m "feat: update checkout to redirect to payment gateway after order creation"
```

---

### Task 4: Create Email Mailables

**Files:**
- Create: `app/Mail/OrderConfirmation.php`
- Create: `app/Mail/OrderStatusChanged.php`
- Create: `app/Mail/ContactFormNotification.php`
- Create: `resources/views/emails/orders/confirmation.blade.php`
- Create: `resources/views/emails/orders/status-changed.blade.php`
- Create: `resources/views/emails/contact/notification.blade.php`

- [ ] **Step 1: Create OrderConfirmation mailable**

Create `app/Mail/OrderConfirmation.php`:
```php
<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'SweetHome Milano — Conferma Ordine #' . $this->order->order_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.confirmation',
        );
    }
}
```

- [ ] **Step 2: Create OrderStatusChanged mailable**

Create `app/Mail/OrderStatusChanged.php`:
```php
<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $oldStatus,
        public string $newStatus
    ) {}

    public function envelope(): Envelope
    {
        $statusLabels = [
            'pending' => 'In Attesa',
            'processing' => 'In Lavorazione',
            'shipped' => 'Spedito',
            'completed' => 'Completato',
            'cancelled' => 'Annullato',
        ];

        return new Envelope(
            subject: 'SweetHome Milano — Ordine #' . $this->order->order_number . ' ' . ($statusLabels[$this->newStatus] ?? $this->newStatus),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.status-changed',
        );
    }
}
```

- [ ] **Step 3: Create ContactFormNotification mailable**

Create `app/Mail/ContactFormNotification.php`:
```php
<?php

namespace App\Mail;

use App\Models\ContactSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactFormNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ContactSubmission $submission
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nuovo messaggio da ' . $this->submission->name . ' — ' . $this->submission->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact.notification',
        );
    }
}
```

- [ ] **Step 4: Create order confirmation email template**

Create `resources/views/emails/orders/confirmation.blade.php`:
```blade
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
```

- [ ] **Step 5: Create order status changed email template**

Create `resources/views/emails/orders/status-changed.blade.php`:
```blade
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
```

- [ ] **Step 6: Create contact form notification email template**

Create `resources/views/emails/contact/notification.blade.php`:
```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
        .header { background: #1A1A1A; color: #F9F9F9; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .message { background: #f5f5f5; padding: 15px; border-radius: 4px; margin: 15px 0; }
        .footer { background: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>SweetHome Milano</h1>
        <p>Nuovo messaggio dal sito</p>
    </div>

    <div class="content">
        <div class="message">
            <p><strong>Da:</strong> {{ $submission->name }} ({{ $submission->email }})</p>
            <p><strong>Telefono:</strong> {{ $submission->phone ?? 'Non fornito' }}</p>
            <p><strong>Oggetto:</strong> {{ $submission->subject }}</p>
            <p><strong>Messaggio:</strong></p>
            <p>{{ $submission->message }}</p>
        </div>
    </div>

    <div class="footer">
        <p>Questa è una notifica automatica dal sito SweetHome Milano.</p>
    </div>
</body>
</html>
```

- [ ] **Step 7: Commit**

```bash
git add app/Mail/ resources/views/emails/
git commit -m "feat: create order and contact form email mailables with templates"
```

---

### Task 5: Wire Up Email Notifications

**Files:**
- Modify: `app/Http/Controllers/PaymentController.php`
- Modify: `app/Filament/Resources/OrderResource.php`
- Create: `app/Observers/OrderObserver.php`
- Create: `app/Providers/EventServiceProvider.php` (or modify existing)

- [ ] **Step 1: Create OrderObserver**

Create `app/Observers/OrderObserver.php`:
```php
<?php

namespace App\Observers;

use App\Mail\OrderConfirmation;
use App\Mail\OrderStatusChanged;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;

class OrderObserver
{
    public function created(Order $order): void
    {
        Mail::to($order->customer_email)->send(new OrderConfirmation($order));
    }

    public function updated(Order $order): void
    {
        if ($order->isDirty('status')) {
            Mail::to($order->customer_email)->send(
                new OrderStatusChanged($order, $order->getOriginal('status'), $order->status)
            );
        }
    }
}
```

- [ ] **Step 2: Register OrderObserver**

Modify `app/Providers/AppServiceProvider.php` — add to `boot()` method:
```php
use App\Models\Order;
use App\Observers\OrderObserver;

Order::observe(OrderObserver::class);
```

- [ ] **Step 3: Add email sending to PaymentController**

Modify `app/Http/Controllers/PaymentController.php` — in the `success()` method, after marking order paid, the observer will fire the status change email. No additional code needed since the observer handles it.

- [ ] **Step 4: Add email action to Filament OrderResource**

Modify `app/Filament/Resources/OrderResource.php` — add a "Send Status Update Email" action to the view page:

In the `getHeaderActions()` or view page actions, add:
```php
Action::make('sendStatusEmail')
    ->label('Invia email stato')
    ->icon('heroicon-o-envelope')
    ->action(function (Order $record) {
        \App\Mail\OrderStatusChanged::dispatch($record, $record->status, $record->status);
        $this->notify('success', 'Email inviata');
    }),
```

- [ ] **Step 5: Configure queue for emails**

Modify `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="info@sweethome.it"
MAIL_FROM_NAME="SweetHome Milano"

QUEUE_CONNECTION=database
```

Run:
```bash
php artisan queue:table
php artisan migrate
```

- [ ] **Step 6: Commit**

```bash
git add app/Observers/ app/Providers/AppServiceProvider.php app/Filament/Resources/OrderResource.php
git commit -m "feat: wire up email notifications via OrderObserver and configure mail queue"
```

---

### Task 6: Create User Order History Dashboard

**Files:**
- Create: `app/Http/Livewire/OrderHistory.php`
- Create: `resources/views/livewire/order-history.blade.php`
- Create: `resources/views/account.blade.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Create OrderHistory Livewire component**

Create `app/Http/Livewire/OrderHistory.php`:
```php
<?php

namespace App\Http\Livewire;

use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;

class OrderHistory extends Component
{
    use WithPagination;

    public function render()
    {
        $orders = Order::query()
            ->where('customer_email', auth()->user()->email)
            ->latest()
            ->paginate(10);

        return view('livewire.order-history', [
            'orders' => $orders,
        ]);
    }
}
```

- [ ] **Step 2: Create order history view**

Create `resources/views/livewire/order-history.blade.php`:
```blade
<div>
    @if($orders->count() > 0)
        <div class="space-y-4">
            @foreach($orders as $order)
                <div class="bg-brand-slate/10 border border-white/5 rounded-sm p-6">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div>
                            <h3 class="text-lg font-serif text-brand-light">
                                Ordine #{{ $order->order_number }}
                            </h3>
                            <p class="text-sm text-gray-400 mt-1">
                                {{ $order->created_at->format('d/m/Y H:i') }}
                            </p>
                        </div>

                        <div class="flex items-center gap-4">
                            <span class="px-3 py-1 text-xs font-semibold rounded-sm
                                @match($order->status)
                                    @case('pending') bg-yellow-500/20 text-yellow-400 @break
                                    @case('processing') bg-blue-500/20 text-blue-400 @break
                                    @case('shipped') bg-purple-500/20 text-purple-400 @break
                                    @case('completed') bg-green-500/20 text-green-400 @break
                                    @case('cancelled') bg-red-500/20 text-red-400 @break
                                @endmatch">
                                @match($order->status)
                                    @case('pending') In Attesa @break
                                    @case('processing') In Lavorazione @break
                                    @case('shipped') Spedito @break
                                    @case('completed') Completato @break
                                    @case('cancelled') Annullato @break
                                @endmatch
                            </span>

                            <span class="text-brand-gold font-bold">
                                €{{ number_format($order->total, 2, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-white/5">
                        <p class="text-sm text-gray-400">
                            {{ $order->items->count() }} articoli —
                            @foreach($order->items->take(3) as $item)
                                {{ $item->product_name }}@if(!$loop->last), @endif
                            @endforeach
                            @if($order->items->count() > 3) e altri {{ $order->items->count() - 3 }} @endif
                        </p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $orders->links() }}
        </div>
    @else
        <div class="text-center py-16">
            <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <h3 class="text-xl font-serif text-brand-light mb-2">Nessun ordine</h3>
            <p class="text-gray-400 mb-6">Non hai ancora effettuato ordini.</p>
            <a href="{{ route('shop') }}"
               class="inline-block px-6 py-3 bg-brand-gold text-brand-dark text-sm font-semibold tracking-widest uppercase hover:bg-opacity-90 transition rounded-sm">
                Vai allo Shop
            </a>
        </div>
    @endif
</div>
```

- [ ] **Step 3: Create account page**

Create `resources/views/account.blade.php`:
```blade
@extends('layouts.app-shop')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl md:text-4xl font-serif font-bold text-brand-light mb-8">Il Mio Account</h1>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <!-- Sidebar -->
        <div class="md:col-span-1">
            <nav class="space-y-2">
                <a href="{{ route('account.orders') }}"
                   class="block px-4 py-2 text-sm rounded-sm {{ request()->routeIs('account.orders') ? 'bg-brand-gold/10 text-brand-gold' : 'text-gray-400 hover:text-brand-light' }} transition">
                    I Miei Ordini
                </a>
                <a href="{{ route('profile.show') }}"
                   class="block px-4 py-2 text-sm rounded-sm {{ request()->routeIs('profile.show') ? 'bg-brand-gold/10 text-brand-gold' : 'text-gray-400 hover:text-brand-light' }} transition">
                    Profilo
                </a>
            </nav>
        </div>

        <!-- Content -->
        <div class="md:col-span-3">
            @livewire('order-history')
        </div>
    </div>
</div>
@endsection
```

- [ ] **Step 4: Add account routes**

Modify `routes/web.php`:
```php
Route::middleware(['auth'])->group(function () {
    Route::get('/account', function () {
        return view('account');
    })->name('account.orders');
});
```

- [ ] **Step 5: Add account link to nav**

Modify `resources/views/layouts/app-shop.blade.php` — in the desktop nav, after the cart icon, add:
```blade
@auth
    <a href="{{ route('account.orders') }}" class="text-sm tracking-wider uppercase hover:text-brand-gold transition-colors duration-300">Account</a>
@else
    <a href="{{ route('login') }}" class="text-sm tracking-wider uppercase hover:text-brand-gold transition-colors duration-300">Accedi</a>
@endauth
```

- [ ] **Step 6: Commit**

```bash
git add app/Http/Livewire/OrderHistory.php resources/views/livewire/order-history.blade.php resources/views/account.blade.php routes/web.php resources/views/layouts/app-shop.blade.php
git commit -m "feat: create user order history dashboard with account page"
```

---

### Task 7: Update Filament Order Management

**Files:**
- Modify: `app/Filament/Resources/OrderResource.php`
- Modify: `app/Filament/Resources/OrderResource/Pages/ViewOrder.php`

- [ ] **Step 1: Add status update action to OrderResource**

Modify `app/Filament/Resources/OrderResource.php` — add bulk actions for status updates:

In the table's `bulkActions()`:
```php
public function bulkActions(): array
{
    return [
        BulkAction::make('markProcessing')
            ->label('Segna come In Lavorazione')
            ->action(fn(Collection $records) => $records->each->update(['status' => 'processing'])),
        BulkAction::make('markShipped')
            ->label('Segna come Spedito')
            ->action(fn(Collection $records) => $records->each->update(['status' => 'shipped'])),
        BulkAction::make('markCompleted')
            ->label('Segna come Completato')
            ->action(fn(Collection $records) => $records->each->update(['status' => 'completed'])),
        BulkAction::make('markCancelled')
            ->label('Segna come Annullato')
            ->action(fn(Collection $records) => $records->each->update(['status' => 'cancelled'])),
    ];
}
```

- [ ] **Step 2: Add payment status filter**

Add to the table's `filters()`:
```php
SelectFilter::make('payment_status')
    ->options([
        'pending' => 'In Attesa',
        'paid' => 'Pagato',
        'failed' => 'Fallito',
        'refunded' => 'Rimborsato',
    ]),
SelectFilter::make('status')
    ->options([
        'pending' => 'In Attesa',
        'processing' => 'In Lavorazione',
        'shipped' => 'Spedito',
        'completed' => 'Completato',
        'cancelled' => 'Annullato',
    ]),
```

- [ ] **Step 3: Add notes editing to ViewOrder page**

Modify `app/Filament/Resources/OrderResource/Pages/ViewOrder.php` — add an edit notes action:
```php
protected function getHeaderActions(): array
{
    return [
        Action::make('updateNotes')
            ->label('Aggiungi Nota')
            ->form([
                Textarea::make('notes')->label('Note'),
            ])
            ->action(function (array $data, Order $record) {
                $record->update(['notes' => $data['notes']]);
            }),
    ];
}
```

- [ ] **Step 4: Commit**

```bash
git add app/Filament/Resources/OrderResource.php app/Filament/Resources/OrderResource/Pages/ViewOrder.php
git commit -m "feat: add bulk status actions and filters to Filament order management"
```

---

## Phase 3 Summary

After completing this phase you will have:
- Stripe Checkout integration for card payments
- PayPal REST API integration for PayPal payments
- Bank transfer as a manual payment option
- Stripe webhook handler for automatic payment confirmation
- Order confirmation emails sent automatically on order creation
- Order status change emails sent when admin updates order status
- Contact form notification emails sent to admin
- User account page with order history
- Enhanced Filament admin with bulk order status actions and filters
- Email queue configured for background processing
