@extends('layouts.app-home')

@section('content')
<div class="min-h-screen flex flex-col lg:flex-row">
    <div class="hidden lg:flex lg:w-1/2 relative">
        <img src="https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?w=1200&q=80"
             alt="Luxury interior"
             class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-brand-dark via-brand-dark/30 to-transparent"></div>
    </div>

    <div class="w-full lg:w-1/2 bg-brand-dark flex flex-col items-center lg:justify-center px-6 py-12 sm:px-8 sm:py-16"
         x-data="{ tab: '{{ $defaultTab ?? 'login' }}', showForm: true }">
        <div class="w-full max-w-md" style="margin-top: 200px;">
            <div class="flex border-b border-white/10 mb-8 sm:mb-10">
                <button @click="showForm = false; setTimeout(() => { tab = 'login'; showForm = true }, 300)"
                        :class="tab === 'login' ? 'text-brand-gold border-brand-gold' : 'text-gray-500 border-transparent hover:text-brand-light'"
                        class="flex-1 pb-3 sm:pb-4 text-xs sm:text-sm font-semibold tracking-widest uppercase border-b-2 transition-all duration-300">
                    Login
                </button>
                <button @click="showForm = false; setTimeout(() => { tab = 'register'; showForm = true }, 300)"
                        :class="tab === 'register' ? 'text-brand-gold border-brand-gold' : 'text-gray-500 border-transparent hover:text-brand-light'"
                        class="flex-1 pb-3 sm:pb-4 text-xs sm:text-sm font-semibold tracking-widest uppercase border-b-2 transition-all duration-300">
                    Register
                </button>
            </div>

            <div x-show="showForm"
                 x-transition:enter="transition ease-out duration-500"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-4">

                <template x-if="tab === 'login'">
                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        <div>
                            <label class="block text-xs tracking-wider uppercase text-gray-400 mb-2">Email</label>
                            <input type="email" name="email" required
                                   class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light placeholder-gray-500 text-sm rounded-sm focus:outline-none focus:border-brand-gold transition-colors">
                        </div>

                        <div>
                            <label class="block text-xs tracking-wider uppercase text-gray-400 mb-2">Password</label>
                            <input type="password" name="password" required
                                   class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light placeholder-gray-500 text-sm rounded-sm focus:outline-none focus:border-brand-gold transition-colors">
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="remember"
                                       class="w-4 h-4 rounded-sm border-white/20 bg-brand-dark/50 text-brand-gold focus:ring-brand-gold focus:ring-offset-0">
                                <span class="text-sm text-gray-400">Ricordami</span>
                            </label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-sm text-brand-gold hover:text-brand-light transition">
                                    Dimenticato la password?
                                </a>
                            @endif
                        </div>

                        <button type="submit"
                                class="w-full px-8 py-4 bg-brand-gold text-brand-dark text-sm font-semibold tracking-widest uppercase hover:bg-opacity-90 transition-all duration-300 rounded-sm">
                            Accedi
                        </button>
                    </form>
                </template>

                <template x-if="tab === 'register'">
                    <form method="POST" action="{{ route('register') }}" class="space-y-5">
                        @csrf

                        <div>
                            <label class="block text-xs tracking-wider uppercase text-gray-400 mb-2">Nome Completo</label>
                            <input type="text" name="name" required
                                   class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light placeholder-gray-500 text-sm rounded-sm focus:outline-none focus:border-brand-gold transition-colors">
                        </div>

                        <div>
                            <label class="block text-xs tracking-wider uppercase text-gray-400 mb-2">Email</label>
                            <input type="email" name="email" required
                                   class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light placeholder-gray-500 text-sm rounded-sm focus:outline-none focus:border-brand-gold transition-colors">
                        </div>

                        <div>
                            <label class="block text-xs tracking-wider uppercase text-gray-400 mb-2">Telefono</label>
                            <input type="tel" name="phone" required
                                   class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light placeholder-gray-500 text-sm rounded-sm focus:outline-none focus:border-brand-gold transition-colors">
                        </div>

                        <div>
                            <label class="block text-xs tracking-wider uppercase text-gray-400 mb-2">Indirizzo</label>
                            <input type="text" name="address_line1" required
                                   class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light placeholder-gray-500 text-sm rounded-sm focus:outline-none focus:border-brand-gold transition-colors">
                        </div>

                        <div>
                            <label class="block text-xs tracking-wider uppercase text-gray-400 mb-2">Indirizzo 2 (opzionale)</label>
                            <input type="text" name="address_line2"
                                   class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light placeholder-gray-500 text-sm rounded-sm focus:outline-none focus:border-brand-gold transition-colors">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs tracking-wider uppercase text-gray-400 mb-2">Citta</label>
                                <input type="text" name="city" required
                                       class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light placeholder-gray-500 text-sm rounded-sm focus:outline-none focus:border-brand-gold transition-colors">
                            </div>
                            <div>
                                <label class="block text-xs tracking-wider uppercase text-gray-400 mb-2">Provincia</label>
                                <input type="text" name="province" required
                                       class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light placeholder-gray-500 text-sm rounded-sm focus:outline-none focus:border-brand-gold transition-colors">
                            </div>
                            <div>
                                <label class="block text-xs tracking-wider uppercase text-gray-400 mb-2">CAP</label>
                                <input type="text" name="postal_code" required
                                       class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light placeholder-gray-500 text-sm rounded-sm focus:outline-none focus:border-brand-gold transition-colors">
                            </div>
                        </div>

                        <input type="hidden" name="country" value="IT">

                        <div>
                            <label class="block text-xs tracking-wider uppercase text-gray-400 mb-2">Password</label>
                            <input type="password" name="password" required
                                   class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light placeholder-gray-500 text-sm rounded-sm focus:outline-none focus:border-brand-gold transition-colors">
                        </div>

                        <div>
                            <label class="block text-xs tracking-wider uppercase text-gray-400 mb-2">Conferma Password</label>
                            <input type="password" name="password_confirmation" required
                                   class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light placeholder-gray-500 text-sm rounded-sm focus:outline-none focus:border-brand-gold transition-colors">
                        </div>

                        <button type="submit"
                                class="w-full px-8 py-4 bg-brand-gold text-brand-dark text-sm font-semibold tracking-widest uppercase hover:bg-opacity-90 transition-all duration-300 rounded-sm">
                            Registrati
                        </button>
                    </form>
                </template>

                <p class="mt-8 text-center text-sm text-gray-400">
                    <span x-text="tab === 'login' ? 'Non hai un account?' : 'Hai gia un account?'"></span>
                    <button @click="showForm = false; setTimeout(() => { tab = tab === 'login' ? 'register' : 'login'; showForm = true }, 300)" class="text-brand-gold hover:text-brand-light transition font-semibold ml-1">
                        <span x-text="tab === 'login' ? 'Registrati' : 'Accedi'"></span>
                    </button>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
