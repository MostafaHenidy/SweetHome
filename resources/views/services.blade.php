@extends('layouts.app-home')

@section('content')
<section class="bg-brand-dark pt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="max-w-3xl mb-16">
            <p class="text-brand-gold text-sm tracking-[0.3em] uppercase mb-4">{{ \App\Support\Content::get('services_page_eyebrow') }}</p>
            <h1 class="text-4xl md:text-5xl font-serif font-bold text-brand-light mb-6">
                {{ \App\Support\Content::get('services_page_title') }}
            </h1>
            <p class="text-gray-400 leading-relaxed">
                {{ \App\Support\Content::get('services_page_description') }}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @for ($i = 1; $i <= 3; $i++)
                <div class="bg-brand-slate p-8 rounded-sm border border-white/5">
                    <span class="text-brand-gold text-sm tracking-[0.2em] uppercase">0{{ $i }}</span>
                    <h2 class="text-2xl font-serif text-brand-light mt-4 mb-4">{{ \App\Support\Content::get("service_{$i}_title") }}</h2>
                    <p class="text-sm text-gray-400 leading-relaxed">{{ \App\Support\Content::get("service_{$i}_description") }}</p>
                </div>
            @endfor
        </div>
    </div>
</section>
@endsection
