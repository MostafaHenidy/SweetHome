<div>
    <div class="max-w-4xl mx-auto">
        <!-- Create Inquiry Form -->
        @if($showCreateForm)
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">New Inquiry</h3>
                <button wire:click="toggleCreateForm" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="px-6 py-4">
                <form wire:submit="createInquiry">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Subject</label>
                            <input type="text" wire:model="subject" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="What is your inquiry about?">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Related Product (Optional)</label>
                            <select wire:model="product_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Select a product (if applicable) --</option>
                                @foreach(\App\Models\Product::all() as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Message</label>
                            <textarea wire:model="message" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Describe your inquiry in detail..."></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" wire:click="toggleCreateForm" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Cancel
                        </button>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Submit Inquiry
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        <!-- Inquiries List -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">My Inquiries</h3>
                    <p class="mt-1 text-sm text-gray-600">Track your customer service inquiries.</p>
                </div>
                <button wire:click="toggleCreateForm"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Inquiry
                </button>
            </div>

            <div class="px-6 py-4">
                @if(count($inquiries) > 0)
                    <div class="space-y-4">
                        @foreach($inquiries as $inquiry)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3">
                                            <h4 class="font-medium text-gray-900">{{ $inquiry->subject }}</h4>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($inquiry->status === 'pending') bg-yellow-100 text-yellow-800
                                                @elseif($inquiry->status === 'contacted') bg-blue-100 text-blue-800
                                                @else bg-green-100 text-green-800 @endif">
                                                {{ ucfirst($inquiry->status) }}
                                            </span>
                                        </div>
                                        <p class="mt-1 text-sm text-gray-600">{{ Str::limit($inquiry->message, 100) }}</p>
                                        @if($inquiry->product)
                                            <p class="mt-2 text-sm text-gray-500">
                                                Related to: <a href="{{ route('product.show', $inquiry->product->slug) }}" class="text-indigo-600 hover:text-indigo-800">{{ $inquiry->product->name }}</a>
                                            </p>
                                        @endif
                                        <p class="mt-2 text-xs text-gray-400">
                                            Submitted {{ $inquiry->created_at->diffForHumans() }}
                                            @if($inquiry->responded_at)
                                                • Responded {{ $inquiry->responded_at->diffForHumans() }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No inquiries yet</h3>
                        <p class="mt-1 text-sm text-gray-500">You haven't submitted any inquiries yet.</p>
                        <div class="mt-6">
                            <button wire:click="toggleCreateForm" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Create Your First Inquiry
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>