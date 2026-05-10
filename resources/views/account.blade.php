@extends('layouts.app-shop')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl md:text-4xl font-serif font-bold text-brand-light mb-8">Il Mio Account</h1>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <!-- Sidebar -->
        <div class="md:col-span-1">
            <nav class="space-y-2">
                <a href="{{ route('account.orders') }}"
                   class="block px-4 py-2 text-sm rounded-sm {{ request()->routeIs('account.orders') ? 'bg-brand-gold/10 text-brand-gold' : 'text-gray-400 hover:text-brand-light' }} transition">
                    I Miei Ordini
                </a>
                <a href="{{ route('account.quotes') }}"
                   class="block px-4 py-2 text-sm rounded-sm {{ request()->routeIs('account.quotes') ? 'bg-brand-gold/10 text-brand-gold' : 'text-gray-400 hover:text-brand-light' }} transition">
                    Preventivi Richiesti
                </a>
                <a href="{{ route('account.favorites') }}"
                   class="block px-4 py-2 text-sm rounded-sm {{ request()->routeIs('account.favorites') ? 'bg-brand-gold/10 text-brand-gold' : 'text-gray-400 hover:text-brand-light' }} transition">
                    Preferiti
                </a>
                <a href="{{ route('account.inquiries') }}"
                   class="block px-4 py-2 text-sm rounded-sm {{ request()->routeIs('account.inquiries') ? 'bg-brand-gold/10 text-brand-gold' : 'text-gray-400 hover:text-brand-light' }} transition">
                    Richieste Assistenza
                </a>
                {{-- <a href="{{ route('account.profile') }}"
                   class="block px-4 py-2 text-sm rounded-sm {{ request()->routeIs('account.profile') ? 'bg-brand-gold/10 text-brand-gold' : 'text-gray-400 hover:text-brand-light' }} transition">
                    Profilo
                </a> --}}
                <a href="{{ route('profile.show') }}"
                   class="block px-4 py-2 text-sm rounded-sm {{ request()->routeIs('profile.show') ? 'bg-brand-gold/10 text-brand-gold' : 'text-gray-400 hover:text-brand-light' }} transition">
                    Impostazioni
                </a>
                <a href="#notification-preferences"
                   class="block px-4 py-2 text-sm rounded-sm text-gray-400 hover:text-brand-light transition">
                    Notifiche
                </a>
            </nav>
        </div>

        <!-- Content -->
        <div class="md:col-span-3">
            @livewire('account-points')

            @if(request()->routeIs('account.orders'))
                <div class="mt-10">
                    <h2 class="text-2xl font-serif text-brand-light mb-6">I Miei Ordini</h2>
                    @livewire('order-history')
                </div>
            @elseif(request()->routeIs('account.quotes'))
                <div class="mt-10">
                    <h2 class="text-2xl font-serif text-brand-light mb-6">Richieste Preventivi</h2>
                    @livewire('quote-history')
                </div>
            @elseif(request()->routeIs('account.favorites'))
                <div class="mt-10">
                    <h2 class="text-2xl font-serif text-brand-light mb-6">I Miei Preferiti</h2>
                    @livewire('user-favorites')
                </div>
            @elseif(request()->routeIs('account.inquiries'))
                <div class="mt-10">
                    <h2 class="text-2xl font-serif text-brand-light mb-6">Richieste Assistenza</h2>
                    @livewire('user-inquiries')
                </div>
            @elseif(request()->routeIs('account.profile'))
                <div class="mt-10">
                    <h2 class="text-2xl font-serif text-brand-light mb-6">Il Mio Profilo</h2>
                    @livewire('user-profile')
                </div>
            @else
                <!-- Default dashboard view -->
                <div class="mt-10">
                    @livewire('user-dashboard')
                </div>
            @endif

            <div id="notification-preferences" class="mt-10">
                <h2 class="text-2xl font-serif text-brand-light mb-6">Preferenze Notifiche</h2>
                @livewire('notification-preferences')
            </div>
        </div>
    </div>
</div>
@endsection
