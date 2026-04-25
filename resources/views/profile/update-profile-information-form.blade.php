<section class="border border-white/5 bg-[#24384a] p-6 lg:p-8 shadow-[0_12px_30px_rgba(0,0,0,0.18)]">
    <div class="grid gap-6 xl:grid-cols-[220px_minmax(0,1fr)] xl:gap-8">
        <div>
            <p class="text-xs font-semibold tracking-[0.3em] uppercase text-brand-gold/80">{{ __('Identita') }}</p>
            <h2 class="mt-3 text-2xl font-serif text-brand-light">{{ __('Profile Information') }}</h2>
            <p class="mt-3 text-sm leading-relaxed text-gray-400">
                {{ __('Update your account\'s profile information and email address.') }}
            </p>
        </div>

        <form wire:submit="updateProfileInformation" class="space-y-6">
            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                <div x-data="{ photoName: null, photoPreview: null }" class="rounded-3xl border border-white/10 bg-white/5 p-5">
                    <input type="file" id="photo" class="hidden"
                           wire:model.live="photo"
                           x-ref="photo"
                           x-on:change="
                               photoName = $refs.photo.files[0].name;
                               const reader = new FileReader();
                               reader.onload = (e) => {
                                   photoPreview = e.target.result;
                               };
                               reader.readAsDataURL($refs.photo.files[0]);
                           " />

                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs tracking-[0.25em] uppercase text-gray-500">{{ __('Profile Photo') }}</p>
                            <p class="mt-2 text-sm text-gray-400">{{ __('Choose an image that represents your account.') }}</p>
                        </div>

                        <div class="flex items-center gap-4">
                            <div x-show="! photoPreview">
                                <img src="{{ $this->user->profile_photo_url }}" alt="{{ $this->user->name }}" class="h-20 w-20 rounded-full object-cover ring-2 ring-brand-gold/40">
                            </div>

                            <div x-show="photoPreview" style="display: none;">
                                <span class="block h-20 w-20 rounded-full bg-cover bg-center ring-2 ring-brand-gold/40"
                                      x-bind:style="'background-image: url(\'' + photoPreview + '\');'"></span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-wrap gap-3">
                        <button type="button"
                                x-on:click.prevent="$refs.photo.click()"
                                class="inline-flex items-center justify-center rounded-full border border-white/15 px-5 py-2.5 text-xs font-semibold tracking-[0.25em] uppercase text-brand-light transition hover:border-brand-gold/50 hover:text-brand-gold">
                            {{ __('Select A New Photo') }}
                        </button>

                        @if ($this->user->profile_photo_path)
                            <button type="button"
                                    wire:click="deleteProfilePhoto"
                                    class="inline-flex items-center justify-center rounded-full border border-red-400/30 px-5 py-2.5 text-xs font-semibold tracking-[0.25em] uppercase text-red-300 transition hover:border-red-300/60 hover:text-red-200">
                                {{ __('Remove Photo') }}
                            </button>
                        @endif
                    </div>

                    <x-input-error for="photo" class="mt-3 text-sm text-red-300" />
                </div>
            @endif

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label for="name" class="block text-xs font-semibold tracking-[0.25em] uppercase text-gray-400">{{ __('Name') }}</label>
                    <x-input id="name" type="text" wire:model="state.name" required autocomplete="name"
                             class="mt-3 block w-full rounded-2xl border border-white/10 bg-brand-dark/70 px-4 py-3 text-brand-light placeholder:text-gray-500 focus:border-brand-gold focus:ring-brand-gold" />
                    <x-input-error for="name" class="mt-3 text-sm text-red-300" />
                </div>

                <div>
                    <label for="email" class="block text-xs font-semibold tracking-[0.25em] uppercase text-gray-400">{{ __('Email') }}</label>
                    <x-input id="email" type="email" wire:model="state.email" required autocomplete="username"
                             class="mt-3 block w-full rounded-2xl border border-white/10 bg-brand-dark/70 px-4 py-3 text-brand-light placeholder:text-gray-500 focus:border-brand-gold focus:ring-brand-gold" />
                    <x-input-error for="email" class="mt-3 text-sm text-red-300" />

                    @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::emailVerification()) && ! $this->user->hasVerifiedEmail())
                        <div class="mt-4 rounded-2xl border border-amber-400/20 bg-amber-400/10 p-4 text-sm text-amber-100">
                            <p>{{ __('Your email address is unverified.') }}</p>
                            <button type="button"
                                    wire:click.prevent="sendEmailVerification"
                                    class="mt-3 inline-flex items-center text-xs font-semibold tracking-[0.25em] uppercase text-brand-gold transition hover:text-brand-light">
                                {{ __('Click here to re-send the verification email.') }}
                            </button>

                            @if ($this->verificationLinkSent)
                                <p class="mt-3 text-sm text-emerald-300">
                                    {{ __('A new verification link has been sent to your email address.') }}
                                </p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex flex-col gap-3 border-t border-white/10 pt-6 sm:flex-row sm:items-center sm:justify-between">
                <x-action-message on="saved" class="text-sm text-brand-gold">
                    {{ __('Saved.') }}
                </x-action-message>

                <button type="submit"
                        wire:loading.attr="disabled"
                        wire:target="photo"
                        class="inline-flex items-center justify-center rounded-full bg-brand-gold px-6 py-3 text-xs font-semibold tracking-[0.3em] uppercase text-brand-dark transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-60">
                    {{ __('Save') }}
                </button>
            </div>
        </form>
    </div>
</section>
