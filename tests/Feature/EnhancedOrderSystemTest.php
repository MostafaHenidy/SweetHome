<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PaymentInstallment;
use App\Models\Product;
use App\Models\User;
use App\Services\PaymentInstallmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EnhancedOrderSystemTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_creates_installments_for_shipping_only_order()
    {
        // Create order directly instead of using checkout flow
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'total' => 1000.00,
            'shipping_method' => 'delivery',
            'service_type' => 'shipping_only',
        ]);

        $service = new PaymentInstallmentService();
        $service->createInstallments($order);

        $this->assertEquals('50-50', $order->payment_plan_type);
        $this->assertEquals(500.00, $order->deposit_amount);

        // Check installments
        $installments = $order->paymentInstallments;
        $this->assertCount(2, $installments);

        $firstInstallment = $installments->first();
        $this->assertEquals(1, $firstInstallment->installment_number);
        $this->assertEquals('deposit', $firstInstallment->due_stage);
        $this->assertEquals(50, $firstInstallment->percentage);
        $this->assertEquals(500.00, $firstInstallment->amount);

        $secondInstallment = $installments->last();
        $this->assertEquals(2, $secondInstallment->installment_number);
        $this->assertEquals('on_delivery', $secondInstallment->due_stage);
        $this->assertEquals(50, $secondInstallment->percentage);
        $this->assertEquals(500.00, $secondInstallment->amount);
    }

    /** @test */
    public function it_creates_installments_for_shipping_and_construction_order()
    {
        // Create order directly instead of using checkout flow
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'total' => 1000.00,
            'shipping_method' => 'delivery',
            'service_type' => 'shipping_and_construction',
        ]);

        $service = new PaymentInstallmentService();
        $service->createInstallments($order);

        $this->assertEquals('50-30-20', $order->payment_plan_type);
        $this->assertEquals(500.00, $order->deposit_amount);

        // Check installments
        $installments = $order->paymentInstallments;
        $this->assertCount(3, $installments);

        $firstInstallment = $installments->first();
        $this->assertEquals(1, $firstInstallment->installment_number);
        $this->assertEquals('deposit', $firstInstallment->due_stage);
        $this->assertEquals(50, $firstInstallment->percentage);
        $this->assertEquals(500.00, $firstInstallment->amount);

        $secondInstallment = $installments->get(1);
        $this->assertEquals(2, $secondInstallment->installment_number);
        $this->assertEquals('on_delivery', $secondInstallment->due_stage);
        $this->assertEquals(30, $secondInstallment->percentage);
        $this->assertEquals(300.00, $secondInstallment->amount);

        $thirdInstallment = $installments->last();
        $this->assertEquals(3, $thirdInstallment->installment_number);
        $this->assertEquals('on_construction', $thirdInstallment->due_stage);
        $this->assertEquals(20, $thirdInstallment->percentage);
        $this->assertEquals(200.00, $thirdInstallment->amount);
    }

    /** @test */
    public function it_creates_pickup_order_without_installments_for_construction()
    {
        // Create order directly instead of using checkout flow
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'total' => 1000.00,
            'shipping_method' => 'pickup',
            'service_type' => 'shipping_only', // Pickup orders should be shipping_only
        ]);

        $service = new PaymentInstallmentService();
        $service->createInstallments($order);

        $this->assertEquals('pickup', $order->shipping_method);
        $this->assertEquals('shipping_only', $order->service_type); // Should be shipping_only for pickup

        // Check installments (still created for pickup)
        $installments = $order->paymentInstallments;
        $this->assertCount(2, $installments);
        $this->assertEquals('50-50', $order->payment_plan_type);
    }

    /** @test */
    public function payment_installment_service_calculates_correctly()
    {
        $order = Order::factory()->create([
            'total' => 1000.00,
            'service_type' => 'shipping_and_construction',
        ]);

        $service = new PaymentInstallmentService();
        $service->createInstallments($order);

        $installments = $order->paymentInstallments;
        $this->assertCount(3, $installments);

        // Check amounts
        $this->assertEquals(500.00, $installments->get(0)->amount); // 50%
        $this->assertEquals(300.00, $installments->get(1)->amount); // 30%
        $this->assertEquals(200.00, $installments->get(2)->amount); // 20%

        // Check percentages
        $this->assertEquals(50, $installments->get(0)->percentage);
        $this->assertEquals(30, $installments->get(1)->percentage);
        $this->assertEquals(20, $installments->get(2)->percentage);

        // Check order fields
        $this->assertEquals('50-30-20', $order->payment_plan_type);
        $this->assertEquals(500.00, $order->deposit_amount);
    }

    /** @test */
    public function it_marks_installment_as_paid()
    {
        $order = Order::factory()->create(['total' => 1000.00]);
        $installment = PaymentInstallment::factory()->create([
            'order_id' => $order->id,
            'amount' => 500.00,
            'status' => 'pending',
        ]);

        $service = new PaymentInstallmentService();
        $success = $service->markInstallmentPaid($installment, 'test_transaction_123');

        $this->assertTrue($success);
        $this->assertEquals('paid', $installment->fresh()->status);
        $this->assertEquals('test_transaction_123', $installment->fresh()->payment_transaction_id);
        $this->assertNotNull($installment->fresh()->paid_at);
    }

    /** @test */
    public function it_gets_next_pending_installment()
    {
        $order = Order::factory()->create(['total' => 1000.00]);
        PaymentInstallment::factory()->create([
            'order_id' => $order->id,
            'installment_number' => 1,
            'status' => 'paid',
        ]);
        $pendingInstallment = PaymentInstallment::factory()->create([
            'order_id' => $order->id,
            'installment_number' => 2,
            'status' => 'pending',
        ]);

        $service = new PaymentInstallmentService();
        $nextInstallment = $service->getNextInstallmentToPay($order);

        $this->assertNotNull($nextInstallment);
        $this->assertEquals($pendingInstallment->id, $nextInstallment->id);
        $this->assertEquals(2, $nextInstallment->installment_number);
    }

    /** @test */
    public function it_determines_when_all_installments_are_paid()
    {
        $order = Order::factory()->create(['total' => 1000.00]);
        PaymentInstallment::factory()->create([
            'order_id' => $order->id,
            'installment_number' => 1,
            'status' => 'paid',
        ]);
        PaymentInstallment::factory()->create([
            'order_id' => $order->id,
            'installment_number' => 2,
            'status' => 'pending',
        ]);

        $service = new PaymentInstallmentService();
        $this->assertFalse($service->areAllInstallmentsPaid($order));

        // Mark second installment as paid
        $order->paymentInstallments()->where('status', 'pending')->update(['status' => 'paid']);

        $this->assertTrue($service->areAllInstallmentsPaid($order));
    }
}