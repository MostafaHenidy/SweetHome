@extends('layouts.app-shop')

@section('content')
<div class="max-w-md mx-auto px-4 py-12">
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <!-- Error Header -->
        <div class="bg-red-500 px-6 py-8 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-full mb-4">
                <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-white">{{ $error }}</h2>
        </div>

        <!-- Details -->
        <div class="p-6">
            <div class="text-center mb-6">
                <p class="text-gray-600">{{ $message }}</p>
            </div>

            <div class="space-y-3">
                <a href="{{ route('home') }}"
                   class="block w-full py-3 px-4 bg-brand-gold text-center text-brand-dark font-semibold rounded hover:bg-brand-gold/90 transition">
                    Go to Homepage
                </a>
                <a href="{{ route('shop') }}"
                   class="block w-full py-3 px-4 bg-gray-100 text-center text-gray-700 font-medium rounded hover:bg-gray-200 transition">
                    Browse Products
                </a>
            </div>
        </div>
    </div>

    <!-- Help Section -->
    <div class="mt-6 text-center text-sm text-gray-500">
        <p>Need help?</p>
        <p class="mt-1">
            <a href="{{ route('contact') }}" class="text-brand-gold hover:underline">
                Contact Customer Support
            </a>
        </p>
    </div>
</div>
@endsection