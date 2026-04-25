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
                <a href="{{ route('profile.show') }}"
                   class="block px-4 py-2 text-sm rounded-sm {{ request()->routeIs('profile.show') ? 'bg-brand-gold/10 text-brand-gold' : 'text-gray-400 hover:text-brand-light' }} transition">
                    Profilo
                </a>
            </nav>
        </div>

        <!-- Content -->
        <div class="md:col-span-3">
            @livewire('account-points')
            <div class="mt-10">
                @livewire('order-history')
            </div>
        </div>
    </div>
</div>
@endsection
