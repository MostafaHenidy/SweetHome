<?php

namespace App\Http\Livewire;

use App\Models\NotificationPreference;
use Livewire\Component;

class NotificationPreferences extends Component
{
    public $email_order_created = true;

    public $email_order_status_changed = true;

    public $email_quote_created = true;

    public $email_quote_status_changed = true;

    public $firebase_order_created = true;

    public $firebase_order_status_changed = true;

    public $firebase_quote_created = true;

    public $firebase_quote_status_changed = true;

    public $database_order_created = true;

    public $database_order_status_changed = true;

    public $database_quote_created = true;

    public $database_quote_status_changed = true;

    public function mount()
    {
        $preferences = auth()->user()->notificationPreferences;
        if ($preferences) {
            $this->email_order_created = $preferences->email_order_created;
            $this->email_order_status_changed = $preferences->email_order_status_changed;
            $this->email_quote_created = $preferences->email_quote_created;
            $this->email_quote_status_changed = $preferences->email_quote_status_changed;
            $this->firebase_order_created = $preferences->firebase_order_created;
            $this->firebase_order_status_changed = $preferences->firebase_order_status_changed;
            $this->firebase_quote_created = $preferences->firebase_quote_created;
            $this->firebase_quote_status_changed = $preferences->firebase_quote_status_changed;
            $this->database_order_created = $preferences->database_order_created;
            $this->database_order_status_changed = $preferences->database_order_status_changed;
            $this->database_quote_created = $preferences->database_quote_created;
            $this->database_quote_status_changed = $preferences->database_quote_status_changed;
        }
    }

    public function save()
    {
        NotificationPreference::updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'email_order_created' => $this->email_order_created,
                'email_order_status_changed' => $this->email_order_status_changed,
                'email_quote_created' => $this->email_quote_created,
                'email_quote_status_changed' => $this->email_quote_status_changed,
                'firebase_order_created' => $this->firebase_order_created,
                'firebase_order_status_changed' => $this->firebase_order_status_changed,
                'firebase_quote_created' => $this->firebase_quote_created,
                'firebase_quote_status_changed' => $this->firebase_quote_status_changed,
                'database_order_created' => $this->database_order_created,
                'database_order_status_changed' => $this->database_order_status_changed,
                'database_quote_created' => $this->database_quote_created,
                'database_quote_status_changed' => $this->database_quote_status_changed,
            ]
        );

        $this->dispatch('notification-preferences-saved');
    }

    public function render()
    {
        return view('livewire.notification-preferences');
    }
}
