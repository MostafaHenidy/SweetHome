<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentInstallment;

class PaymentInstallmentService
{
    /**
     * Create payment installments based on order total and service type
     */
    public function createInstallments(Order $order): void
    {
        // Clear any existing installments
        $order->paymentInstallments()->delete();

        $total = (float) $order->total;
        $installments = [];

        if ($order->service_type === 'shipping_and_construction') {
            // 50-30-20 plan for shipping and construction
            $installments = [
                [
                    'installment_number' => 1,
                    'amount' => round($total * 0.50, 2),
                    'percentage' => 50,
                    'due_stage' => 'deposit',
                ],
                [
                    'installment_number' => 2,
                    'amount' => round($total * 0.30, 2),
                    'percentage' => 30,
                    'due_stage' => 'on_delivery',
                ],
                [
                    'installment_number' => 3,
                    'amount' => round($total * 0.20, 2),
                    'percentage' => 20,
                    'due_stage' => 'on_construction',
                ],
            ];
        } else {
            // 50-50 plan for shipping only
            $installments = [
                [
                    'installment_number' => 1,
                    'amount' => round($total * 0.50, 2),
                    'percentage' => 50,
                    'due_stage' => 'deposit',
                ],
                [
                    'installment_number' => 2,
                    'amount' => round($total * 0.50, 2),
                    'percentage' => 50,
                    'due_stage' => 'on_delivery',
                ],
            ];
        }

        // Create installment records
        foreach ($installments as $installment) {
            PaymentInstallment::create(array_merge($installment, [
                'order_id' => $order->id,
                'status' => $installment['due_stage'] === 'deposit' ? 'pending' : 'pending',
            ]));
        }

        // Update order with payment plan type and deposit amount
        $order->update([
            'payment_plan_type' => $order->service_type === 'shipping_and_construction' ? '50-30-20' : '50-50',
            'deposit_amount' => $installments[0]['amount'],
        ]);
    }

    /**
     * Mark an installment as paid
     */
    public function markInstallmentPaid(
        PaymentInstallment $installment,
        string $transactionId,
        ?\DateTimeInterface $paidAt = null
    ): bool {
        return $installment->update([
            'status' => 'paid',
            'payment_transaction_id' => $transactionId,
            'paid_at' => $paidAt ?? now(),
        ]);
    }

    /**
     * Get the next installment to pay for an order
     */
    public function getNextInstallmentToPay(Order $order): ?PaymentInstallment
    {
        return $order->paymentInstallments()
            ->where('status', 'pending')
            ->orderBy('installment_number')
            ->first();
    }

    /**
     * Calculate total paid amount for an order
     */
    public function getTotalPaidAmount(Order $order): float
    {
        return $order->paymentInstallments()
            ->where('status', 'paid')
            ->sum('amount');
    }

    /**
     * Check if all installments are paid
     */
    public function areAllInstallmentsPaid(Order $order): bool
    {
        return $order->paymentInstallments()
            ->where('status', 'pending')
            ->count() === 0;
    }

    /**
     * Get installments summary for display
     */
    public function getInstallmentsSummary(Order $order): array
    {
        $installments = $order->paymentInstallments;
        $summary = [];

        foreach ($installments as $installment) {
            $summary[] = [
                'number' => $installment->installment_number,
                'amount' => $installment->amount,
                'formatted_amount' => $installment->formatted_amount,
                'percentage' => $installment->percentage,
                'due_stage' => $installment->due_stage,
                'due_stage_label' => $installment->due_stage_label,
                'status' => $installment->status,
                'is_pending' => $installment->isPending(),
                'is_paid' => $installment->isPaid(),
            ];
        }

        return $summary;
    }
}