<button type="button" wire:click.prevent.stop="toggleFavorite"
        class="p-2 rounded-full transition-colors duration-200 {{ $isFavorite ? 'text-red-500 hover:text-red-600 bg-red-50' : 'text-gray-400 hover:text-red-500 hover:bg-red-50' }}"
        title="{{ $isFavorite ? 'Remove from favorites' : 'Add to favorites' }}">
    <svg class="w-5 h-5" fill="{{ $isFavorite ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
    </svg>
</button>