<?php

namespace App\Http\Livewire;

use App\Models\UserInquiry;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class UserInquiries extends Component
{
    public $inquiries;

    public $showCreateForm = false;

    public $subject;

    public $message;

    public $product_id;

    public function mount()
    {
        $this->loadInquiries();
    }

    public function loadInquiries()
    {
        $this->inquiries = UserInquiry::with('product')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();
    }

    public function toggleCreateForm()
    {
        $this->showCreateForm = ! $this->showCreateForm;
        if (! $this->showCreateForm) {
            $this->reset(['subject', 'message', 'product_id']);
        }
    }

    public function createInquiry()
    {
        $this->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'product_id' => 'nullable|exists:products,id',
        ]);

        UserInquiry::create([
            'user_id' => Auth::id(),
            'product_id' => $this->product_id,
            'subject' => $this->subject,
            'message' => $this->message,
            'status' => 'pending',
        ]);

        $this->reset(['subject', 'message', 'product_id', 'showCreateForm']);
        $this->loadInquiries();
        $this->emit('inquiryCreated');
    }

    public function render()
    {
        return view('livewire.user-inquiries');
    }
}
