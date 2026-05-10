<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuoteRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Order $quote;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $quote)
    {
        $this->quote = $quote;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        // For admin notifications, always send email and Firebase
        if ($this->isAdmin($notifiable)) {
            $channels[] = 'mail';
            $channels[] = 'firebase';

            return $channels;
        }

        // Check notification preferences for customer
        if ($this->shouldSendEmail($notifiable)) {
            $channels[] = 'mail';
        }

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
        if ($this->isAdmin($notifiable)) {
            $subject = "🆕 Nuova Richiesta Preventivo #{$this->quote->order_number}";

            return (new MailMessage)
                ->subject($subject)
                ->greeting('Nuova Richiesta Preventivo Ricevuta')
                ->line('È stata ricevuta una nuova richiesta di preventivo:')
                ->line("• Numero: #{$this->quote->order_number}")
                ->line("• Cliente: {$this->quote->customer_name}")
                ->line("• Email: {$this->quote->customer_email}")
                ->line("• Telefono: {$this->quote->customer_phone}")
                ->line("• Data: {$this->quote->created_at->format('d/m/Y H:i')}")
                ->action('Gestisci Preventivo', route('filament.admin.pages.dashboard'))
                ->line('Grazie per il tuo lavoro!');
        }

        $subject = "Richiesta Preventivo #{$this->quote->order_number} Inviata";

        return (new MailMessage)
            ->subject($subject)
            ->greeting("Ciao {$this->quote->customer_name},")
            ->line('La tua richiesta di preventivo è stata ricevuta con successo.')
            ->line("• Numero richiesta: #{$this->quote->order_number}")
            ->line("• Data: {$this->quote->created_at->format('d/m/Y H:i')}")
            ->line('Il nostro team ti contatterà presto con il preventivo.')
            ->line('Grazie per aver scelto SweetHome!');
    }

    /**
     * Get the Firebase representation of the notification.
     */
    public function toFirebase($notifiable): array
    {
        if ($this->isAdmin($notifiable)) {
            return [
                'title' => 'Nuova Richiesta Preventivo',
                'body' => "Da {$this->quote->customer_name} - #{$this->quote->order_number}",
                'data' => [
                    'type' => 'quote_request_created',
                    'quote_id' => $this->quote->id,
                    'quote_number' => $this->quote->order_number,
                    'customer_name' => $this->quote->customer_name,
                    'customer_email' => $this->quote->customer_email,
                    'click_action' => route('filament.admin.pages.dashboard'),
                ],
            ];
        }

        return [
            'title' => 'Richiesta Preventivo Inviata',
            'body' => "Richiesta #{$this->quote->order_number} ricevuta con successo",
            'data' => [
                'type' => 'quote_request_created',
                'quote_id' => $this->quote->id,
                'quote_number' => $this->quote->order_number,
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
            'customer_name' => $this->quote->customer_name,
            'customer_email' => $this->quote->customer_email,
            'message' => $this->isAdmin($notifiable)
                ? "Nuova richiesta preventivo #{$this->quote->order_number} da {$this->quote->customer_name}"
                : "La tua richiesta preventivo #{$this->quote->order_number} è stata inviata con successo",
        ];
    }

    /**
     * Determine if the notifiable is an admin
     */
    protected function isAdmin($notifiable): bool
    {
        // Check if this is being sent to an admin email or admin user
        if ($notifiable instanceof User) {
            return $notifiable->hasRole('admin') || $notifiable->hasRole('super-admin');
        }

        // Check if it's an admin email
        $adminEmails = config('app.admin_emails', []);
        if (isset($notifiable->email)) {
            return in_array($notifiable->email, $adminEmails);
        }

        return false;
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

        return $preferences->email_quote_created;
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

        return $preferences->firebase_quote_created;
    }
}
