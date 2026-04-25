@php
    $siteName = \App\Models\Setting::get('site_name', 'SweetHome Milano');
    $siteLogo = \App\Models\Setting::get('site_logo');
    $logoUrl = $siteLogo ? '/storage/' . ltrim($siteLogo, '/') : null;
    $class = $class ?? 'text-2xl font-serif tracking-widest uppercase text-brand-light';
    $logoClass = $logoClass ?? 'h-10 w-auto';
@endphp

<a href="/" class="inline-flex items-center gap-3 {{ $class }}">
    @if($logoUrl)
        <img src="{{ $logoUrl }}" alt="{{ $siteName }} logo" class="{{ $logoClass }}">
    @endif
    <span>{{ $siteName }}</span>
</a>
