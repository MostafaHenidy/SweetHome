@php
    $options = json_decode($get('variant_options_json') ?? '[]', true);
@endphp
<div>
    @forelse($options as $index => $opt)
        <div class="mb-2 p-2 bg-gray-50 rounded border border-gray-200">
            <div class="flex items-center justify-between mb-1">
                <strong class="text-sm text-gray-700">{{ $opt['name'] }}:</strong>
            </div>
            <div class="flex flex-wrap gap-1">
                @foreach($opt['values'] as $val)
                    <span class="inline-flex items-center px-2 py-0.5 bg-indigo-100 text-indigo-800 text-xs rounded font-medium">
                        {{ $val }}
                    </span>
                @endforeach
            </div>
        </div>
    @empty
        <div class="text-center py-4">
            <svg class="mx-auto h-8 w-8 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            <p class="text-sm text-gray-400">Nessuna opzione configurata</p>
            <p class="text-xs text-gray-400 mt-1">Aggiungi un'opzione usando il form a sinistra</p>
        </div>
    @endforelse
</div>
