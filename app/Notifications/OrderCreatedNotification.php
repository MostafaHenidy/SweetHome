<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Order $order;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        // Check notification preferences for email
        if ($this->shouldSendEmail($notifiable)) {
            $channels[] = 'mail';
        }

        // Check notification preferences for Firebase
        if ($this->shouldSendFirebase($notifiable)) {
            $channels[] = 'firebase';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->order->type === 'quote_request'
            ? "Nuova Richiesta Preventivo #{$this->order->order_number}"
            : "Nuovo Ordine #{$this->order->order_number}";

        return (new MailMessage)
            ->subject($subject)
            ->greeting($this->getGreeting($notifiable))
            ->line($this->getOrderDescription())
            ->line('Totale: €'.number_format($this->order->total, 2, ',', '.'))
            ->line('Stato: '.$this->getStatusLabel())
            ->action('Visualizza Dettagli', route('account.orders'))
            ->line('Grazie per aver scelto SweetHome!');
    }

    /**
     * Get the Firebase representation of the notification.
     */
    public function toFirebase($notifiable): array
    {
        return [
            'title' => $this->order->type === 'quote_request'
                ? 'Nuova Richiesta Preventivo'
                : 'Nuovo Ordine',
            'body' => "Ordine #{$this->order->order_number} - Totale: €".number_format($this->order->total, 2, ',', '.'),
            'data' => [
                'type' => 'order_created',
                'order_id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'order_type' => $this->order->type,
                'total' => (float) $this->order->total,
                'status' => $this->order->status,
                'click_action' => route('account.orders'),
            ],
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'order_type' => $this->order->type,
            'total' => (float) $this->order->total,
            'status' => $this->order->status,
            'message' => $this->order->type === 'quote_request'
                ? "Richiesta preventivo #{$this->order->order_number} inviata con successo"
                : "Ordine #{$this->order->order_number} creato con successo",
        ];
    }

    /**
     * Determine if email should be sent based on user preferences
     */
    protected function shouldSendEmail($notifiable): bool
    {
        if (! $notifiable instanceof User) {
            return true; // Default to sending email for non-user notifiables
        }

        $preferences = $notifiable->notificationPreferences;
        if (! $preferences) {
            return true; // Default to sending if no preferences set
        }

        return $this->order->type === 'quote_request'
            ? $preferences->email_quote_created
            : $preferences->email_order_created;
    }

    /**
     * Determine if Firebase notification should be sent based on user preferences
     */
    protected function shouldSendFirebase($notifiable): bool
    {
        if (! $notifiable instanceof User) {
            return false; // Don't send Firebase to non-user notifiables
        }

        $preferences = $notifiable->notificationPreferences;
        if (! $preferences) {
            return true; // Default to sending if no preferences set
        }

        return $this->order->type === 'quote_request'
            ? $preferences->firebase_quote_created
            : $preferences->firebase_order_created;
    }

    protected function getGreeting($notifiable): string
    {
        if ($notifiable instanceof User) {
            return "Ciao {$notifiable->name},";
        }

        return 'Gentile Cliente,';
    }

    protected function getOrderDescription(): string
    {
        if ($this->order->type === 'quote_request') {
            return 'La tua richiesta di preventivo è stata ricevuta con successo.';
        }

        return 'Il tuo ordine è stato ricevuto con successo.';
    }

    protected function getStatusLabel(): string
    {
        $statuses = [
            'pending' => 'In Attesa',
            'processing' => 'In Lavorazione',
            'shipped' => 'Spedito',
            'completed' => 'Completato',
            'cancelled' => 'Annullato',
            'awaiting_payment' => 'In Attesa Pagamento',
            'quote_sent' => 'Preventivo Inviato',
        ];

        return $statuses[$this->order->status] ?? ucfirst($this->order->status);
    }
}
