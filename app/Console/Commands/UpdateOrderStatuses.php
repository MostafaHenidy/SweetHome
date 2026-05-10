<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\PaymentInstallmentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateOrderStatuses extends Command
{
    protected $signature = 'orders:update-statuses
                            {--order= : Specific order number to update}';

    protected $description = 'Update order statuses based on payment installment progress';

    public function handle(): int
    {
        $orderNumber = $this->option('order');
        $installmentService = app(PaymentInstallmentService::class);

        $query = Order::with('paymentInstallments')
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'cancelled');

        if ($orderNumber) {
            $query->where('order_number', $orderNumber);
        }

        $orders = $query->get();
        $updatedCount = 0;

        foreach ($orders as $order) {
            try {
                $oldStatus = $order->status;
                $newStatus = $this->determineOrderStatus($order, $installmentService);

                if ($oldStatus !== $newStatus) {
                    $order->update(['status' => $newStatus]);
                    $updatedCount++;

                    $this->line("✓ Updated order {$order->order_number}: {$oldStatus} → {$newStatus}");
                }
            } catch (\Exception $e) {
                Log::error('Failed to update order status', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Updated {$updatedCount} order statuses.");
        return Command::SUCCESS;
    }

    private function determineOrderStatus(Order $order, PaymentInstallmentService $installmentService): string
    {
        // For orders without installments, keep existing logic
        if (!$order->paymentInstallments()->exists()) {
            return $order->status;
        }

        $allPaid = $installmentService->areAllInstallmentsPaid($order);
        $nextInstallment = $installmentService->getNextInstallmentToPay($order);

        // Check payment progress and determine status
        if ($allPaid) {
            // All installments paid
            if ($order->service_type === 'shipping_and_construction') {
                if ($order->status === 'construction_completed') {
                    return 'completed';
                }
                return $order->status === 'construction_in_progress' ? 'construction_in_progress' : 'construction_scheduled';
            } else {
                return 'processing';
            }
        }

        // Not all installments paid yet
        if ($nextInstallment) {
            switch ($nextInstallment->due_stage) {
                case 'deposit':
                    return 'pending_deposit';
                case 'on_delivery':
                    return $order->status === 'shipped' ? 'shipped' : 'deposit_paid';
                case 'on_construction':
                    return $order->status === 'delivered' ? 'delivered' : 'construction_scheduled';
            }
        }

        return $order->status;
    }
}