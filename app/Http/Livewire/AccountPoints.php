<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class AccountPoints extends Component
{
    use WithPagination;

    public string $type = '';

    public function updatingType(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();

        $transactions = $user->pointsTransactions()
            ->when($this->type !== '', fn ($query) => $query->where('type', $this->type))
            ->latest()
            ->paginate(8);

        return view('livewire.account-points', [
            'user' => $user,
            'transactions' => $transactions,
        ]);
    }
}
