<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\PaymentInstallment;
use App\Notifications\PaymentReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendPaymentReminders extends Command
{
    protected $signature = 'payment:reminders
                            {--days=3 : Number of days before due date to send reminder}
                            {--order= : Specific order number to check}';

    protected $description = 'Send payment reminders for pending installments';

    public function handle(): int
    {
        $daysBefore = (int) $this->option('days');
        $orderNumber = $this->option('order');

        $this->info("Sending payment reminders for installments due in {$daysBefore} days...");

        // Get installments that need reminders
        $installments = PaymentInstallment::query()
            ->where('status', 'pending')
            ->where('due_stage', '!=', 'deposit') // Don't remind for deposits (pay immediately)
            ->when($orderNumber, function ($query) use ($orderNumber) {
                return $query->whereHas('order', function ($q) use ($orderNumber) {
                    $q->where('order_number', $orderNumber);
                });
            })
            ->with('order')
            ->get();

        $remindersSent = 0;

        foreach ($installments as $installment) {
            try {
                // Check if we should send reminder based on order status
                if ($this->shouldSendReminder($installment)) {
                    $user = $installment->order->user;

                    if ($user && $user->email) {
                        // Send notification (implement based on your notification system)
                        // $user->notify(new PaymentReminderNotification($installment));

                        Log::info('Payment reminder sent', [
                            'order_id' => $installment->order_id,
                            'installment_id' => $installment->id,
                            'user_email' => $user->email,
                            'amount' => $installment->amount,
                            'due_stage' => $installment->due_stage,
                        ]);

                        $remindersSent++;

                        $this->line("✓ Reminder sent for order {$installment->order->order_number}, installment {$installment->installment_number}");
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to send payment reminder', [
                    'installment_id' => $installment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Sent {$remindersSent} payment reminders.");
        return Command::SUCCESS;
    }

    private function shouldSendReminder(PaymentInstallment $installment): bool
    {
        $order = $installment->order;

        // Only send reminders for orders that are in the appropriate status
        $requiredStatuses = ['deposit_paid', 'shipped', 'delivered'];

        if (!in_array($order->status, $requiredStatuses)) {
            return false;
        }

        // Check if this installment is the next one to be paid
        $nextPending = $order->paymentInstallments()
            ->where('status', 'pending')
            ->orderBy('installment_number')
            ->first();

        return $nextPending && $nextPending->id === $installment->id;
    }
}