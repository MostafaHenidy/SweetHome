<section class="border border-white/5 bg-[#24384a] p-6 lg:p-8 shadow-[0_12px_30px_rgba(0,0,0,0.18)]">
    <div class="grid gap-6 xl:grid-cols-[220px_minmax(0,1fr)] xl:gap-8">
        <div>
            <p class="text-xs font-semibold tracking-[0.3em] uppercase text-brand-gold/80">{{ __('Sicurezza') }}</p>
            <h2 class="mt-3 text-2xl font-serif text-brand-light">{{ __('Update Password') }}</h2>
            <p class="mt-3 text-sm leading-relaxed text-gray-400">
                {{ __('Ensure your account is using a long, random password to stay secure.') }}
            </p>
        </div>

        <form wire:submit="updatePassword" class="space-y-5">
            <div>
                <label for="current_password" class="block text-xs font-semibold tracking-[0.25em] uppercase text-gray-400">{{ __('Current Password') }}</label>
                <x-input id="current_password" type="password" wire:model="state.current_password" autocomplete="current-password"
                         class="mt-3 block w-full rounded-2xl border border-white/10 bg-brand-dark/70 px-4 py-3 text-brand-light placeholder:text-gray-500 focus:border-brand-gold focus:ring-brand-gold" />
                <x-input-error for="current_password" class="mt-3 text-sm text-red-300" />
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label for="password" class="block text-xs font-semibold tracking-[0.25em] uppercase text-gray-400">{{ __('New Password') }}</label>
                    <x-input id="password" type="password" wire:model="state.password" autocomplete="new-password"
                             class="mt-3 block w-full rounded-2xl border border-white/10 bg-brand-dark/70 px-4 py-3 text-brand-light placeholder:text-gray-500 focus:border-brand-gold focus:ring-brand-gold" />
                    <x-input-error for="password" class="mt-3 text-sm text-red-300" />
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold tracking-[0.25em] uppercase text-gray-400">{{ __('Confirm Password') }}</label>
                    <x-input id="password_confirmation" type="password" wire:model="state.password_confirmation" autocomplete="new-password"
                             class="mt-3 block w-full rounded-2xl border border-white/10 bg-brand-dark/70 px-4 py-3 text-brand-light placeholder:text-gray-500 focus:border-brand-gold focus:ring-brand-gold" />
                    <x-input-error for="password_confirmation" class="mt-3 text-sm text-red-300" />
                </div>
            </div>

            <div class="flex flex-col gap-3 border-t border-white/10 pt-6 sm:flex-row sm:items-center sm:justify-between">
                <x-action-message on="saved" class="text-sm text-brand-gold">
                    {{ __('Saved.') }}
                </x-action-message>

                <button type="submit"
                        class="inline-flex items-center justify-center rounded-full bg-brand-gold px-6 py-3 text-xs font-semibold tracking-[0.3em] uppercase text-brand-dark transition hover:opacity-90">
                    {{ __('Save') }}
                </button>
            </div>
        </form>
    </div>
</section>
