<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SweetHome Milano' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|inter:300,400,500,600" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-brand-dark text-brand-light antialiased font-sans">

    <nav x-data="{ open: false }" class="fixed top-0 left-0 right-0 z-50 bg-brand-dark/95 backdrop-blur-sm border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20">
                <div class="flex items-center">
                    <a href="/" class="text-2xl font-serif tracking-widest uppercase text-brand-light">SweetHome</a>
                </div>
                <div class="hidden sm:flex sm:items-center sm:space-x-8">
                    <a href="/" class="text-sm tracking-wider uppercase hover:text-brand-gold transition-colors duration-300">Home</a>
                    <a href="/shop" class="text-sm tracking-wider uppercase hover:text-brand-gold transition-colors duration-300">Shop</a>
                    <a href="/services" class="text-sm tracking-wider uppercase hover:text-brand-gold transition-colors duration-300">Servizi</a>
                    <a href="/contact" class="text-sm tracking-wider uppercase hover:text-brand-gold transition-colors duration-300">Contatti</a>
                    <a href="/cart" class="relative group">
                        <svg class="w-6 h-6 hover:text-brand-gold transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </a>
                </div>
                <div class="sm:hidden flex items-center">
                    <button @click="open = !open" class="text-brand-light hover:text-brand-gold transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/>
                            <path x-show="open" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <div x-show="open" x-cloak x-transition class="sm:hidden border-t border-white/10 bg-brand-dark">
            <div class="px-4 py-4 space-y-3">
                <a href="/" class="block text-sm tracking-wider uppercase hover:text-brand-gold transition">Home</a>
                <a href="/shop" class="block text-sm tracking-wider uppercase hover:text-brand-gold transition">Shop</a>
                <a href="/services" class="block text-sm tracking-wider uppercase hover:text-brand-gold transition">Servizi</a>
                <a href="/contact" class="block text-sm tracking-wider uppercase hover:text-brand-gold transition">Contatti</a>
                <a href="/cart" class="block text-sm tracking-wider uppercase hover:text-brand-gold transition">Carrello</a>
            </div>
        </div>
    </nav>

    <main class="pt-20">
        @yield('content')
    </main>

    <footer class="bg-black border-t border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
                <div class="md:col-span-1">
                    <a href="/" class="text-2xl font-serif tracking-widest uppercase text-brand-light">SweetHome</a>
                    <p class="mt-4 text-sm text-gray-400 leading-relaxed">Design d'interni di lusso per chi cerca l'eccellenza nell'arredamento.</p>
                </div>
                <div>
                    <h4 class="text-sm font-semibold tracking-wider uppercase text-brand-gold mb-4">Navigazione</h4>
                    <ul class="space-y-2">
                        <li><a href="/" class="text-sm text-gray-400 hover:text-brand-light transition">Home</a></li>
                        <li><a href="/shop" class="text-sm text-gray-400 hover:text-brand-light transition">Shop</a></li>
                        <li><a href="/services" class="text-sm text-gray-400 hover:text-brand-light transition">Servizi</a></li>
                        <li><a href="/contact" class="text-sm text-gray-400 hover:text-brand-light transition">Contatti</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-semibold tracking-wider uppercase text-brand-gold mb-4">Contatti</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li>Via Carlo Espinasse 104/a</li>
                        <li>20156 Milano, Italia</li>
                        <li class="pt-2"><a href="tel:+393288995628" class="hover:text-brand-light transition">328 899 5628</a></li>
                        <li><a href="mailto:info@sweethome.it" class="hover:text-brand-light transition">info@sweethome.it</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-semibold tracking-wider uppercase text-brand-gold mb-4">Seguici</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-brand-gold transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-brand-gold transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                    </div>
                </div>
            </div>
            <div class="border-t border-white/5 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-xs text-gray-500">&copy; {{ date('Y') }} SweetHome Milano. Tutti i diritti riservati.</p>
                <div class="flex space-x-6 mt-4 md:mt-0">
                    <a href="#" class="text-xs text-gray-500 hover:text-gray-300 transition">Privacy Policy</a>
                    <a href="#" class="text-xs text-gray-500 hover:text-gray-300 transition">Cookie Policy</a>
                </div>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
