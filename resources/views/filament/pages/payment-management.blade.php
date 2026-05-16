<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Dashboard Overview --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Metodi Attivi</p>
                        <p class="text-2xl font-semibold">{{ $this->getEnabledMethodsCount() }}/5</p>
                    </div>
                    <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Stato Credenziali</p>
                        <p class="text-2xl font-semibold">
                            @php
                                $statusCount = 0;
                                $status = $this->getPaymentMethodsStatus();
                                foreach (['stripe', 'paypal', 'fawaterak'] as $method) {
                                    if ($status[$method]['has_credentials']) {
                                        $statusCount++;
                                    }
                                }
                            @endphp
                            {{ $statusCount }}/3
                        </p>
                    </div>
                    <div class="p-3 bg-green-100 dark:bg-green-900 rounded-full">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Rateizzazione</p>
                        <p class="text-2xl font-semibold">
                            {{ $this->data['enable_installments'] ? 'Attiva' : 'Disattiva' }}
                        </p>
                    </div>
                    <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-full">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Limiti Debito</p>
                        <p class="text-2xl font-semibold">
                            €{{ number_format($this->data['global_debt_limit'] ?? 0, 2, ',', '.') }}
                        </p>
                    </div>
                    <div class="p-3 bg-orange-100 dark:bg-orange-900 rounded-full">
                        <svg class="w-6 h-6 text-orange-600 dark:text-orange-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Payment Methods Status --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow mb-6">
            <h3 class="text-lg font-medium mb-4">Stato Metodi di Pagamento</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                @php
                    $methods = [
                        'fawaterak' => ['name' => 'Fawaterak', 'icon' => '💳'],
                        'stripe' => ['name' => 'Carta di Credito', 'icon' => '💳'],
                        'paypal' => ['name' => 'PayPal', 'icon' => '🅿️'],
                        'bank_transfer' => ['name' => 'Bonifico', 'icon' => '🏦'],
                        'cash_on_delivery' => ['name' => 'Contrassegno', 'icon' => '💵'],
                    ];
                    $status = $this->getPaymentMethodsStatus();
                @endphp

                @foreach ($methods as $key => $method)
                    <div class="border rounded-lg p-4 {{ $status[$key]['enabled'] ? 'border-green-500 bg-green-50 dark:bg-green-900/20' : 'border-gray-300 bg-gray-50 dark:bg-gray-700/50' }}">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-2xl">{{ $method['icon'] }}</span>
                            <span class="px-2 py-1 text-xs rounded-full {{ $status[$key]['enabled'] ? 'bg-green-200 text-green-800' : 'bg-gray-200 text-gray-800' }}">
                                {{ $status[$key]['enabled'] ? 'Attivo' : 'Disattivo' }}
                            </span>
                        </div>
                        <p class="font-medium text-sm">{{ $method['name'] }}</p>
                        @if ($key !== 'bank_transfer' && $key !== 'cash_on_delivery')
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $status[$key]['has_credentials'] ? '✅ Credenziali configurate' : '❌ Credenziali mancanti' }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Filament Form --}}
        <x-filament-panels::form wire:submit="save">
            {{ $this->form }}

            <x-filament-panels::form.actions :actions="$this->getCachedFormActions()" />
        </x-filament-panels::form>

        {{-- Security Notice --}}
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div class="ml-3">
                    <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Sicurezza delle Credenziali</h4>
                    <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                        Tutte le credenziali API sono memorizzate in modo sicuro. Assicurati di utilizzare chiavi di ambiente di produzione
                        solo in ambiente di produzione e di limitare le autorizzazioni delle chiavi API ai permessi necessari.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>