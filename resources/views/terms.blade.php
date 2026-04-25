@extends('layouts.app-home')

@section('content')
<section class="bg-brand-dark">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <article class="prose prose-invert max-w-none prose-headings:font-serif prose-a:text-brand-gold">
            {!! \App\Support\Content::get('terms_content') !!}
        </article>
    </div>
</section>
@endsection
