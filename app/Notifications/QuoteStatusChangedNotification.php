<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuoteStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Order $quote;

    protected string $oldStatus;

    protected string $newStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $quote, string $oldStatus, string $newStatus)
    {
        $this->quote = $quote;
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
        $subject = "Aggiornamento Preventivo #{$this->quote->order_number}";

        return (new MailMessage)
            ->subject($subject)
            ->greeting($this->getGreeting($notifiable))
            ->line("Il tuo preventivo #{$this->quote->order_number} è stato aggiornato.")
            ->line('Stato precedente: '.$this->getStatusLabel($this->oldStatus))
            ->line('Nuovo stato: '.$this->getStatusLabel($this->newStatus))
            ->when($this->newStatus === 'quote_sent', function ($message) {
                $message->line('Il preventivo è pronto per la revisione.');
            })
            ->action('Visualizza Preventivo', route('account.quotes'))
            ->line('Grazie per aver scelto SweetHome!');
    }

    /**
     * Get the Firebase representation of the notification.
     */
    public function toFirebase($notifiable): array
    {
        $title = 'Aggiornamento Preventivo';
        $body = "Preventivo #{$this->quote->order_number}: ".$this->getStatusLabel($this->newStatus);

        if ($this->newStatus === 'quote_sent') {
            $body = "Preventivo #{$this->quote->order_number} disponibile per la revisione";
        }

        return [
            'title' => $title,
            'body' => $body,
            'data' => [
                'type' => 'quote_status_changed',
                'quote_id' => $this->quote->id,
                'quote_number' => $this->quote->order_number,
                'old_status' => $this->oldStatus,
                'new_status' => $this->newStatus,
                'click_action' => route('account.quotes'),
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
            'quote_id' => $this->quote->id,
            'quote_number' => $this->quote->order_number,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'message' => "Stato preventivo #{$this->quote->order_number} modificato da '{$this->getStatusLabel($this->oldStatus)}' a '{$this->getStatusLabel($this->newStatus)}'",
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

        return $preferences->email_quote_status_changed;
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

        return $preferences->firebase_quote_status_changed;
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
            'quote_sent' => 'Preventivo Inviato',
            'accepted' => 'Accettato',
            'rejected' => 'Rifiutato',
            'expired' => 'Scaduto',
            'cancelled' => 'Annullato',
        ];

        return $statuses[$status] ?? ucfirst($status);
    }
}
