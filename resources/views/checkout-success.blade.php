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
    <p class="text-sm text-gray-500 mb-6">Riceverai una email di conferma a {{ $order->customer_email }}.</p>

    {{-- Payment Plan Summary --}}
    @if($order->paymentInstallments->count() > 0)
    <div class="bg-gray-800/50 rounded-lg p-6 mb-8 text-left max-w-md mx-auto">
        <h3 class="text-lg font-semibold text-brand-gold mb-4">Piano di Pagamento</h3>
        <div class="space-y-3">
            @foreach($order->paymentInstallments as $installment)
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center
                        {{ $installment->isPaid() ? 'bg-green-500/20 text-green-400' : 'bg-gray-600/50 text-gray-400' }}">
                        @if($installment->isPaid())
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        @else
                            {{ $installment->installment_number }}
                        @endif
                    </div>
                    <div>
                        <p class="text-sm text-gray-300">{{ $installment->due_stage_label }}</p>
                        <p class="text-xs text-gray-500">{{ $installment->percentage }}% - Installment {{ $installment->installment_number }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm font-semibold {{ $installment->isPaid() ? 'text-green-400' : 'text-gray-300' }}">
                        {{ $installment->formatted_amount }}
                    </p>
                    <p class="text-xs {{ $installment->isPaid() ? 'text-green-400' : 'text-gray-500' }}">
                        {{ $installment->isPaid() ? 'Pagato' : 'In attesa' }}
                    </p>
                </div>
            </div>
            @endforeach
        </div>
        <div class="border-t border-gray-700 mt-4 pt-4">
            <div class="flex justify-between">
                <span class="text-gray-400">Totale Ordine:</span>
                <span class="text-white font-semibold">{{ '€' . number_format($order->total, 2, ',', '.') }}</span>
            </div>
        </div>
    </div>
    @endif

    @if(!empty($confirmationUrl))
        <p class="mb-3">
            <a href="{{ $confirmationUrl }}" class="inline-block px-6 py-2 border border-brand-gold/50 text-brand-gold text-sm font-semibold tracking-widest uppercase rounded-sm hover:bg-brand-gold/10 transition">
                Scarica modulo ordine (PDF)
            </a>
        </p>
    @endif
    @if(!empty($invoiceUrl))
        <p class="mb-8">
            <a href="{{ $invoiceUrl }}" class="inline-block px-6 py-2 border border-white/20 text-gray-200 text-sm font-semibold tracking-widest uppercase rounded-sm hover:bg-white/5 transition">
                Scarica ricevuta / fattura (PDF)
            </a>
        </p>
    @endif

    <a href="{{ route('shop') }}" class="inline-block px-8 py-3 bg-brand-gold text-brand-dark text-sm font-semibold tracking-widest uppercase hover:bg-opacity-90 transition rounded-sm">Continua lo Shopping</a>
</div>
@endsection
