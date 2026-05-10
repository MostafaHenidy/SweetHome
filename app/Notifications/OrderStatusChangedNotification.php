<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Order $order;

    protected string $oldStatus;

    protected string $newStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, string $oldStatus, string $newStatus)
    {
        $this->order = $order;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
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
        $subject = "Aggiornamento Ordine #{$this->order->order_number}";

        return (new MailMessage)
            ->subject($subject)
            ->greeting($this->getGreeting($notifiable))
            ->line("Il tuo ordine #{$this->order->order_number} è stato aggiornato.")
            ->line('Stato precedente: '.$this->getStatusLabel($this->oldStatus))
            ->line('Nuovo stato: '.$this->getStatusLabel($this->newStatus))
            ->action('Visualizza Dettagli', route('account.orders'))
            ->line('Grazie per aver scelto SweetHome!');
    }

    /**
     * Get the Firebase representation of the notification.
     */
    public function toFirebase($notifiable): array
    {
        return [
            'title' => 'Aggiornamento Ordine',
            'body' => "Ordine #{$this->order->order_number}: ".$this->getStatusLabel($this->newStatus),
            'data' => [
                'type' => 'order_status_changed',
                'order_id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'old_status' => $this->oldStatus,
                'new_status' => $this->newStatus,
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
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'message' => "Stato ordine #{$this->order->order_number} modificato da '{$this->getStatusLabel($this->oldStatus)}' a '{$this->getStatusLabel($this->newStatus)}'",
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

        return $preferences->email_order_status_changed;
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

        return $preferences->firebase_order_status_changed;
    }

    protected function getGreeting($notifiable): string
    {
        if ($notifiable instanceof User) {
            return "Ciao {$notifiable->name},";
        }

        return 'Gentile Cliente,';
    }

    protected function getStatusLabel(string $status): string
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

        return $statuses[$status] ?? ucfirst($status);
    }
}
