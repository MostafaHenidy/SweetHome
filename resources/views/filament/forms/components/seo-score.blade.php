@php
    $metaTitle = $get('meta_title') ?? '';
    $metaDescription = $get('meta_description') ?? '';
    $focusKeyword = $get('focus_keyword') ?? '';
    $name = $get('name') ?? '';

    $titleScore = 0;
    $titleLength = mb_strlen($metaTitle);
    if ($titleLength == 0) {
        $titleLabel = 'Assente';
        $titleColor = 'text-red-600';
    } elseif ($titleLength < 30) {
        $titleLabel = 'Corto (' . $titleLength . ')';
        $titleColor = 'text-yellow-600';
    } elseif ($titleLength > 60) {
        $titleLabel = 'Lungo (' . $titleLength . ')';
        $titleColor = 'text-yellow-600';
    } else {
        $titleLabel = 'Ottimo (' . $titleLength . ')';
        $titleColor = 'text-green-600';
    }

    $descScore = 0;
    $descLength = mb_strlen($metaDescription);
    if ($descLength == 0) {
        $descLabel = 'Assente';
        $descColor = 'text-red-600';
    } elseif ($descLength < 120) {
        $descLabel = 'Corta (' . $descLength . ')';
        $descColor = 'text-yellow-600';
    } elseif ($descLength > 160) {
        $descLabel = 'Lunga (' . $descLength . ')';
        $descColor = 'text-yellow-600';
    } else {
        $descLabel = 'Ottimo (' . $descLength . ')';
        $descColor = 'text-green-600';
    }

    $keywordInTitle = $focusKeyword && $metaTitle && mb_stripos($metaTitle, $focusKeyword) !== false;
    $keywordInDesc = $focusKeyword && $metaDescription && mb_stripos($metaDescription, $focusKeyword) !== false;

    $totalScore = 0;
    if ($metaTitle) $totalScore += 30;
    if ($titleLength >= 30 && $titleLength <= 60) $totalScore += 10;
    if ($metaDescription) $totalScore += 30;
    if ($descLength >= 120 && $descLength <= 160) $totalScore += 15;
    if ($focusKeyword) $totalScore += 15;
    if ($keywordInTitle) $totalScore += 5;
    if ($keywordInDesc) $totalScore += 10;

    $scorePercent = min(100, $totalScore);
    if ($scorePercent >= 80) {
        $scoreColor = 'bg-green-500';
        $scoreLabel = 'Ottimo';
    } elseif ($scorePercent >= 50) {
        $scoreColor = 'bg-yellow-500';
        $scoreLabel = 'Da migliorare';
    } else {
        $scoreColor = 'bg-red-500';
        $scoreLabel = 'Critico';
    }
@endphp
<div class="space-y-3">
    {{-- Score Ring --}}
    <div class="flex items-center justify-center">
        <div class="relative inline-flex items-center justify-center w-16 h-16">
            <svg class="w-16 h-16 transform -rotate-90">
                <circle cx="32" cy="32" r="28" stroke="#e5e7eb" stroke-width="4" fill="none" />
                <circle cx="32" cy="32" r="28" stroke="{{ $scorePercent >= 80 ? '#22c55e' : ($scorePercent >= 50 ? '#eab308' : '#ef4444') }}"
                    stroke-width="4" fill="none"
                    stroke-dasharray="{{ round($scorePercent * 1.76) }} 176"
                    stroke-linecap="round" />
            </svg>
            <span class="absolute text-sm font-bold text-gray-700">{{ $scorePercent }}</span>
        </div>
    </div>
    <div class="text-center">
        <span class="text-xs font-medium px-2 py-0.5 rounded {{ str_replace('bg-', 'bg-opacity-20 text-', $scoreColor) }} {{ str_replace('bg-', 'bg-opacity-100 text-white ', $scoreColor) }}">
            Score {{ $scoreLabel }}
        </span>
    </div>

    {{-- Title Check --}}
    <div class="border-t border-gray-200 pt-2">
        <div class="flex items-center justify-between text-xs">
            <span class="text-gray-500">Meta Title</span>
            <span class="{{ $titleColor }} font-medium">{{ $titleLabel }}</span>
        </div>
        @if($focusKeyword && $metaTitle)
            <div class="flex items-center justify-between text-xs mt-1">
                <span class="text-gray-400">Keyword in title</span>
                @if($keywordInTitle)
                    <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                @else
                    <svg class="w-4 h-4 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                @endif
            </div>
        @endif
    </div>

    {{-- Description Check --}}
    <div class="border-t border-gray-200 pt-2">
        <div class="flex items-center justify-between text-xs">
            <span class="text-gray-500">Meta Description</span>
            <span class="{{ $descColor }} font-medium">{{ $descLabel }}</span>
        </div>
        @if($focusKeyword && $metaDescription)
            <div class="flex items-center justify-between text-xs mt-1">
                <span class="text-gray-400">Keyword in desc</span>
                @if($keywordInDesc)
                    <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                @else
                    <svg class="w-4 h-4 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                @endif
            </div>
        @endif
    </div>

    {{-- Keyword presence --}}
    @if($focusKeyword)
        <div class="border-t border-gray-200 pt-2">
            <div class="flex items-center text-xs">
                <span class="text-gray-500">Focus Keyword:</span>
                <span class="ml-1 font-medium text-indigo-600">{{ $focusKeyword }}</span>
            </div>
        </div>
    @endif
</div>