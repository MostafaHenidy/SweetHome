<div class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-brand-slate/10 border border-white/5 rounded-sm p-6">
            <p class="text-sm text-gray-400 mb-2">Saldo punti</p>
            <p class="text-3xl font-serif text-brand-light">{{ number_format($user->points_balance) }}</p>
        </div>
        <div class="bg-brand-slate/10 border border-white/5 rounded-sm p-6">
            <p class="text-sm text-gray-400 mb-2">Valore equivalente</p>
            <p class="text-3xl font-serif text-brand-gold">€{{ number_format($user->points_value_in_euros, 2, ',', '.') }}</p>
        </div>
    </div>

    <div class="bg-brand-slate/10 border border-white/5 rounded-sm p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <h2 class="text-xl font-serif text-brand-light">Storico punti</h2>
            <select wire:model.live="type" class="px-3 py-2 bg-brand-dark/50 border border-white/10 text-brand-light text-sm rounded-sm focus:outline-none focus:border-brand-gold">
                <option value="">Tutti</option>
                <option value="earned">Guadagnati</option>
                <option value="spent">Spesi</option>
                <option value="admin_adjustment">Adeguamenti</option>
            </select>
        </div>

        @if($transactions->count())
            <div class="space-y-3">
                @foreach($transactions as $transaction)
                    <div class="flex items-start justify-between gap-4 border-b border-white/5 pb-3 last:border-0 last:pb-0">
                        <div>
                            <p class="text-sm text-brand-light">{{ $transaction->description }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $transaction->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <span class="text-sm font-semibold {{ $transaction->points >= 0 ? 'text-green-400' : 'text-red-400' }}">
                            {{ $transaction->points >= 0 ? '+' : '' }}{{ number_format($transaction->points) }}
                        </span>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $transactions->links() }}
            </div>
        @else
            <p class="text-sm text-gray-400">Non ci sono ancora movimenti punti.</p>
        @endif
    </div>
</div>
