<?php

namespace App\Notifications;

use App\Models\PaymentInstallment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class PaymentReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected PaymentInstallment $installment
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $order = $this->installment->order;
        $installmentNumber = $this->installment->installment_number;
        $totalInstallments = $order->paymentInstallments->count();

        return (new MailMessage)
            ->subject("Ricorda: Pagamento #{installmentNumber} di {$order->order_number} in scadenza")
            ->greeting("Ciao {$notifiable->name},")
            ->line("Questo è un promemoria per il tuo pagamento in rate per l'ordine **{$order->order_number}**.")
            ->line("**Dettagli Installment:**")
            ->line("- Importo: €" . number_format($this->installment->amount, 2, ',', '.'))
            ->line("- Scadenza: {$this->installment->due_stage_label}")
            ->line("- Installment {$installmentNumber} di {$totalInstallments}")
            ->action('Paga Ora', $this->getPaymentUrl())
            ->line("Grazie per il tuo acquisto!");
    }

    private function getPaymentUrl(): string
    {
        return route('payment.initiate', [
            'orderNumber' => $this->installment->order->order_number,
            'installment' => $this->installment->installment_number,
        ]);
    }

    public function toArray($notifiable): array
    {
        return [
            'installment_id' => $this->installment->id,
            'order_number' => $this->installment->order->order_number,
            'amount' => $this->installment->amount,
            'due_stage' => $this->installment->due_stage,
            'payment_url' => $this->getPaymentUrl(),
        ];
    }
}