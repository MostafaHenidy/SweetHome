@extends('layouts.app-shop')

@section('content')
<div class="max-w-md mx-auto px-4 py-12">
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <!-- Success Header -->
        <div class="bg-green-500 px-6 py-8 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-full mb-4">
                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-white">Coupon Redeemed!</h2>
        </div>

        <!-- Details -->
        <div class="p-6">
            <div class="text-center mb-6">
                <p class="text-gray-600 mb-2">You've earned</p>
                <p class="text-4xl font-bold text-green-600">+{{ $points }} points</p>
                <p class="text-sm text-gray-500 mt-1">from {{ $coupon->code }}</p>
            </div>

            <div class="border-t border-gray-200 pt-4 mb-6">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Previous Balance</span>
                    <span class="font-medium">{{ $newBalance - $points }} points</span>
                </div>
                <div class="flex justify-between items-center mt-2">
                    <span class="text-gray-600">Points Added</span>
                    <span class="font-medium text-green-600">+{{ $points }} points</span>
                </div>
                <div class="flex justify-between items-center mt-3 pt-3 border-t border-gray-200">
                    <span class="font-semibold">New Balance</span>
                    <span class="font-bold text-lg">{{ $newBalance }} points</span>
                </div>
            </div>

            <div class="space-y-3">
                <a href="{{ route('account.favorites') }}"
                   class="block w-full py-3 px-4 bg-brand-gold text-center text-brand-dark font-semibold rounded hover:bg-brand-gold/90 transition">
                    View Your Points
                </a>
                <a href="{{ route('shop') }}"
                   class="block w-full py-3 px-4 bg-gray-100 text-center text-gray-700 font-medium rounded hover:bg-gray-200 transition">
                    Continue Shopping
                </a>
            </div>
        </div>
    </div>

    <!-- Additional Info -->
    <div class="mt-6 text-center text-sm text-gray-500">
        <p>Points have been added to your account.</p>
        <p class="mt-1">You can use your points at checkout.</p>
    </div>
</div>
@endsection