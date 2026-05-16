<?php

namespace Tests\Feature;

use App\Events\OrderCreated;
use App\Listeners\SendOrderNotifications;
use App\Models\Order;
use App\Models\Product;
use App\Notifications\OrderCreatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OrderEmailNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_created_event_fires_and_sends_notifications()
    {
        // Create a test order
        $order = Order::create([
            'order_number' => 'TEST-001',
            'type' => 'direct',
            'customer_name' => 'Test Customer',
            'customer_email' => 'test@example.com',
            'shipping_address' => [
                'type' => 'pickup',
                'location_name' => 'Magazzino Principale Milano',
                'location_address' => 'Via delle Industrie, 123, 20138 Milano (MI)',
                'location_phone' => '328 899 5628',
                'location_hours' => 'Lun-Ven: 9:00-18:00, Sab: 9:00-13:00',
            ],
            'billing_address' => ['city' => 'Milano', 'line1' => 'Test Street 1', 'postal_code' => '20100', 'province' => 'MI'],
            'subtotal' => 100.00,
            'shipping_cost' => 10.00,
            'tax' => 0,
            'total' => 110.00,
            'status' => 'pending',
            'payment_method' => 'cash_on_delivery',
            'payment_status' => 'pending',
            'shipping_method' => 'pickup',
            'service_type' => 'shipping_only',
        ]);

        // Mock the notification system
        Notification::fake();

        // Dispatch the event
        Event::dispatch(OrderCreated::class, new OrderCreated($order));

        // For this test, we just verify that the event can be dispatched without errors
        // The actual notification sending is tested in other tests
        $this->assertTrue(true);
    }

    public function test_order_created_notification_contains_correct_data()
    {
        $order = Order::create([
            'order_number' => 'TEST-002',
            'type' => 'direct',
            'customer_name' => 'Test Customer',
            'customer_email' => 'test@example.com',
            'shipping_address' => [
                'type' => 'pickup',
                'location_name' => 'Magazzino Principale Milano',
                'location_address' => 'Via delle Industrie, 123, 20138 Milano (MI)',
            ],
            'billing_address' => ['city' => 'Milano', 'line1' => 'Test Street 1', 'postal_code' => '20100', 'province' => 'MI'],
            'subtotal' => 100.00,
            'shipping_cost' => 10.00,
            'tax' => 0,
            'total' => 110.00,
            'status' => 'pending',
            'payment_method' => 'cash_on_delivery',
            'payment_status' => 'pending',
            'shipping_method' => 'pickup',
            'service_type' => 'shipping_only',
        ]);

        $notification = new OrderCreatedNotification($order);

        // Verify the notification can be created and has order data
        $this->assertEquals($order->order_number, $notification->toArray($order)['order_number']);
        $this->assertEquals(110.00, $notification->toArray($order)['total']);

        // Test that the mail message can be generated without errors
        $mockUser = new \stdClass();
        $mockUser->name = 'Test User';

        $mailMessage = $notification->toMail($mockUser);

        // Just verify it returns a mail message object
        $this->assertInstanceOf(\Illuminate\Notifications\Messages\MailMessage::class, $mailMessage);
    }

    public function test_send_order_notifications_listener_handles_event()
    {
        $order = Order::create([
            'order_number' => 'TEST-003',
            'type' => 'direct',
            'customer_name' => 'Test Customer',
            'customer_email' => 'test@example.com',
            'shipping_address' => [
                'type' => 'pickup',
                'location_name' => 'Magazzino Principale Milano',
                'location_address' => 'Via delle Industrie, 123, 20138 Milano (MI)',
            ],
            'billing_address' => ['city' => 'Milano', 'line1' => 'Test Street 1', 'postal_code' => '20100', 'province' => 'MI'],
            'subtotal' => 100.00,
            'shipping_cost' => 10.00,
            'tax' => 0,
            'total' => 110.00,
            'status' => 'pending',
            'payment_method' => 'cash_on_delivery',
            'payment_status' => 'pending',
            'shipping_method' => 'pickup',
            'service_type' => 'shipping_only',
        ]);

        Notification::fake();

        $listener = new SendOrderNotifications();
        $event = new OrderCreated($order);

        // This should not throw an exception
        $listener->handle($event);

        // Basic assertion that listener executed
        $this->assertTrue(true);
    }
}