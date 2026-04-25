<div class="space-y-2">
    @if($appliedCoupon)
        <div class="flex items-center justify-between rounded-sm border border-brand-gold/30 bg-brand-gold/5 px-3 py-2 text-sm">
            <div>
                <p class="text-brand-light">{{ $appliedCoupon['code'] }}</p>
                <p class="text-xs text-green-400">Sconto: -€{{ number_format($appliedCoupon['discount'], 2, ',', '.') }}</p>
            </div>
            <button wire:click="remove" class="text-xs uppercase tracking-wider text-brand-gold hover:text-brand-light">Rimuovi</button>
        </div>
    @else
        <div class="flex gap-2">
            <input type="text" wire:model.defer="code" placeholder="Coupon"
                   class="min-w-0 flex-1 px-3 py-2 bg-brand-dark/50 border border-white/10 text-brand-light placeholder-gray-500 text-sm rounded-sm focus:outline-none focus:border-brand-gold transition">
            <button wire:click="apply" wire:loading.attr="disabled" class="px-4 py-2 bg-brand-gold text-brand-dark text-xs font-semibold uppercase tracking-wider rounded-sm disabled:opacity-60">
                Applica
            </button>
        </div>
        @if($error)
            <p class="text-xs text-red-400">{{ $error }}</p>
        @endif
    @endif
</div>
