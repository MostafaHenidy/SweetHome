<div class="space-y-6">
    <div class="bg-brand-slate/10 border border-white/5 rounded-sm p-6">
        <h3 class="text-lg font-serif text-brand-light mb-4">Preferenze Notifiche Email</h3>

        <div class="space-y-4">
            <label class="flex items-center justify-between">
                <span class="text-sm text-gray-300">Nuovi ordini</span>
                <input type="checkbox" wire:model="email_order_created" class="rounded border-gray-300 text-brand-gold focus:ring-brand-gold">
            </label>

            <label class="flex items-center justify-between">
                <span class="text-sm text-gray-300">Aggiornamenti stato ordini</span>
                <input type="checkbox" wire:model="email_order_status_changed" class="rounded border-gray-300 text-brand-gold focus:ring-brand-gold">
            </label>

            <label class="flex items-center justify-between">
                <span class="text-sm text-gray-300">Nuove richieste preventivo</span>
                <input type="checkbox" wire:model="email_quote_created" class="rounded border-gray-300 text-brand-gold focus:ring-brand-gold">
            </label>

            <label class="flex items-center justify-between">
                <span class="text-sm text-gray-300">Aggiornamenti preventivi</span>
                <input type="checkbox" wire:model="email_quote_status_changed" class="rounded border-gray-300 text-brand-gold focus:ring-brand-gold">
            </label>
        </div>
    </div>

    <div class="bg-brand-slate/10 border border-white/5 rounded-sm p-6">
        <h3 class="text-lg font-serif text-brand-light mb-4">Preferenze Notifiche Push (Firebase)</h3>

        <div class="space-y-4">
            <label class="flex items-center justify-between">
                <span class="text-sm text-gray-300">Nuovi ordini</span>
                <input type="checkbox" wire:model="firebase_order_created" class="rounded border-gray-300 text-brand-gold focus:ring-brand-gold">
            </label>

            <label class="flex items-center justify-between">
                <span class="text-sm text-gray-300">Aggiornamenti stato ordini</span>
                <input type="checkbox" wire:model="firebase_order_status_changed" class="rounded border-gray-300 text-brand-gold focus:ring-brand-gold">
            </label>

            <label class="flex items-center justify-between">
                <span class="text-sm text-gray-300">Nuove richieste preventivo</span>
                <input type="checkbox" wire:model="firebase_quote_created" class="rounded border-gray-300 text-brand-gold focus:ring-brand-gold">
            </label>

            <label class="flex items-center justify-between">
                <span class="text-sm text-gray-300">Aggiornamenti preventivi</span>
                <input type="checkbox" wire:model="firebase_quote_status_changed" class="rounded border-gray-300 text-brand-gold focus:ring-brand-gold">
            </label>
        </div>
    </div>

    <div class="bg-brand-slate/10 border border-white/5 rounded-sm p-6">
        <h3 class="text-lg font-serif text-brand-light mb-4">Preferenze Notifiche In-App</h3>

        <div class="space-y-4">
            <label class="flex items-center justify-between">
                <span class="text-sm text-gray-300">Nuovi ordini</span>
                <input type="checkbox" wire:model="database_order_created" class="rounded border-gray-300 text-brand-gold focus:ring-brand-gold">
            </label>

            <label class="flex items-center justify-between">
                <span class="text-sm text-gray-300">Aggiornamenti stato ordini</span>
                <input type="checkbox" wire:model="database_order_status_changed" class="rounded border-gray-300 text-brand-gold focus:ring-brand-gold">
            </label>

            <label class="flex items-center justify-between">
                <span class="text-sm text-gray-300">Nuove richieste preventivo</span>
                <input type="checkbox" wire:model="database_quote_created" class="rounded border-gray-300 text-brand-gold focus:ring-brand-gold">
            </label>

            <label class="flex items-center justify-between">
                <span class="text-sm text-gray-300">Aggiornamenti preventivi</span>
                <input type="checkbox" wire:model="database_quote_status_changed" class="rounded border-gray-300 text-brand-gold focus:ring-brand-gold">
            </label>
        </div>
    </div>

    <div class="flex justify-end">
        <button wire:click="save" class="px-6 py-2 bg-brand-gold text-brand-dark font-semibold rounded-sm hover:bg-opacity-90 transition">
            Salva Preferenze
        </button>
    </div>

    @if(session()->has('message'))
        <div class="mt-4 p-4 bg-green-500/20 border border-green-500/50 text-green-400 rounded-sm">
            {{ session('message') }}
        </div>
    @endif
</div>
