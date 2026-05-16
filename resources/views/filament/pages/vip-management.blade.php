<x-filament-panels::page>
    <div class="space-y-8">
        <x-filament-panels::form wire:submit="saveSettings">
            {{ $this->form }}

            <x-filament-panels::form.actions :actions="$this->getCachedFormActions()" />
        </x-filament-panels::form>

        {{ $this->table }}
    </div>
</x-filament-panels::page>
