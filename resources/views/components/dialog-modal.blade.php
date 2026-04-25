@props(['id' => null, 'maxWidth' => null])

<x-modal :id="$id" :maxWidth="$maxWidth" {{ $attributes }}>
    <div class="px-6 py-4">
        <div class="text-lg font-serif text-brand-light">
            {{ $title }}
        </div>

        <div class="mt-4 text-sm text-gray-300">
            {{ $content }}
        </div>
    </div>

    <div class="border-t border-white/10 bg-black/20 px-6 py-4 text-end">
        {{ $footer }}
    </div>
</x-modal>
