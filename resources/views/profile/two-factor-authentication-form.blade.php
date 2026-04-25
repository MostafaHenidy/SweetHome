<section class="border border-white/5 bg-[#24384a] p-6 lg:p-8 shadow-[0_12px_30px_rgba(0,0,0,0.18)]">
    <div class="grid gap-6 xl:grid-cols-[220px_minmax(0,1fr)] xl:gap-8">
        <div>
            <p class="text-xs font-semibold tracking-[0.3em] uppercase text-brand-gold/80">{{ __('Protezione') }}</p>
            <h2 class="mt-3 text-2xl font-serif text-brand-light">{{ __('Two Factor Authentication') }}</h2>
            <p class="mt-3 text-sm leading-relaxed text-gray-400">
                {{ __('Add additional security to your account using two factor authentication.') }}
            </p>
        </div>

        <div class="space-y-5">
            <div class="border border-white/5 bg-black/15 p-5">
                <h3 class="text-lg font-medium text-brand-light">
                    @if ($this->enabled)
                        @if ($showingConfirmation)
                            {{ __('Finish enabling two factor authentication.') }}
                        @else
                            {{ __('You have enabled two factor authentication.') }}
                        @endif
                    @else
                        {{ __('You have not enabled two factor authentication.') }}
                    @endif
                </h3>

                <p class="mt-3 max-w-2xl text-sm leading-relaxed text-gray-400">
                    {{ __('When two factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone\'s Google Authenticator application.') }}
                </p>
            </div>

            @if ($this->enabled && $showingQrCode)
                <div class="border border-white/5 bg-black/15 p-5">
                    <p class="text-sm font-semibold text-brand-light">
                        @if ($showingConfirmation)
                            {{ __('To finish enabling two factor authentication, scan the following QR code using your phone\'s authenticator application or enter the setup key and provide the generated OTP code.') }}
                        @else
                            {{ __('Two factor authentication is now enabled. Scan the following QR code using your phone\'s authenticator application or enter the setup key.') }}
                        @endif
                    </p>

                    <div class="mt-5 inline-flex bg-white p-4">
                        {!! $this->user->twoFactorQrCodeSvg() !!}
                    </div>

                    <div class="mt-5 border border-white/5 bg-black/15 px-4 py-3">
                        <p class="text-xs font-semibold tracking-[0.25em] uppercase text-gray-500">{{ __('Setup Key') }}</p>
                        <p class="mt-2 break-all font-mono text-sm text-brand-light">{{ decrypt($this->user->two_factor_secret) }}</p>
                    </div>

                    @if ($showingConfirmation)
                        <div class="mt-5 max-w-sm">
                            <label for="code" class="block text-xs font-semibold tracking-[0.25em] uppercase text-gray-400">{{ __('Code') }}</label>
                            <x-input id="code" type="text" name="code" inputmode="numeric" autofocus autocomplete="one-time-code"
                                     wire:model="code"
                                     wire:keydown.enter="confirmTwoFactorAuthentication"
                                     class="mt-3 block w-full rounded-2xl border border-white/10 bg-brand-dark/70 px-4 py-3 text-brand-light placeholder:text-gray-500 focus:border-brand-gold focus:ring-brand-gold" />
                            <x-input-error for="code" class="mt-3 text-sm text-red-300" />
                        </div>
                    @endif
                </div>
            @endif

            @if ($this->enabled && $showingRecoveryCodes)
                <div class="border border-white/5 bg-black/15 p-5">
                    <p class="text-sm font-semibold text-brand-light">
                        {{ __('Store these recovery codes in a secure password manager. They can be used to recover access to your account if your two factor authentication device is lost.') }}
                    </p>

                    <div class="mt-5 grid gap-2 border border-white/5 bg-black/15 px-4 py-4 font-mono text-sm text-brand-light">
                        @foreach (json_decode(decrypt($this->user->two_factor_recovery_codes), true) as $code)
                            <div>{{ $code }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex flex-wrap gap-3 border-t border-white/10 pt-6">
                @if (! $this->enabled)
                    <x-confirms-password wire:then="enableTwoFactorAuthentication">
                        <button type="button"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center rounded-full bg-brand-gold px-6 py-3 text-xs font-semibold tracking-[0.3em] uppercase text-brand-dark transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-60">
                            {{ __('Enable') }}
                        </button>
                    </x-confirms-password>
                @else
                    @if ($showingRecoveryCodes)
                        <x-confirms-password wire:then="regenerateRecoveryCodes">
                            <button type="button"
                                    class="inline-flex items-center justify-center rounded-full border border-white/15 px-5 py-3 text-xs font-semibold tracking-[0.25em] uppercase text-brand-light transition hover:border-brand-gold/50 hover:text-brand-gold">
                                {{ __('Regenerate Recovery Codes') }}
                            </button>
                        </x-confirms-password>
                    @elseif ($showingConfirmation)
                        <x-confirms-password wire:then="confirmTwoFactorAuthentication">
                            <button type="button"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center justify-center rounded-full bg-brand-gold px-6 py-3 text-xs font-semibold tracking-[0.3em] uppercase text-brand-dark transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-60">
                                {{ __('Confirm') }}
                            </button>
                        </x-confirms-password>
                    @else
                        <x-confirms-password wire:then="showRecoveryCodes">
                            <button type="button"
                                    class="inline-flex items-center justify-center rounded-full border border-white/15 px-5 py-3 text-xs font-semibold tracking-[0.25em] uppercase text-brand-light transition hover:border-brand-gold/50 hover:text-brand-gold">
                                {{ __('Show Recovery Codes') }}
                            </button>
                        </x-confirms-password>
                    @endif

                    @if ($showingConfirmation)
                        <x-confirms-password wire:then="disableTwoFactorAuthentication">
                            <button type="button"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center justify-center rounded-full border border-white/15 px-5 py-3 text-xs font-semibold tracking-[0.25em] uppercase text-brand-light transition hover:border-brand-gold/50 hover:text-brand-gold">
                                {{ __('Cancel') }}
                            </button>
                        </x-confirms-password>
                    @else
                        <x-confirms-password wire:then="disableTwoFactorAuthentication">
                            <button type="button"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center justify-center rounded-full border border-red-400/30 px-5 py-3 text-xs font-semibold tracking-[0.25em] uppercase text-red-300 transition hover:border-red-300/60 hover:text-red-200">
                                {{ __('Disable') }}
                            </button>
                        </x-confirms-password>
                    @endif
                @endif
            </div>
        </div>
    </div>
</section>
