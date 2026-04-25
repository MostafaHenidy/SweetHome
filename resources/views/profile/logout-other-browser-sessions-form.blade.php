<section class="border border-white/5 bg-[#24384a] p-6 lg:p-8 shadow-[0_12px_30px_rgba(0,0,0,0.18)]">
    <div class="grid gap-6 xl:grid-cols-[220px_minmax(0,1fr)] xl:gap-8">
        <div>
            <p class="text-xs font-semibold tracking-[0.3em] uppercase text-brand-gold/80">{{ __('Dispositivi') }}</p>
            <h2 class="mt-3 text-2xl font-serif text-brand-light">{{ __('Browser Sessions') }}</h2>
            <p class="mt-3 text-sm leading-relaxed text-gray-400">
                {{ __('Manage and log out your active sessions on other browsers and devices.') }}
            </p>
        </div>

        <div>
            <p class="max-w-2xl text-sm leading-relaxed text-gray-400">
                {{ __('If necessary, you may log out of all of your other browser sessions across all of your devices. Some of your recent sessions are listed below; however, this list may not be exhaustive. If you feel your account has been compromised, you should also update your password.') }}
            </p>

            @if (count($this->sessions) > 0)
                <div class="mt-6 space-y-4">
                    @foreach ($this->sessions as $session)
                        <div class="flex items-start gap-4 border border-white/5 bg-black/15 p-5">
                            <div class="shrink-0">
                                @if ($session->agent->isDesktop())
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 text-brand-gold/80">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25" />
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 text-brand-gold/80">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                                    </svg>
                                @endif
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-brand-light">
                                    {{ $session->agent->platform() ? $session->agent->platform() : __('Unknown') }}
                                    <span class="text-gray-500">/</span>
                                    {{ $session->agent->browser() ? $session->agent->browser() : __('Unknown') }}
                                </p>

                                <p class="mt-1 text-sm text-gray-400">
                                    {{ $session->ip_address }}
                                </p>

                                <p class="mt-2 text-xs tracking-[0.2em] uppercase text-gray-500">
                                    @if ($session->is_current_device)
                                        <span class="text-emerald-300">{{ __('This device') }}</span>
                                    @else
                                        {{ __('Last active') }} {{ $session->last_active }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="mt-6 flex flex-col gap-3 border-t border-white/10 pt-6 sm:flex-row sm:items-center sm:justify-between">
                <x-action-message on="loggedOut" class="text-sm text-brand-gold">
                    {{ __('Done.') }}
                </x-action-message>

                <button type="button"
                        wire:click="confirmLogout"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center rounded-full border border-white/15 px-5 py-3 text-xs font-semibold tracking-[0.25em] uppercase text-brand-light transition hover:border-brand-gold/50 hover:text-brand-gold disabled:cursor-not-allowed disabled:opacity-60">
                    {{ __('Log Out Other Browser Sessions') }}
                </button>
            </div>

            <x-dialog-modal wire:model.live="confirmingLogout">
                <x-slot name="title">
                    {{ __('Log Out Other Browser Sessions') }}
                </x-slot>

                <x-slot name="content">
                    <p class="text-sm leading-relaxed text-gray-300">
                        {{ __('Please enter your password to confirm you would like to log out of your other browser sessions across all of your devices.') }}
                    </p>

                    <div class="mt-5" x-data="{}" x-on:confirming-logout-other-browser-sessions.window="setTimeout(() => $refs.password.focus(), 250)">
                        <x-input type="password"
                                 autocomplete="current-password"
                                 placeholder="{{ __('Password') }}"
                                 x-ref="password"
                                 wire:model="password"
                                 wire:keydown.enter="logoutOtherBrowserSessions"
                                 class="block w-full rounded-2xl border border-white/10 bg-brand-dark/70 px-4 py-3 text-brand-light placeholder:text-gray-500 focus:border-brand-gold focus:ring-brand-gold" />

                        <x-input-error for="password" class="mt-3 text-sm text-red-300" />
                    </div>
                </x-slot>

                <x-slot name="footer">
                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button type="button"
                                wire:click="$toggle('confirmingLogout')"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center rounded-full border border-white/15 px-5 py-3 text-xs font-semibold tracking-[0.25em] uppercase text-brand-light transition hover:border-brand-gold/50 hover:text-brand-gold">
                            {{ __('Cancel') }}
                        </button>

                        <button type="button"
                                wire:click="logoutOtherBrowserSessions"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center rounded-full bg-brand-gold px-6 py-3 text-xs font-semibold tracking-[0.3em] uppercase text-brand-dark transition hover:opacity-90">
                            {{ __('Log Out Other Browser Sessions') }}
                        </button>
                    </div>
                </x-slot>
            </x-dialog-modal>
        </div>
    </div>
</section>
