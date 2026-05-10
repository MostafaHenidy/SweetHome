<div>
    <button wire:click="addToCart()"
            wire:loading.attr="disabled"
            class="w-full inline-flex items-center justify-center rounded-sm bg-[#b89252] px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-[#f3ede3] hover:bg-[#c8a35f] transition-colors cursor-pointer">
        <span wire:loading.remove>
            @if($added)
                Aggiunto!
            @else
                Aggiungi al carrello
            @endif
        </span>
        <span wire:loading>Aggiungendo...</span>
    </button>
</div>