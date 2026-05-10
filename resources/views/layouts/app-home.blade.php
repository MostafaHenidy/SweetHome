<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'SweetHome Milano' }}</title>
    @php($siteFavicon = \App\Models\Setting::get('site_favicon'))
    @if($siteFavicon)
        <link rel="icon" href="{{ '/storage/' . ltrim($siteFavicon, '/') }}">
        <link rel="apple-touch-icon" href="{{ '/storage/' . ltrim($siteFavicon, '/') }}">
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|inter:300,400,500,600" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Styles -->
    @livewireStyles
</head>
<body class="bg-brand-dark text-brand-light antialiased font-sans">

    <!-- Navigation -->
    <nav x-data="{ open: false }" class="fixed top-0 left-0 right-0 z-50 bg-brand-dark/95 backdrop-blur-sm border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20">
                <!-- Logo -->
                <div class="flex items-center">
                    @include('partials.storefront-brand', ['logoClass' => 'h-10 w-auto'])
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden sm:flex sm:items-center sm:space-x-8">
                    <a href="/" class="text-sm tracking-wider uppercase hover:text-brand-gold transition-colors duration-300">Home</a>
                    <a href="/shop" class="text-sm tracking-wider uppercase hover:text-brand-gold transition-colors duration-300">Shop</a>
                    <a href="/services" class="text-sm tracking-wider uppercase hover:text-brand-gold transition-colors duration-300">Servizi</a>
                    <a href="/contact" class="text-sm tracking-wider uppercase hover:text-brand-gold transition-colors duration-300">Contatti</a>
                    <a href="/cart" class="relative group">
                        <svg class="w-6 h-6 hover:text-brand-gold transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        @if(isset($cartCount) && $cartCount > 0)
                            <span class="absolute -top-2 -right-2 bg-brand-gold text-brand-dark text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold">{{ $cartCount }}</span>
                        @endif
                    </a>
                    @auth
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open"
                                    class="text-sm tracking-wider uppercase hover:text-brand-gold transition-colors duration-300 flex items-center gap-1">
                                {{ Auth::user()->name }}
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="open"
                                 @click.away="open = false"
                                 x-cloak
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                                <a href="{{ route('account.orders') }}"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-brand-dark">
                                    {{ __('My Account') }}
                                </a>
                                <a href="{{ route('account.profile') }}"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-brand-dark">
                                    {{ __('Profile') }}
                                </a>
                                <a href="{{ route('account.favorites') }}"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-brand-dark">
                                    {{ __('Favorites') }}
                                </a>
                                <div class="border-t border-gray-200 my-1"></div>
                                <form method="POST" action="{{ route('logout') }}" x-data>
                                    @csrf
                                    <button type="submit"
                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-brand-dark">
                                        {{ __('Log Out') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('auth') }}" class="text-sm tracking-wider uppercase hover:text-brand-gold transition-colors duration-300">Accedi</a>
                    @endauth
                </div>

                <!-- Mobile menu button -->
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

        <!-- Mobile Menu -->
        <div x-show="open" x-cloak x-transition class="sm:hidden border-t border-white/10 bg-brand-dark">
            <div class="px-4 py-4 space-y-3">
                <a href="/" class="block text-sm tracking-wider uppercase hover:text-brand-gold transition">Home</a>
                <a href="/shop" class="block text-sm tracking-wider uppercase hover:text-brand-gold transition">Shop</a>
                <a href="/services" class="block text-sm tracking-wider uppercase hover:text-brand-gold transition">Servizi</a>
                <a href="/contact" class="block text-sm tracking-wider uppercase hover:text-brand-gold transition">Contatti</a>
                <a href="/cart" class="block text-sm tracking-wider uppercase hover:text-brand-gold transition">Carrello</a>
                @auth
                    <div class="border-t border-white/10 pt-3 mt-3">
                        <div class="text-sm text-gray-400 mb-2">{{ Auth::user()->name }}</div>
                        <a href="{{ route('account.orders') }}" class="block text-sm tracking-wider uppercase hover:text-brand-gold transition">My Account</a>
                        <a href="{{ route('account.profile') }}" class="block text-sm tracking-wider uppercase hover:text-brand-gold transition">Profile</a>
                        <a href="{{ route('account.favorites') }}" class="block text-sm tracking-wider uppercase hover:text-brand-gold transition">Favorites</a>
                        <form method="POST" action="{{ route('logout') }}" x-data>
                            @csrf
                            <button type="submit" class="block text-sm tracking-wider uppercase hover:text-brand-gold transition text-left w-full">
                                Log Out
                            </button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('auth') }}" class="block text-sm tracking-wider uppercase hover:text-brand-gold transition">Accedi</a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    @include('partials.storefront-footer')

    @livewireScripts
</body>
</html>
