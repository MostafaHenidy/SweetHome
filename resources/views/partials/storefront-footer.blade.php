@php
    $footerLinks = \App\Support\Content::get('footer_links', []);
    $socialLinks = \App\Support\Content::get('social_links', []);
    $siteName = \App\Models\Setting::get('site_name', 'SweetHome Milano');
    $siteEmail = \App\Models\Setting::get('site_email', 'info@sweethome.it');
    $sitePhone = \App\Models\Setting::get('site_phone', '328 899 5628');
    $siteAddress = \App\Models\Setting::get('site_address', 'Via Carlo Espinasse 104/a, 20156 Milano MI');
    $platforms = \App\Support\Content::socialPlatforms();
@endphp

<footer class="bg-black border-t border-white/5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
            <div class="md:col-span-1">
                @include('partials.storefront-brand', ['logoClass' => 'h-10 w-auto'])
                <p class="mt-4 text-sm text-gray-400 leading-relaxed">{{ \App\Support\Content::get('footer_brand_text') }}</p>
            </div>

            <div>
                <h4 class="text-sm font-semibold tracking-wider uppercase text-brand-gold mb-4">Navigazione</h4>
                <ul class="space-y-2">
                    @foreach ($footerLinks as $link)
                        <li>
                            <a href="{{ $link['url'] ?? '#' }}" class="text-sm text-gray-400 hover:text-brand-light transition">
                                {{ $link['label'] ?? 'Link' }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h4 class="text-sm font-semibold tracking-wider uppercase text-brand-gold mb-4">Contatti</h4>
                <ul class="space-y-2 text-sm text-gray-400">
                    <li>{{ $siteAddress }}</li>
                    <li class="pt-2"><a href="tel:{{ preg_replace('/\s+/', '', $sitePhone) }}" class="hover:text-brand-light transition">{{ $sitePhone }}</a></li>
                    <li><a href="mailto:{{ $siteEmail }}" class="hover:text-brand-light transition">{{ $siteEmail }}</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-sm font-semibold tracking-wider uppercase text-brand-gold mb-4">Seguici</h4>
                <div class="flex flex-wrap gap-4">
                    @foreach ($socialLinks as $link)
                        @php
                            $platform = $link['platform'] ?? strtolower($link['label'] ?? 'social');
                            $label = $platforms[$platform] ?? ($link['label'] ?? 'Social');
                        @endphp
                        <a href="{{ $link['url'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-brand-gold transition" aria-label="{{ $label }}" title="{{ $label }}">
                            @switch($platform)
                                @case('facebook')
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06c0 5.02 3.66 9.18 8.44 9.94v-7.03H7.9v-2.91h2.54V9.84c0-2.52 1.5-3.91 3.78-3.91 1.1 0 2.24.2 2.24.2V8.6H15.2c-1.24 0-1.63.78-1.63 1.57v1.89h2.78l-.44 2.91h-2.34V22C18.34 21.24 22 17.08 22 12.06z"/></svg>
                                    @break
                                @case('instagram')
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M7.8 2h8.4A5.81 5.81 0 0 1 22 7.8v8.4a5.81 5.81 0 0 1-5.8 5.8H7.8A5.81 5.81 0 0 1 2 16.2V7.8A5.81 5.81 0 0 1 7.8 2zm-.2 2A3.6 3.6 0 0 0 4 7.6v8.8A3.6 3.6 0 0 0 7.6 20h8.8a3.6 3.6 0 0 0 3.6-3.6V7.6A3.6 3.6 0 0 0 16.4 4H7.6zm9.65 1.5a1.25 1.25 0 1 1 0 2.5 1.25 1.25 0 0 1 0-2.5zM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10zm0 2a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/></svg>
                                    @break
                                @case('x')
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M18.9 2.5h3.1l-6.78 7.75 7.98 11.25h-6.25l-4.9-6.4-5.6 6.4H3.35l7.25-8.28L2.95 2.5H9.35l4.42 5.84 5.13-5.84zm-1.1 17.02h1.72L8.4 4.38H6.55l11.25 15.14z"/></svg>
                                    @break
                                @case('linkedin')
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M4.98 3.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5zM3 9.4h4v12.1H3V9.4zm6.3 0h3.8v1.65h.05c.53-1 1.83-2.05 3.77-2.05 4.03 0 4.78 2.65 4.78 6.1v6.4h-4v-5.67c0-1.35-.02-3.08-1.88-3.08-1.88 0-2.17 1.47-2.17 2.98v5.87h-4V9.4z"/></svg>
                                    @break
                                @case('youtube')
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M21.58 7.18a2.74 2.74 0 0 0-1.93-1.94C17.95 4.78 12 4.78 12 4.78s-5.95 0-7.65.46a2.74 2.74 0 0 0-1.93 1.94A28.48 28.48 0 0 0 2 12a28.48 28.48 0 0 0 .42 4.82 2.74 2.74 0 0 0 1.93 1.94c1.7.46 7.65.46 7.65.46s5.95 0 7.65-.46a2.74 2.74 0 0 0 1.93-1.94A28.48 28.48 0 0 0 22 12a28.48 28.48 0 0 0-.42-4.82zM10 15.27V8.73L15.45 12 10 15.27z"/></svg>
                                    @break
                                @case('tiktok')
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M17.5 6.2a5.82 5.82 0 0 0 3.4 1.1v3.35a9.11 9.11 0 0 1-3.4-.7v5.45A6.1 6.1 0 1 1 11.4 9.3c.35 0 .69.03 1.02.09v3.48a2.73 2.73 0 1 0 1.76 2.55V2h3.32v4.2z"/></svg>
                                    @break
                                @case('pinterest')
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12.08 2C6.58 2 3 5.64 3 10.53c0 2.27 1.2 5.1 3.13 6 .29.13.45.07.52-.2l.43-1.74a.46.46 0 0 0-.11-.43 5.58 5.58 0 0 1-1.15-3.38c0-3.41 2.58-6.71 6.97-6.71 3.8 0 6.46 2.6 6.46 6.31 0 4.2-2.12 7.1-4.87 7.1-1.52 0-2.66-1.26-2.29-2.8.44-1.85 1.29-3.85 1.29-5.18 0-1.2-.64-2.2-1.98-2.2-1.57 0-2.83 1.62-2.83 3.8 0 1.38.47 2.32.47 2.32l-1.9 8.04c-.33 1.4-.2 3.35-.06 4.54h.1c.65-.93 1.48-2.37 1.83-3.72l1.02-3.88a4.13 4.13 0 0 0 3.52 1.8c4.63 0 7.97-4.26 7.97-9.56C21.5 5.57 17.36 2 12.08 2z"/></svg>
                                    @break
                                @case('whatsapp')
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.52 3.48A11.82 11.82 0 0 0 12.1 0 11.9 11.9 0 0 0 1.78 17.86L0 24l6.32-1.66A11.9 11.9 0 0 0 24 11.91c0-3.18-1.24-6.17-3.48-8.43zM12.1 21.9a9.86 9.86 0 0 1-5.02-1.37l-.36-.21-3.75.98 1-3.65-.24-.38A9.81 9.81 0 1 1 12.1 21.9zm5.39-7.37c-.3-.15-1.75-.86-2.02-.96-.27-.1-.47-.15-.67.15-.2.3-.77.96-.94 1.16-.17.2-.35.22-.64.07-.3-.15-1.25-.46-2.38-1.47a8.9 8.9 0 0 1-1.65-2.05c-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.49s1.07 2.89 1.22 3.09c.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.7.63.71.23 1.36.2 1.87.12.57-.09 1.75-.72 2-1.41.25-.7.25-1.29.17-1.42-.07-.12-.27-.2-.57-.35z"/></svg>
                                    @break
                                @case('telegram')
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M21.94 4.16 18.7 19.42c-.24 1.08-.88 1.35-1.78.84l-4.92-3.63-2.37 2.28c-.26.26-.48.48-.98.48l.35-5 9.1-8.23c.4-.35-.09-.55-.61-.2L6.24 13.05 1.4 11.54c-1.05-.33-1.07-1.05.22-1.55L20.55 2.7c.88-.33 1.65.2 1.39 1.46z"/></svg>
                                    @break
                                @case('snapchat')
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2c2.7 0 4.9 2.05 4.9 4.8 0 1.08-.1 2.05-.08 3.02.02.28.19.43.4.43.34 0 .75-.3 1.1-.3.42 0 .75.32.75.7 0 .63-.76 1-1.47 1.27-.48.18-.62.36-.44.78.63 1.47 1.88 2.08 3.03 2.6.37.17.69.31.65.72-.06.58-1.02.9-2.78 1.17-.16.11-.22.6-.45.95-.23.35-.58.44-.95.44-.44 0-.94-.15-1.5-.15-.86 0-1.37.32-1.96.69-.45.28-.92.58-1.2.58s-.75-.3-1.2-.58c-.59-.37-1.1-.69-1.96-.69-.56 0-1.06.15-1.5.15-.37 0-.72-.09-.95-.44-.23-.35-.29-.84-.45-.95-1.76-.27-2.72-.59-2.78-1.17-.04-.41.28-.55.65-.72 1.15-.52 2.4-1.13 3.03-2.6.18-.42.04-.6-.44-.78-.71-.27-1.47-.64-1.47-1.27 0-.38.33-.7.75-.7.35 0 .76.3 1.1.3.21 0 .38-.15.4-.43.02-.97-.08-1.94-.08-3.02C7.1 4.05 9.3 2 12 2z"/></svg>
                                    @break
                                @case('threads')
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12.2 2C6.55 2 3 5.75 3 12.04 3 18.31 6.54 22 12.2 22c5.3 0 8.8-3.1 8.8-7.78 0-3.67-2.1-5.74-5.05-6.42-.72-2.02-2.2-3.1-4.24-3.1-2.18 0-3.75 1.17-4.42 3.3l2.24.6c.34-1.1 1.08-1.68 2.16-1.68 1.03 0 1.75.52 2.16 1.55-3.42.08-5.54 1.54-5.54 3.87 0 2.05 1.62 3.45 3.98 3.45 2.6 0 4.13-1.46 4.13-3.95 0-.56-.04-1.08-.12-1.56 1.43.57 2.22 1.82 2.22 3.84 0 3.4-2.43 5.55-6.3 5.55-4.24 0-6.75-2.83-6.75-7.63 0-4.85 2.52-7.76 6.75-7.76 2.82 0 4.93 1.28 5.96 3.63l2.2-.92C18.94 3.76 16.02 2 12.2 2zm1.88 8.62c.06.4.09.82.09 1.25 0 1.16-.7 1.83-1.94 1.83-1.02 0-1.68-.52-1.68-1.35 0-1.04 1.18-1.68 3.53-1.73z"/></svg>
                                    @break
                                @case('github')
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 0 0-3.16 19.49c.5.09.68-.22.68-.48v-1.7c-2.78.6-3.37-1.34-3.37-1.34-.45-1.16-1.11-1.47-1.11-1.47-.91-.62.07-.61.07-.61 1 .07 1.53 1.03 1.53 1.03.9 1.53 2.34 1.09 2.91.83.09-.65.35-1.09.63-1.34-2.22-.25-4.56-1.11-4.56-4.94 0-1.09.39-1.98 1.03-2.68-.1-.25-.45-1.27.1-2.64 0 0 .84-.27 2.75 1.02A9.55 9.55 0 0 1 12 5.84c.85 0 1.7.11 2.5.33 1.9-1.29 2.74-1.02 2.74-1.02.55 1.37.2 2.39.1 2.64.64.7 1.03 1.59 1.03 2.68 0 3.84-2.34 4.69-4.57 4.94.36.31.68.92.68 1.85V21c0 .27.18.58.69.48A10 10 0 0 0 12 2z"/></svg>
                                    @break
                                @case('behance')
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M9.4 11.4c1.2-.58 1.78-1.5 1.78-2.76 0-2.15-1.6-3.24-4.78-3.24H1.5v13.2h5.08c3.18 0 4.77-1.25 4.77-3.74 0-1.7-.65-2.85-1.95-3.46zM4.4 7.75h1.73c1.4 0 2.1.45 2.1 1.35 0 .96-.66 1.44-1.98 1.44H4.4V7.75zm2.02 8.5H4.4v-3.57h2.08c1.3 0 1.96.6 1.96 1.78 0 1.2-.67 1.79-2.02 1.79zM15.7 6.4h5.1V5h-5.1v1.4zm6.5 7.7c0-3.12-1.66-4.68-4.98-4.68-3.02 0-4.78 1.86-4.78 4.8 0 2.94 1.8 4.68 4.88 4.68 2.39 0 3.96-.95 4.7-2.85h-2.73c-.34.6-.94.9-1.8.9-1.35 0-2.08-.7-2.2-2.1h6.9c.01-.26.01-.51.01-.75zm-6.86-1.1c.2-1.1.82-1.65 1.86-1.65 1.18 0 1.85.55 2 1.65h-3.86z"/></svg>
                                    @break
                                @case('dribbble')
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm6.65 4.6a8.02 8.02 0 0 1 1.82 5.03 16.3 16.3 0 0 0-5.67-.27 30.95 30.95 0 0 0-.58-1.25 17.98 17.98 0 0 0 4.43-3.51zM12 3.85a8.1 8.1 0 0 1 5.4 2.05 15.4 15.4 0 0 1-3.95 3.1 41.7 41.7 0 0 0-2.82-4.95c.44-.13.9-.2 1.37-.2zM8.6 4.82a36.7 36.7 0 0 1 2.85 4.9 29.5 29.5 0 0 1-7.55 1 8.12 8.12 0 0 1 4.7-5.9zM3.83 12.57h.25c2.5 0 5.26-.37 8.13-1.15.2.4.37.8.54 1.2l-.27.08c-3.27 1.06-5.64 3.16-6.82 5.4a8.08 8.08 0 0 1-1.83-5.53zM12 20.15a8.06 8.06 0 0 1-5.02-1.74c.96-1.96 2.98-3.78 6.44-4.98a33.2 33.2 0 0 1 1.67 6.29c-.94.28-1.98.43-3.09.43zm4.86-1.32a35.2 35.2 0 0 0-1.52-5.85 14.9 14.9 0 0 1 4.97.35 8.13 8.13 0 0 1-3.45 5.5z"/></svg>
                                    @break
                                @default
                                    <span class="text-sm">{{ $label }}</span>
                            @endswitch
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="border-t border-white/5 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-xs text-gray-500">&copy; {{ date('Y') }} {{ $siteName }}. Tutti i diritti riservati.</p>
            <div class="flex flex-wrap justify-center gap-6">
                <a href="{{ route('policy.show') }}" class="text-xs text-gray-500 hover:text-gray-300 transition">Privacy Policy</a>
                <a href="{{ route('terms.show') }}" class="text-xs text-gray-500 hover:text-gray-300 transition">Termini e Condizioni</a>
            </div>
        </div>
    </div>
</footer>
