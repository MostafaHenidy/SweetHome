@php
    $options = json_decode($get('variant_options_json') ?? '[]', true);
    $combinations = [[]];
    foreach ($options as $opt) {
        if (!empty($opt['values'])) {
            $new = [];
            foreach ($combinations as $c) {
                foreach ($opt['values'] as $v) {
                    $new[] = array_merge($c, [$opt['name'] => $v]);
                }
            }
            $combinations = $new;
        }
    }
    $total = count($combinations);
    $allCombinations = $combinations;
@endphp
<div>
    @if($total === 0)
        <div class="text-center py-3">
            <svg class="mx-auto h-6 w-6 text-gray-300 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <p class="text-xs text-gray-400">Nessuna combinazione</p>
        </div>
    @else
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-gray-600">Combinazioni:</span>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $total > 50 ? 'bg-red-100 text-red-800' : ($total > 20 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                {{ $total }} varianti
            </span>
        </div>
        <div class="max-h-32 overflow-y-auto border border-gray-200 rounded divide-y divide-gray-100">
            @foreach(array_slice($allCombinations, 0, 10) as $combo)
                <div class="px-2 py-1.5 text-xs text-gray-600">
                    {{ implode(' + ', array_values($combo)) }}
                </div>
            @endforeach
            @if($total > 10)
                <div class="px-2 py-1.5 text-xs text-gray-400 italic bg-gray-50">
                    ... e altre {{ $total - 10 }} combinazioni
                </div>
            @endif
        </div>
    @endif
</div>
