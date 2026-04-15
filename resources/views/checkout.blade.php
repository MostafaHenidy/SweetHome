@extends('layouts.app-home')

@section('content')
<div class="pt-20 min-h-screen bg-brand-dark">
    {{-- Hero Banner --}}
    <div class="relative h-64 overflow-hidden">
        <img src="https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=1920&q=80"
             alt="Luxury interior"
             class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-brand-dark/70"></div>
        <div class="absolute inset-0 flex items-center justify-center">
            <div class="text-center">
                <p class="text-brand-gold text-sm tracking-[0.3em] uppercase mb-4">SweetHome Milano</p>
                <h1 class="text-4xl md:text-5xl font-serif font-bold text-brand-light">
                    IL LUSSO DI ABITARE.
                </h1>
                <p class="text-gray-300 mt-4 max-w-lg mx-auto">
                    Trasformiamo i tuoi spazi in ambienti unici e raffinati. Design d'interni di lusso, arredamento su misura e consulenza personalizzata.
                </p>
            </div>
        </div>
    </div>

    {{-- Checkout Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Checkout Form --}}
            <div class="lg:col-span-2">
                {{-- Step Indicator --}}
                <div class="flex items-center mb-12">
                    <div class="flex items-center flex-1">
                        <div class="w-10 h-10 rounded-full bg-brand-gold text-brand-dark flex items-center justify-center text-sm font-bold">1</div>
                        <span class="ml-3 text-sm text-brand-gold font-semibold tracking-wider uppercase">Spedizione</span>
                    </div>
                    <div class="flex-1 h-px bg-white/10 mx-4"></div>
                    <div class="flex items-center flex-1">
                        <div class="w-10 h-10 rounded-full border border-white/20 text-gray-500 flex items-center justify-center text-sm font-bold">2</div>
                        <span class="ml-3 text-sm text-gray-500 tracking-wider uppercase">Pagamento</span>
                    </div>
                    <div class="flex-1 h-px bg-white/10 mx-4"></div>
                    <div class="flex items-center flex-1">
                        <div class="w-10 h-10 rounded-full border border-white/20 text-gray-500 flex items-center justify-center text-sm font-bold">3</div>
                        <span class="ml-3 text-sm text-gray-500 tracking-wider uppercase">Conferma</span>
                    </div>
                </div>

                {{-- Shipping Form --}}
                <div class="bg-brand-dark/40 backdrop-blur-sm border border-white/5 rounded-sm p-8">
                    <h2 class="text-xl font-serif text-brand-light mb-8">Dettagli Spedizione</h2>

                    <form class="space-y-5">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs tracking-wider uppercase text-gray-400 mb-2">Nome</label>
                                <input type="text" name="first_name" required
                                       class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light placeholder-gray-500 text-sm rounded-sm focus:outline-none focus:border-brand-gold transition-colors">
                            </div>
                            <div>
                                <label class="block text-xs tracking-wider uppercase text-gray-400 mb-2">Cognome</label>
                                <input type="text" name="last_name" required
                                       class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light placeholder-gray-500 text-sm rounded-sm focus:outline-none focus:border-brand-gold transition-colors">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
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
                        </div>

                        <div>
                            <label class="block text-xs tracking-wider uppercase text-gray-400 mb-2">Indirizzo</label>
                            <input type="text" name="address" required
                                   class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light placeholder-gray-500 text-sm rounded-sm focus:outline-none focus:border-brand-gold transition-colors">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                            <div>
                                <label class="block text-xs tracking-wider uppercase text-gray-400 mb-2">Città</label>
                                <input type="text" name="city" required
                                       class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light placeholder-gray-500 text-sm rounded-sm focus:outline-none focus:border-brand-gold transition-colors">
                            </div>
                            <div>
                                <label class="block text-xs tracking-wider uppercase text-gray-400 mb-2">CAP</label>
                                <input type="text" name="postal_code" required
                                       class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light placeholder-gray-500 text-sm rounded-sm focus:outline-none focus:border-brand-gold transition-colors">
                            </div>
                            <div>
                                <label class="block text-xs tracking-wider uppercase text-gray-400 mb-2">Paese</label>
                                <select name="country" required
                                        class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light text-sm rounded-sm focus:outline-none focus:border-brand-gold transition-colors appearance-none">
                                    <option value="IT">Italia</option>
                                    <option value="FR">Francia</option>
                                    <option value="DE">Germania</option>
                                    <option value="ES">Spagna</option>
                                    <option value="UK">Regno Unito</option>
                                </select>
                            </div>
                        </div>

                        <div class="pt-4 flex gap-4">
                            <a href="/cart"
                               class="px-8 py-4 border border-white/20 text-brand-light text-sm font-semibold tracking-widest uppercase hover:border-brand-gold hover:text-brand-gold transition-all duration-300 rounded-sm">
                                Indietro
                            </a>
                            <button type="submit"
                                    class="flex-1 px-8 py-4 bg-brand-gold text-brand-dark text-sm font-semibold tracking-widest uppercase hover:bg-opacity-90 transition-all duration-300 rounded-sm">
                                Continua al Pagamento
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Order Summary Sidebar --}}
            <div class="lg:col-span-1">
                <div class="bg-brand-slate p-8 rounded-sm sticky top-28">
                    <h3 class="text-lg font-serif text-brand-light mb-6 pb-4 border-b border-white/10">
                        Riepilogo Ordine
                    </h3>

                    {{-- Order Items --}}
                    <div class="space-y-4 mb-6">
                        @foreach($items as $item)
                        <div class="flex gap-4">
                            <div class="w-16 h-16 flex-shrink-0 overflow-hidden rounded-sm">
                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}"
                                     class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-brand-light truncate">{{ $item['name'] }}</p>
                                <p class="text-xs text-gray-400">Qtà: {{ $item['quantity'] }}</p>
                            </div>
                            <span class="text-sm text-brand-light font-semibold flex-shrink-0">
                                €{{ number_format($item['price'] * $item['quantity'], 2) }}
                            </span>
                        </div>
                        @endforeach
                    </div>

                    <div class="space-y-3 mb-6 pt-4 border-t border-white/10">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400">Subtotale</span>
                            <span class="text-brand-light">€{{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400">Spedizione</span>
                            <span class="text-brand-light">{{ $shipping > 0 ? '€' . number_format($shipping, 2) : 'Gratuita' }}</span>
                        </div>
                        <div class="border-t border-white/10 pt-3 flex justify-between">
                            <span class="text-brand-light font-semibold">Totale</span>
                            <span class="text-brand-gold text-xl font-bold">€{{ number_format($total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- I Nostri Servizi --}}
    <section class="bg-brand-slate border-t border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="text-center mb-16">
                <p class="text-brand-gold text-sm tracking-[0.3em] uppercase mb-4">Cosa Offriamo</p>
                <h2 class="text-3xl md:text-4xl font-serif font-bold text-brand-light">
                    I nostri servizi
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-brand-dark/40 backdrop-blur-sm p-8 rounded-sm border border-white/5 hover:border-brand-gold/30 transition-all duration-300 group">
                    <div class="w-12 h-12 mb-6 text-brand-gold group-hover:scale-110 transition-transform duration-300">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-serif text-brand-light mb-3">Progettazione</h3>
                    <p class="text-sm text-gray-400 leading-relaxed mb-6">
                        Creiamo progetti su misura che riflettono il tuo stile di vita, ottimizzando ogni spazio con eleganza e funzionalità.
                    </p>
                    <a href="/services" class="text-brand-gold text-sm font-semibold tracking-wider uppercase hover:text-brand-light transition">
                        Scopri di più &rarr;
                    </a>
                </div>

                <div class="bg-brand-dark/40 backdrop-blur-sm p-8 rounded-sm border border-white/5 hover:border-brand-gold/30 transition-all duration-300 group">
                    <div class="w-12 h-12 mb-6 text-brand-gold group-hover:scale-110 transition-transform duration-300">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205l3 1m1.5.5l-1.5-.5M6.75 7.364V3h-3v18m3-13.636l10.5-3.819"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-serif text-brand-light mb-3">Arredamento Completo</h3>
                    <p class="text-sm text-gray-400 leading-relaxed mb-6">
                        Dalla selezione dei materiali all'installazione finale, ci occupiamo di ogni aspetto per un risultato impeccabile.
                    </p>
                    <a href="/services" class="text-brand-gold text-sm font-semibold tracking-wider uppercase hover:text-brand-light transition">
                        Scopri di più &rarr;
                    </a>
                </div>

                <div class="bg-brand-dark/40 backdrop-blur-sm p-8 rounded-sm border border-white/5 hover:border-brand-gold/30 transition-all duration-300 group">
                    <div class="w-12 h-12 mb-6 text-brand-gold group-hover:scale-110 transition-transform duration-300">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-serif text-brand-light mb-3">Consulenza Personalizzata</h3>
                    <p class="text-sm text-gray-400 leading-relaxed mb-6">
                        I nostri esperti ti guidano nella scelta dei materiali, colori e finiture per creare l'ambiente perfetto.
                    </p>
                    <a href="/services" class="text-brand-gold text-sm font-semibold tracking-wider uppercase hover:text-brand-light transition">
                        Scopri di più &rarr;
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Newsletter / Quote Form --}}
    <section class="bg-brand-slate border-t border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="max-w-2xl mx-auto text-center">
                <p class="text-brand-gold text-sm tracking-[0.3em] uppercase mb-4">Contattaci</p>
                <h2 class="text-3xl md:text-4xl font-serif font-bold text-brand-light mb-4">
                    Richiedi un preventivo online
                </h2>
                <p class="text-gray-400 mb-10">
                    Compila il modulo e il nostro team ti ricontatterà entro 24 ore per una consulenza gratuita.
                </p>

                <form class="space-y-4" action="#" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <input type="text" name="name" placeholder="Nome e Cognome"
                               class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light placeholder-gray-500 text-sm rounded-sm focus:outline-none focus:border-brand-gold transition-colors">
                        <input type="email" name="email" placeholder="Email"
                               class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light placeholder-gray-500 text-sm rounded-sm focus:outline-none focus:border-brand-gold transition-colors">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <input type="tel" name="phone" placeholder="Telefono"
                               class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light placeholder-gray-500 text-sm rounded-sm focus:outline-none focus:border-brand-gold transition-colors">
                        <select name="service"
                                class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-gray-500 text-sm rounded-sm focus:outline-none focus:border-brand-gold transition-colors appearance-none">
                            <option value="">Seleziona servizio</option>
                            <option value="progettazione">Progettazione</option>
                            <option value="arredamento">Arredamento Completo</option>
                            <option value="consulenza">Consulenza</option>
                            <option value="altro">Altro</option>
                        </select>
                    </div>
                    <textarea name="message" rows="4" placeholder="Descrivi il tuo progetto..."
                              class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light placeholder-gray-500 text-sm rounded-sm focus:outline-none focus:border-brand-gold transition-colors resize-none"></textarea>
                    <button type="submit"
                            class="w-full sm:w-auto px-10 py-4 bg-brand-gold text-brand-dark text-sm font-semibold tracking-widest uppercase hover:bg-opacity-90 transition-all duration-300 rounded-sm">
                        Invia Richiesta
                    </button>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection
