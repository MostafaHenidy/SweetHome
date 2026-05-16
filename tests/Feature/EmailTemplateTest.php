<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PickupLocation;
use App\Notifications\OrderCreatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class EmailTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_pickup_order_email_template_renders_without_errors()
    {
        // Create a dummy product for the test
        $product = \App\Models\Product::create([
            'sku' => 'TEST-'.uniqid(),
            'name' => 'Test Product',
            'slug' => 'test-product-'.uniqid(),
            'price' => 50.00,
            'status' => 'published',
        ]);

        // Create a pickup location
        $pickupLocation = PickupLocation::create([
            'name' => 'Magazzino Principale Milano',
            'address' => 'Via delle Industrie, 123',
            'city' => 'Milano',
            'province' => 'MI',
            'postal_code' => '20138',
            'phone' => '328 899 5628',
            'hours' => 'Lun-Ven: 9:00-18:00, Sab: 9:00-13:00',
            'is_active' => true,
        ]);

        // Create a pickup order
        $order = Order::create([
            'order_number' => 'TEST-PICKUP-001',
            'type' => 'direct',
            'customer_name' => 'Test Customer',
            'customer_email' => 'test@example.com',
            'shipping_address' => [
                'type' => 'pickup',
                'location_id' => $pickupLocation->id,
                'location_name' => $pickupLocation->name,
                'location_address' => $pickupLocation->getFormattedAddressAttribute(),
                'location_phone' => $pickupLocation->phone,
                'location_hours' => $pickupLocation->hours,
            ],
            'billing_address' => [
                'line1' => 'Test Street 1',
                'city' => 'Milano',
                'province' => 'MI',
                'postal_code' => '20100',
                'country' => 'IT',
            ],
            'subtotal' => 100.00,
            'shipping_cost' => 0.00, // No shipping cost for pickup
            'tax' => 0,
            'total' => 100.00,
            'status' => 'pending',
            'payment_method' => 'cash_on_delivery',
            'payment_status' => 'pending',
            'shipping_method' => 'pickup',
            'service_type' => 'shipping_only',
            'pickup_location_id' => $pickupLocation->id,
        ]);

        // Add an order item
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => 'Test Product',
            'product_sku' => 'TEST-001',
            'line_description' => 'Test Product Description',
            'quantity' => 2,
            'unit_price' => 50.00,
            'total_price' => 100.00,
            'vat_rate' => 22,
        ]);

        // Test that the email template renders without errors
        $view = View::make('emails.orders.confirmation', ['order' => $order]);

        $html = $view->render();

        // Assert the template renders successfully
        $this->assertStringContainsString('SweetHome Milano', $html);
        $this->assertStringContainsString($order->order_number, $html);
        $this->assertStringContainsString('Magazzino Principale Milano', $html);
        $this->assertStringContainsString('Via delle Industrie, 123', $html);
        $this->assertStringContainsString('328 899 5628', $html);
        $this->assertStringContainsString('Lun-Ven: 9:00-18:00, Sab: 9:00-13:00', $html);
    }

    public function test_delivery_order_email_template_renders_without_errors()
    {
        // Create a dummy product for the test
        $product = \App\Models\Product::create([
            'sku' => 'TEST-'.uniqid(),
            'name' => 'Test Product',
            'slug' => 'test-product-'.uniqid(),
            'price' => 50.00,
            'status' => 'published',
        ]);

        // Create a delivery order
        $order = Order::create([
            'order_number' => 'TEST-DELIVERY-001',
            'type' => 'direct',
            'customer_name' => 'Test Customer',
            'customer_email' => 'test@example.com',
            'shipping_address' => [
                'line1' => 'Via Roma 123',
                'line2' => 'Appartamento 4B',
                'city' => 'Milano',
                'province' => 'MI',
                'postal_code' => '20100',
                'country' => 'IT',
            ],
            'billing_address' => [
                'line1' => 'Via Roma 123',
                'line2' => 'Appartamento 4B',
                'city' => 'Milano',
                'province' => 'MI',
                'postal_code' => '20100',
                'country' => 'IT',
            ],
            'subtotal' => 100.00,
            'shipping_cost' => 10.00,
            'tax' => 0,
            'total' => 110.00,
            'status' => 'pending',
            'payment_method' => 'cash_on_delivery',
            'payment_status' => 'pending',
            'shipping_method' => 'delivery',
            'service_type' => 'shipping_only',
        ]);

        // Add an order item
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => 'Test Product',
            'product_sku' => 'TEST-001',
            'line_description' => 'Test Product Description',
            'quantity' => 2,
            'unit_price' => 50.00,
            'total_price' => 100.00,
            'vat_rate' => 22,
        ]);

        // Test that the email template renders without errors
        $view = View::make('emails.orders.confirmation', ['order' => $order]);

        $html = $view->render();

        // Assert the template renders successfully
        $this->assertStringContainsString('SweetHome Milano', $html);
        $this->assertStringContainsString($order->order_number, $html);
        $this->assertStringContainsString('Via Roma 123', $html);
        $this->assertStringContainsString('Appartamento 4B', $html);
        $this->assertStringContainsString('Milano', $html);
        $this->assertStringContainsString('20100', $html);
    }
}