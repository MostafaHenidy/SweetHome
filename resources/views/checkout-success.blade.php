@extends('layouts.app-shop')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
    <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-green-500/20 flex items-center justify-center">
        <svg class="w-10 h-10 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
    </div>
    <h1 class="text-3xl font-serif font-bold text-brand-light mb-4">Ordine Confermato!</h1>
    <p class="text-gray-400 mb-2">Grazie per il tuo acquisto, {{ $order->customer_name }}.</p>
    <p class="text-gray-400 mb-8">Numero ordine: <span class="text-brand-gold font-mono">{{ $order->order_number }}</span></p>
    <p class="text-sm text-gray-500 mb-8">Riceverai una email di conferma a {{ $order->customer_email }}.</p>
    <a href="{{ route('shop') }}" class="inline-block px-8 py-3 bg-brand-gold text-brand-dark text-sm font-semibold tracking-widest uppercase hover:bg-opacity-90 transition rounded-sm">Continua lo Shopping</a>
</div>
@endsection
