<?php

namespace Tests\Feature;

use App\Http\Livewire\CheckoutPage;
use App\Http\Livewire\QuoteRequestPage;
use App\Models\Order;
use App\Models\Product;
use App\Services\BrandedPdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Tests\TestCase;

class CheckoutAndQuoteRequestFlowTest extends TestCase
{
    use RefreshDatabase;

    private function createPublishedProduct(): Product
    {
        return Product::create([
            'sku' => 'TST-'.uniqid('', true),
            'name' => 'Test Marble',
            'slug' => 'test-marble-'.uniqid('', true),
            'price' => 100.00,
            'status' => 'published',
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function guestSessionCart(Product $product): array
    {
        $key = $product->id.'-'.md5(json_encode([]));

        return [
            $key => [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
                'image' => null,
                'quantity' => 2,
                'attributes' => [],
            ],
        ];
    }

    public function test_guest_checkout_creates_direct_order_installments_and_redirects_to_payment(): void
    {
        Event::fake();

        $this->mock(BrandedPdfService::class, function ($mock) {
            $mock->shouldReceive('generateAndStoreOrderConfirmation')
                ->once()
                ->andReturn('orders/confirmation_test.pdf');
        });

        $product = $this->createPublishedProduct();
        $cart = $this->guestSessionCart($product);

        $this->withSession(['cart' => $cart]);

        Livewire::test(CheckoutPage::class)
            ->set('customer_name', 'Mario Rossi')
            ->set('customer_email', 'mario@example.com')
            ->call('nextStep')
            ->set('shipping_method', 'delivery')
            ->set('service_type', 'shipping_only')
            ->set('shipping_address_line1', 'Via Roma 10')
            ->set('shipping_city', 'Milano')
            ->set('shipping_province', 'MI')
            ->set('shipping_postal_code', '20100')
            ->set('shipping_country', 'IT')
            ->call('nextStep')
            ->set('payment_method', 'stripe')
            ->set('address_line1', 'Via Garibaldi 5')
            ->set('address_line2', '')
            ->set('city', 'Milano')
            ->set('province', 'MI')
            ->set('postal_code', '20121')
            ->set('country', 'IT')
            ->call('placeOrder')
            ->assertRedirect();

        $order = Order::where('type', 'direct')->first();
        $this->assertNotNull($order);
        $this->assertSame('pending_deposit', $order->status);
        $this->assertSame('delivery', $order->shipping_method);
        $this->assertSame('shipping_only', $order->service_type);
        $this->assertSame(200.0, (float) $order->subtotal);
        $this->assertCount(1, $order->items);
        $this->assertCount(2, $order->paymentInstallments);
        $this->assertSame('orders/confirmation_test.pdf', $order->order_confirmation_pdf_path);
    }

    public function test_guest_quote_request_creates_quote_order_and_redirects_to_success(): void
    {
        Event::fake();

        $this->mock(BrandedPdfService::class, function ($mock) {
            $mock->shouldReceive('generateAndStoreQuote')
                ->once()
                ->andReturn('quotes/quote_test.pdf');
        });

        $product = $this->createPublishedProduct();
        $cart = $this->guestSessionCart($product);

        $this->withSession(['cart' => $cart]);

        Livewire::test(QuoteRequestPage::class)
            ->set('customer_name', 'Luigi Verdi')
            ->set('customer_email', 'luigi@example.com')
            ->call('nextStep')
            ->set('address', 'Corso Italia 1')
            ->set('city', 'Torino')
            ->set('state', 'TO')
            ->set('zip_code', '10100')
            ->set('country', 'IT')
            ->call('nextStep')
            ->call('submitQuoteRequest')
            ->assertRedirect();

        $order = Order::where('type', 'quote_request')->first();
        $this->assertNotNull($order);
        $this->assertSame('quote_sent', $order->status);
        $this->assertSame(200.0, (float) $order->total);
        $this->assertCount(1, $order->items);
        $this->assertSame('quotes/quote_test.pdf', $order->quote_pdf_path);
    }
}
