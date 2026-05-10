<?php

namespace App\Http\Livewire;

use App\Models\Order;
use Livewire\Component;

class QuoteSuccessPage extends Component
{
    public $order;

    public $orderNumber;

    public function mount($orderNumber)
    {
        $this->orderNumber = $orderNumber;
        $this->order = Order::with(['customer', 'items'])
            ->where('order_number', $orderNumber)
            ->where('type', 'quote_request')
            ->first();

        if (! $this->order) {
            abort(404, 'Quote request not found');
        }
    }

    public function render()
    {
        return view('livewire.quote-success-page');
    }
}
