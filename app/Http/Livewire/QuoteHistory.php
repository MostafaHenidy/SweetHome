<?php

namespace App\Http\Livewire;

use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;

class QuoteHistory extends Component
{
    use WithPagination;

    public function render()
    {
        $quoteRequests = Order::query()
            ->where('customer_email', auth()->user()->email)
            ->where('type', 'quote_request')
            ->latest()
            ->paginate(10);

        return view('livewire.quote-history', [
            'quoteRequests' => $quoteRequests,
        ]);
    }
}
