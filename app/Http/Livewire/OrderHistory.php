<?php

namespace App\Http\Livewire;

use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;

class OrderHistory extends Component
{
    use WithPagination;

    public function render()
    {
        $orders = Order::query()
            ->where('customer_email', auth()->user()->email)
            ->where('type', 'direct') // Only show direct orders, not quote requests
            ->latest()
            ->paginate(10);

        return view('livewire.order-history', [
            'orders' => $orders,
        ]);
    }
}
