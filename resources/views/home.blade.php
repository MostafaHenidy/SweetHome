@extends('layouts.app-shop')

@section('content')

{{-- ============================================
     HERO SECTION
     ============================================ --}}
<section class="relative min-h-screen flex items-center justify-center overflow-hidden">
    <!-- Background Image -->
    <div class="absolute inset-0">
        <img src="{{asset('imgs/black-and-white-designing-sNwHCsDAT1c-unsplash.jpg')}}"
             alt="Luxury interior with hardwood floors"
             class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-r from-brand-dark/90 via-brand-dark/70 to-brand-dark/40"></div>
    </div>

    <!-- Hero Content -->
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-32">
        <div class="max-w-2xl">
            <p class="text-brand-gold text-sm tracking-[0.3em] uppercase mb-6">{{ \App\Support\Content::get('home_hero_eyebrow') }}</p>
            <h1 class="text-5xl md:text-6xl lg:text-7xl font-serif font-bold text-brand-light leading-tight mb-6">
                {!! nl2br(e(\App\Support\Content::get('home_hero_title'))) !!}
            </h1>
            <p class="text-lg text-gray-300 leading-relaxed mb-10 max-w-lg">
                {{ \App\Support\Content::get('home_hero_description') }}
            </p>
            <div class="flex flex-col sm:flex-row gap-4">
                <a href="{{ \App\Support\Content::get('home_hero_primary_url') }}" class="inline-flex items-center justify-center px-8 py-4 bg-brand-gold text-brand-dark text-sm font-semibold tracking-widest uppercase hover:bg-opacity-90 transition-all duration-300">
                    {{ \App\Support\Content::get('home_hero_primary_label') }}
                </a>
                <a href="{{ \App\Support\Content::get('home_hero_secondary_url') }}" class="inline-flex items-center justify-center px-8 py-4 border border-white/30 text-brand-light text-sm font-semibold tracking-widest uppercase hover:border-brand-gold hover:text-brand-gold transition-all duration-300">
                    {{ \App\Support\Content::get('home_hero_secondary_label') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Scroll Indicator -->
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-10">
        <div class="w-6 h-10 border-2 border-white/30 rounded-full flex justify-center">
            <div class="w-1 h-3 bg-brand-gold rounded-full mt-2 animate-bounce"></div>
        </div>
    </div>
</section>

{{-- ============================================
     FEATURES STRIP
     ============================================ --}}
<section class="bg-brand-dark border-b border-white/5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12 text-center">
            <!-- Feature 1 -->
            <div class="flex flex-col items-center">
                <div class="w-16 h-16 mb-6 text-brand-gold">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42"/>
                    </svg>
                </div>
                <h3 class="text-lg font-serif text-brand-light mb-2">Arredamento su Misura</h3>
                <p class="text-sm text-gray-400 leading-relaxed">Ogni pezzo creato su misura per i tuoi spazi, con attenzione ai dettagli e materiali pregiati.</p>
            </div>

            <!-- Feature 2 -->
            <div class="flex flex-col items-center">
                <div class="w-16 h-16 mb-6 text-brand-gold">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-serif text-brand-light mb-2">Materiali di Prima Qualità</h3>
                <p class="text-sm text-gray-400 leading-relaxed">Selezioniamo solo i migliori materiali italiani e internazionali per garantire eccellenza.</p>
            </div>

            <!-- Feature 3 -->
            <div class="flex flex-col items-center">
                <div class="w-16 h-16 mb-6 text-brand-gold">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-serif text-brand-light mb-2">Progettazione e Consulenza</h3>
                <p class="text-sm text-gray-400 leading-relaxed">Il nostro team di designer ti guida in ogni fase, dalla concept alla realizzazione finale.</p>
            </div>
        </div>
    </div>
</section>

{{-- ============================================
     "LA TUA CASA PRENDE FORMA" SECTION
     ============================================ --}}
<section class="bg-brand-dark">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <!-- Image -->
            <div class="relative">
                <div class="aspect-[4/3] overflow-hidden rounded-sm">
                    <img src="https://images.unsplash.com/photo-1581858726788-75bc0f6a952d?w=800&q=80"
                         alt="Hardwood floor texture"
                         class="w-full h-full object-cover hover:scale-105 transition-transform duration-700">
                </div>
                <div class="absolute -bottom-6 -right-6 w-48 h-48 border border-brand-gold/30 rounded-sm -z-10 hidden lg:block"></div>
            </div>

            <!-- Content -->
            <div>
                <p class="text-brand-gold text-sm tracking-[0.3em] uppercase mb-4">Design d'Interni</p>
                <h2 class="text-3xl md:text-4xl font-serif font-bold text-brand-light mb-6">
                    La tua casa prende forma
                </h2>
                <p class="text-gray-400 leading-relaxed mb-4">
                    Ogni ambiente racconta una storia. Il nostro approccio personalizzato trasforma i tuoi desideri in spazi vivibili, funzionali e straordinariamente belli.
                </p>
                <p class="text-gray-400 leading-relaxed mb-8">
                    Dal concept iniziale alla consegna finale, ti accompagniamo in un percorso di scoperta dove ogni dettaglio conta.
                </p>
                <a href="/projects" class="inline-flex items-center gap-2 text-brand-gold text-sm font-semibold tracking-widest uppercase hover:gap-4 transition-all duration-300">
                    Scopri i Progetti
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ============================================
     PRODOTTI PER OGNI AMBIENTE
     ============================================ --}}
<section class="bg-brand-dark border-t border-white/5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="text-center mb-16">
            <p class="text-brand-gold text-sm tracking-[0.3em] uppercase mb-4">Collezioni</p>
            <h2 class="text-3xl md:text-4xl font-serif font-bold text-brand-light">
                Prodotti per ogni ambiente
            </h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Category 1: Soggiorno -->
            <a href="/shop?category=soggiorno" class="group relative overflow-hidden rounded-sm">
                <div class="aspect-[4/3] overflow-hidden">
                    <img src="{{asset('imgs/mike-k-5jcKmzVsRnY-unsplash.jpg')}}"
                         alt="Modern living room with large windows"
                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-brand-dark/90 via-brand-dark/30 to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-6">
                    <h3 class="text-xl font-serif text-brand-light mb-1">Soggiorno</h3>
                    <p class="text-sm text-gray-300">Divani, tavoli e complementi</p>
                </div>
            </a>

            <!-- Category 2: Camera da Letto -->
            <a href="/shop?category=camera" class="group relative overflow-hidden rounded-sm">
                <div class="aspect-[4/3] overflow-hidden">
                    <img src="{{asset('imgs/steven-ungermann-CVTmLMv5oG4-unsplash.jpg')}}"
                         alt="Bright bedroom with large windows"
                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-brand-dark/90 via-brand-dark/30 to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-6">
                    <h3 class="text-xl font-serif text-brand-light mb-1">Camera da Letto</h3>
                    <p class="text-sm text-gray-300">Letti, armadi e comodini</p>
                </div>
            </a>

            <!-- Category 3: Cucina -->
            <a href="/shop?category=cucina" class="group relative overflow-hidden rounded-sm">
                <div class="aspect-[4/3] overflow-hidden">
                    <img src="{{asset('imgs/1.jpg')}}"
                         alt="Modern kitchen with tile floor"
                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-brand-dark/90 via-brand-dark/30 to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-6">
                    <h3 class="text-xl font-serif text-brand-light mb-1">Cucina</h3>
                    <p class="text-sm text-gray-300">Mobili e accessori cucina</p>
                </div>
            </a>

            <!-- Category 4: Bagno -->
            <a href="/shop?category=bagno" class="group relative overflow-hidden rounded-sm">
                <div class="aspect-[4/3] overflow-hidden">
                    <img src="{{asset('imgs/2.jpg')}}"
                         alt="Modern bathroom with tile floor"
                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-brand-dark/90 via-brand-dark/30 to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-6">
                    <h3 class="text-xl font-serif text-brand-light mb-1">Bagno</h3>
                    <p class="text-sm text-gray-300">Mobili e accessori bagno</p>
                </div>
            </a>

            <!-- Category 5: Ufficio -->
            <a href="/shop?category=ufficio" class="group relative overflow-hidden rounded-sm">
                <div class="aspect-[4/3] overflow-hidden">
                    <img src="{{asset('imgs/3.jpg')}}"
                         alt="Home office with wooden floor"
                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-brand-dark/90 via-brand-dark/30 to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-6">
                    <h3 class="text-xl font-serif text-brand-light mb-1">Ufficio</h3>
                    <p class="text-sm text-gray-300">Scrivanie e sedie ergonomiche</p>
                </div>
            </a>

            <!-- Category 6: Esterno -->
            <a href="/shop?category=esterno" class="group relative overflow-hidden rounded-sm">
                <div class="aspect-[4/3] overflow-hidden">
                    <img src="{{asset('imgs/4.jpg')}}"
                         alt="Outdoor terrace with elegant flooring"
                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-brand-dark/90 via-brand-dark/30 to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-6">
                    <h3 class="text-xl font-serif text-brand-light mb-1">Esterno</h3>
                    <p class="text-sm text-gray-300">Arredo giardino e terrazzo</p>
                </div>
            </a>
        </div>
    </div>
</section>

{{-- ============================================
     I NOSTRI SERVIZI
     ============================================ --}}
<section class="bg-brand-slate">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="text-center mb-16">
            <p class="text-brand-gold text-sm tracking-[0.3em] uppercase mb-4">{{ \App\Support\Content::get('home_services_eyebrow') }}</p>
            <h2 class="text-3xl md:text-4xl font-serif font-bold text-brand-light">
                {{ \App\Support\Content::get('home_services_title') }}
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Service 1 -->
            <div class="bg-brand-dark/40 backdrop-blur-sm p-8 rounded-sm border border-white/5 hover:border-brand-gold/30 transition-all duration-300 group">
                <div class="w-12 h-12 mb-6 text-brand-gold group-hover:scale-110 transition-transform duration-300">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42"/>
                    </svg>
                </div>
                <h3 class="text-xl font-serif text-brand-light mb-3">{{ \App\Support\Content::get('service_1_title') }}</h3>
                <p class="text-sm text-gray-400 leading-relaxed mb-6">
                    {{ \App\Support\Content::get('service_1_description') }}
                </p>
                <a href="/services" class="text-brand-gold text-sm font-semibold tracking-wider uppercase hover:text-brand-light transition">
                    Scopri di più &rarr;
                </a>
            </div>

            <!-- Service 2 -->
            <div class="bg-brand-dark/40 backdrop-blur-sm p-8 rounded-sm border border-white/5 hover:border-brand-gold/30 transition-all duration-300 group">
                <div class="w-12 h-12 mb-6 text-brand-gold group-hover:scale-110 transition-transform duration-300">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205l3 1m1.5.5l-1.5-.5M6.75 7.364V3h-3v18m3-13.636l10.5-3.819"/>
                    </svg>
                </div>
                <h3 class="text-xl font-serif text-brand-light mb-3">{{ \App\Support\Content::get('service_2_title') }}</h3>
                <p class="text-sm text-gray-400 leading-relaxed mb-6">
                    {{ \App\Support\Content::get('service_2_description') }}
                </p>
                <a href="/services" class="text-brand-gold text-sm font-semibold tracking-wider uppercase hover:text-brand-light transition">
                    Scopri di più &rarr;
                </a>
            </div>

            <!-- Service 3 -->
            <div class="bg-brand-dark/40 backdrop-blur-sm p-8 rounded-sm border border-white/5 hover:border-brand-gold/30 transition-all duration-300 group">
                <div class="w-12 h-12 mb-6 text-brand-gold group-hover:scale-110 transition-transform duration-300">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/>
                    </svg>
                </div>
                <h3 class="text-xl font-serif text-brand-light mb-3">{{ \App\Support\Content::get('service_3_title') }}</h3>
                <p class="text-sm text-gray-400 leading-relaxed mb-6">
                    {{ \App\Support\Content::get('service_3_description') }}
                </p>
                <a href="/services" class="text-brand-gold text-sm font-semibold tracking-wider uppercase hover:text-brand-light transition">
                    Scopri di più &rarr;
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ============================================
     SHOWROOM LOCATION
     ============================================ --}}
<section class="bg-brand-dark border-t border-white/5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-stretch">
            <div class="overflow-hidden rounded-sm border border-white/10 h-80 lg:h-auto lg:min-h-0 lg:self-stretch">
                <iframe
                    src="{{ \App\Support\Content::get('showroom_map_embed') }}"
                    width="600"
                    height="450"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Mappa dello showroom Sweet Home"
                    class="w-full h-full">
                </iframe>
            </div>

            <div class="lg:self-center">
                <p class="text-brand-gold text-sm tracking-[0.3em] uppercase mb-4">{{ \App\Support\Content::get('showroom_eyebrow') }}</p>
                <h2 class="text-3xl md:text-4xl font-serif font-bold text-brand-light mb-6">
                    {{ \App\Support\Content::get('showroom_title') }}
                </h2>
                <p class="text-gray-400 leading-relaxed mb-4">
                    {{ \App\Support\Content::get('showroom_description') }}
                </p>
                <p class="text-gray-400 leading-relaxed mb-8">
                    {{ \App\Support\Content::get('showroom_extra_description') }}
                </p>
                <a href="{{ \App\Support\Content::get('showroom_map_url') }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 text-brand-gold text-sm font-semibold tracking-widest uppercase hover:gap-4 transition-all duration-300">
                    Apri su Google Maps
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ============================================
     I NOSTRI PROGETTI IN PRIMO PIANO
     ============================================ --}}
<section class="bg-brand-dark border-t border-white/5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="text-center mb-16">
            <p class="text-brand-gold text-sm tracking-[0.3em] uppercase mb-4">Portfolio</p>
            <h2 class="text-3xl md:text-4xl font-serif font-bold text-brand-light">
                I nostri progetti in primo piano
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Project 1 -->
            <a href="/projects/1" class="group relative overflow-hidden rounded-sm">
                <div class="aspect-[16/10] overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800&q=80"
                         alt="Villa Moderna Milano"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-brand-dark via-brand-dark/20 to-transparent opacity-80 group-hover:opacity-90 transition-opacity duration-300"></div>
                <div class="absolute bottom-0 left-0 right-0 p-8">
                    <span class="text-brand-gold text-xs tracking-[0.2em] uppercase">Residenziale</span>
                    <h3 class="text-2xl font-serif text-brand-light mt-2 mb-2">Villa Moderna Milano</h3>
                    <p class="text-sm text-gray-300 mb-4">Ristrutturazione completa di una villa contemporanea con finiture di lusso.</p>
                    <span class="inline-flex items-center gap-2 text-brand-gold text-sm font-semibold tracking-wider uppercase group-hover:gap-4 transition-all duration-300">
                        Vedi Progetto
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </span>
                </div>
            </a>

            <!-- Project 2 -->
            <a href="/projects/2" class="group relative overflow-hidden rounded-sm">
                <div class="aspect-[16/10] overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?w=800&q=80"
                         alt="Bright room with large windows"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-brand-dark via-brand-dark/20 to-transparent opacity-80 group-hover:opacity-90 transition-opacity duration-300"></div>
                <div class="absolute bottom-0 left-0 right-0 p-8">
                    <span class="text-brand-gold text-xs tracking-[0.2em] uppercase">Residenziale</span>
                    <h3 class="text-2xl font-serif text-brand-light mt-2 mb-2">Attico Brera</h3>
                    <p class="text-sm text-gray-300 mb-4">Design minimalista per un attico con vista panoramica sul centro storico.</p>
                    <span class="inline-flex items-center gap-2 text-brand-gold text-sm font-semibold tracking-wider uppercase group-hover:gap-4 transition-all duration-300">
                        Vedi Progetto
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </span>
                </div>
            </a>

            <!-- Project 3 -->
            <a href="/projects/3" class="group relative overflow-hidden rounded-sm">
                <div class="aspect-[16/10] overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80"
                         alt="Premium hardwood floor close-up"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-brand-dark via-brand-dark/20 to-transparent opacity-80 group-hover:opacity-90 transition-opacity duration-300"></div>
                <div class="absolute bottom-0 left-0 right-0 p-8">
                    <span class="text-brand-gold text-xs tracking-[0.2em] uppercase">Commerciale</span>
                    <h3 class="text-2xl font-serif text-brand-light mt-2 mb-2">Loft Industriale</h3>
                    <p class="text-sm text-gray-300 mb-4">Conversione di uno spazio industriale in un loft residenziale di charme.</p>
                    <span class="inline-flex items-center gap-2 text-brand-gold text-sm font-semibold tracking-wider uppercase group-hover:gap-4 transition-all duration-300">
                        Vedi Progetto
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </span>
                </div>
            </a>

            <!-- Project 4 -->
            <a href="/projects/4" class="group relative overflow-hidden rounded-sm">
                <div class="aspect-[16/10] overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=800&q=80"
                         alt="Penthouse Navigli"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-brand-dark via-brand-dark/20 to-transparent opacity-80 group-hover:opacity-90 transition-opacity duration-300"></div>
                <div class="absolute bottom-0 left-0 right-0 p-8">
                    <span class="text-brand-gold text-xs tracking-[0.2em] uppercase">Residenziale</span>
                    <h3 class="text-2xl font-serif text-brand-light mt-2 mb-2">Penthouse Navigli</h3>
                    <p class="text-sm text-gray-300 mb-4">Arredamento di lusso per un penthouse con terrazza panoramica.</p>
                    <span class="inline-flex items-center gap-2 text-brand-gold text-sm font-semibold tracking-wider uppercase group-hover:gap-4 transition-all duration-300">
                        Vedi Progetto
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </span>
                </div>
            </a>
        </div>

        <div class="text-center mt-12">
            <a href="/projects" class="inline-flex items-center gap-2 px-8 py-4 border border-brand-gold/50 text-brand-gold text-sm font-semibold tracking-widest uppercase hover:bg-brand-gold hover:text-brand-dark transition-all duration-300">
                Tutti i Progetti
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>
    </div>
</section>

{{-- ============================================
     I NOSTRI PARTNER
     ============================================ --}}
<section class="bg-brand-dark border-t border-white/5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center mb-12">
            <h2 class="text-2xl md:text-3xl font-serif font-bold text-brand-light">
                I nostri partner
            </h2>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 items-center">
            <!-- Partner 1 -->
            <div class="flex items-center justify-center p-6 opacity-50 hover:opacity-100 transition-opacity duration-300">
                <div class="text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>
                    </svg>
                    <p class="text-xs text-gray-500 mt-2 tracking-wider uppercase">LuxBrand</p>
                </div>
            </div>

            <!-- Partner 2 -->
            <div class="flex items-center justify-center p-6 opacity-50 hover:opacity-100 transition-opacity duration-300">
                <div class="text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z"/>
                    </svg>
                    <p class="text-xs text-gray-500 mt-2 tracking-wider uppercase">ItalianDesign</p>
                </div>
            </div>

            <!-- Partner 3 -->
            <div class="flex items-center justify-center p-6 opacity-50 hover:opacity-100 transition-opacity duration-300">
                <div class="text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
                    </svg>
                    <p class="text-xs text-gray-500 mt-2 tracking-wider uppercase">CraftMaster</p>
                </div>
            </div>

            <!-- Partner 4 -->
            <div class="flex items-center justify-center p-6 opacity-50 hover:opacity-100 transition-opacity duration-300">
                <div class="text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17l-5.1-5.1m0 0L12 4.37m-5.68 5.7h13.38"/>
                    </svg>
                    <p class="text-xs text-gray-500 mt-2 tracking-wider uppercase">EcoLiving</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================
     NEWSLETTER / PREVENTIVO
     ============================================ --}}
<section class="bg-brand-slate">
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

@endsection
