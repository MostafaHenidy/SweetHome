<section class="border border-red-400/20 bg-[#3a2024] p-6 lg:p-8 shadow-[0_12px_30px_rgba(0,0,0,0.18)]">
    <div class="grid gap-6 xl:grid-cols-[220px_minmax(0,1fr)] xl:gap-8">
        <div>
            <p class="text-xs font-semibold tracking-[0.3em] uppercase text-red-300/80">{{ __('Zona critica') }}</p>
            <h2 class="mt-3 text-2xl font-serif text-brand-light">{{ __('Delete Account') }}</h2>
            <p class="mt-3 text-sm leading-relaxed text-gray-400">
                {{ __('Permanently delete your account.') }}
            </p>
        </div>

        <div>
            <p class="max-w-2xl text-sm leading-relaxed text-gray-400">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
            </p>

            <div class="mt-6 border-t border-red-400/20 pt-6">
                <button type="button"
                        wire:click="confirmUserDeletion"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center rounded-full border border-red-400/40 px-5 py-3 text-xs font-semibold tracking-[0.25em] uppercase text-red-300 transition hover:border-red-300/70 hover:text-red-200 disabled:cursor-not-allowed disabled:opacity-60">
                    {{ __('Delete Account') }}
                </button>
            </div>

            <x-dialog-modal wire:model.live="confirmingUserDeletion">
                <x-slot name="title">
                    {{ __('Delete Account') }}
                </x-slot>

                <x-slot name="content">
                    <p class="text-sm leading-relaxed text-gray-300">
                        {{ __('Are you sure you want to delete your account? Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                    </p>

                    <div class="mt-5" x-data="{}" x-on:confirming-delete-user.window="setTimeout(() => $refs.password.focus(), 250)">
                        <x-input type="password"
                                 autocomplete="current-password"
                                 placeholder="{{ __('Password') }}"
                                 x-ref="password"
                                 wire:model="password"
                                 wire:keydown.enter="deleteUser"
                                 class="block w-full rounded-2xl border border-white/10 bg-brand-dark/70 px-4 py-3 text-brand-light placeholder:text-gray-500 focus:border-brand-gold focus:ring-brand-gold" />

                        <x-input-error for="password" class="mt-3 text-sm text-red-300" />
                    </div>
                </x-slot>

                <x-slot name="footer">
                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button type="button"
                                wire:click="$toggle('confirmingUserDeletion')"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center rounded-full border border-white/15 px-5 py-3 text-xs font-semibold tracking-[0.25em] uppercase text-brand-light transition hover:border-brand-gold/50 hover:text-brand-gold">
                            {{ __('Cancel') }}
                        </button>

                        <button type="button"
                                wire:click="deleteUser"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center rounded-full border border-red-400/40 px-5 py-3 text-xs font-semibold tracking-[0.25em] uppercase text-red-300 transition hover:border-red-300/70 hover:text-red-200">
                            {{ __('Delete Account') }}
                        </button>
                    </div>
                </x-slot>
            </x-dialog-modal>
        </div>
    </div>
</section>
