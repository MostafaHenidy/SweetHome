@extends('layouts.app-home')

@section('content')
@php
    $siteEmail = \App\Models\Setting::get('site_email', 'info@sweethome.it');
    $sitePhone = \App\Models\Setting::get('site_phone', '328 899 5628');
    $siteAddress = \App\Models\Setting::get('site_address', 'Via Carlo Espinasse 104/a, 20156 Milano MI');
@endphp

<section class="bg-brand-dark pt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <div>
                <p class="text-brand-gold text-sm tracking-[0.3em] uppercase mb-4">{{ \App\Support\Content::get('contact_page_eyebrow') }}</p>
                <h1 class="text-4xl md:text-5xl font-serif font-bold text-brand-light mb-6">
                    {{ \App\Support\Content::get('contact_page_title') }}
                </h1>
                <p class="text-gray-400 leading-relaxed mb-10">
                    {{ \App\Support\Content::get('contact_page_description') }}
                </p>

                <div class="space-y-5 text-gray-400">
                    <p><span class="text-brand-gold">Indirizzo:</span> {{ $siteAddress }}</p>
                    <p><span class="text-brand-gold">Telefono:</span> <a href="tel:{{ preg_replace('/\s+/', '', $sitePhone) }}" class="hover:text-brand-light transition">{{ $sitePhone }}</a></p>
                    <p><span class="text-brand-gold">Email:</span> <a href="mailto:{{ $siteEmail }}" class="hover:text-brand-light transition">{{ $siteEmail }}</a></p>
                </div>
            </div>

            <form class="bg-brand-slate p-8 rounded-sm border border-white/5 space-y-5">
                <div>
                    <label class="block text-xs tracking-wider uppercase text-gray-400 mb-2">Nome</label>
                    <input type="text" class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light text-sm rounded-sm focus:outline-none focus:border-brand-gold transition-colors">
                </div>
                <div>
                    <label class="block text-xs tracking-wider uppercase text-gray-400 mb-2">Email</label>
                    <input type="email" class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light text-sm rounded-sm focus:outline-none focus:border-brand-gold transition-colors">
                </div>
                <div>
                    <label class="block text-xs tracking-wider uppercase text-gray-400 mb-2">Messaggio</label>
                    <textarea rows="5" class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light text-sm rounded-sm focus:outline-none focus:border-brand-gold transition-colors"></textarea>
                </div>
                <button type="button" class="w-full px-8 py-4 bg-brand-gold text-brand-dark text-sm font-semibold tracking-widest uppercase hover:bg-opacity-90 transition-all duration-300 rounded-sm">
                    Invia Richiesta
                </button>
            </form>
        </div>
    </div>
</section>
@endsection
