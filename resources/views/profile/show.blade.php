@extends('layouts.app-shop')

@section('content')
<section>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-3xl md:text-4xl font-serif font-bold text-brand-light mb-8">Il Mio Profilo</h1>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <aside class="md:col-span-1">
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
            </aside>

            <div class="md:col-span-3 space-y-6">
                @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                    @livewire('profile.update-profile-information-form')
                @endif

                @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                    @livewire('profile.update-password-form')
                @endif

                @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                    @livewire('profile.two-factor-authentication-form')
                @endif

                @livewire('profile.logout-other-browser-sessions-form')

                @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
                    @livewire('profile.delete-user-form')
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
