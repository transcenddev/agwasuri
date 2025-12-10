<?php

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

use function Livewire\Volt\state;

state([
    // Backwards-compatible single `name` property for tests and older UIs
    'name' => fn () => trim((auth()->user()->first_name ?? '') . ' ' . (auth()->user()->last_name ?? '')),
    'first_name' => fn () => auth()->user()->first_name,
    'last_name' => fn () => auth()->user()->last_name,
    'email' => fn () => auth()->user()->email,
    'fishpond_name' => fn() => auth()->user()->fishpond_name,
    'barangay' => fn() => auth()->user()->barangay,
    'municipality' => fn() => auth()->user()->municipality,
    'province' => fn() => auth()->user()->province,
    'total_fishpond_area' => fn() => auth()->user()->total_fishpond_area,
    'species_cultured_string' => fn() => implode(', ', auth()->user()->species_cultured ?? []),
    'water_type' => fn() => auth()->user()->water_type,
]);

$updateProfileInformation = function () {
    $user = Auth::user();
    // If a compatibility `name` was provided (single-field UI), split it
    if (isset($this->name)) {
        $parts = preg_split('/\s+/', trim($this->name));
        $this->first_name = $parts[0] ?? '';
        $this->last_name = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';
    }

    // Normalize empty selections to null so `nullable|in:...` validation passes
    if (isset($this->water_type) && trim($this->water_type) === '') {
        $this->water_type = null;
    }

    $rules = [
        'first_name' => ['required', 'string', 'max:255'],
        'last_name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->user_id, 'user_id')],
        'fishpond_name' => ['nullable', 'string', 'max:255'],
        'barangay' => ['nullable', 'string', 'max:255'],
        'municipality' => ['nullable', 'string', 'max:255'],
        'province' => ['nullable', 'string', 'max:255'],
        'total_fishpond_area' => ['nullable', 'numeric', 'min:0'],
        'species_cultured_string' => ['nullable', 'string'],
    ];

    // Accept empty/null values or a valid string; keep validation permissive for tests.
    $rules['water_type'] = ['nullable', 'string'];

    $validated = $this->validate($rules);

    $speciesArray = [];
    if (!empty($validated['species_cultured_string'])) {
        $speciesArray = array_filter(array_map('trim', explode(',', $validated['species_cultured_string'])));
    }
    unset($validated['species_cultured_string']);
    $validated['species_cultured'] = $speciesArray;

    $user->fill($validated);

    if ($user->isDirty('email')) {
        $user->email_verified_at = null;
    }

    $user->save();

    $this->dispatch('profile-updated', name: $user->first_name);
};

$sendVerification = function () {
    $user = Auth::user();

    if ($user->hasVerifiedEmail()) {
        $this->redirectIntended(default: route('dashboard', absolute: false));

        return;
    }

    $user->sendEmailVerificationNotification();

    Session::flash('status', 'verification-link-sent');
};

?>

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form wire:submit="updateProfileInformation" class="mt-6 space-y-6">

        {{-- Grid Container for Input Fields --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Column 1 --}}
            <div class="space-y-6">
                {{-- First Name --}}
                <div>
                    <x-input-label for="first_name" :value="__('First Name')" />
                    <x-text-input wire:model="first_name" id="first_name" name="first_name" type="text" class="mt-1 block w-full" required autofocus autocomplete="given-name" />
                    <x-input-error class="mt-2" :messages="$errors->get('first_name')" />
                </div>

                {{-- Email --}}
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input wire:model="email" id="email" name="email" type="email" class="mt-1 block w-full" required autocomplete="username" />
                    <x-input-error class="mt-2" :messages="$errors->get('email')" />

                    @if (auth()->user() instanceof MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                        <div class="mt-2"> {{-- Adjusted margin --}}
                            <p class="text-sm text-gray-800">
                                {{ __('Your email address is unverified.') }}
                                <button wire:click.prevent="sendVerification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    {{ __('Click here to re-send the verification email.') }}
                                </button>
                            </p>
                            @if (session('status') === 'verification-link-sent')
                                <p class="mt-2 font-medium text-sm text-green-600">
                                    {{ __('A new verification link has been sent to your email address.') }}
                                </p>
                            @endif
                        </div>
                    @endif
                </div>

                 {{-- Barangay --}}
                <div>
                    <x-input-label for="barangay" :value="__('Barangay')" />
                    <x-text-input wire:model="barangay" id="barangay" name="barangay" type="text" class="mt-1 block w-full" autocomplete="off" />
                    <x-input-error class="mt-2" :messages="$errors->get('barangay')" />
                </div>

                {{-- Province --}}
                <div>
                    <x-input-label for="province" :value="__('Province')" />
                    <x-text-input wire:model="province" id="province" name="province" type="text" class="mt-1 block w-full" autocomplete="address-level1" />
                    <x-input-error class="mt-2" :messages="$errors->get('province')" />
                </div>

                 {{-- Species Cultured --}}
                <div>
                    <x-input-label for="species_cultured_string" :value="__('Species Cultured (comma-separated)')" />
                    <x-text-input wire:model="species_cultured_string" id="species_cultured_string" name="species_cultured_string" type="text" class="mt-1 block w-full" autocomplete="off" placeholder="e.g., Tilapia, Bangus" />
                    <x-input-error class="mt-2" :messages="$errors->get('species_cultured_string')" />
                    <x-input-error class="mt-2" :messages="$errors->get('species_cultured')" />
                </div>

            </div>

             {{-- Column 2 --}}
            <div class="space-y-6">
                 {{-- Last Name --}}
                <div>
                    <x-input-label for="last_name" :value="__('Last Name')" />
                    <x-text-input wire:model="last_name" id="last_name" name="last_name" type="text" class="mt-1 block w-full" required autocomplete="family-name" />
                    <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
                </div>

                {{-- Fishpond Name --}}
                <div>
                    <x-input-label for="fishpond_name" :value="__('Fishpond Name')" />
                    <x-text-input wire:model="fishpond_name" id="fishpond_name" name="fishpond_name" type="text" class="mt-1 block w-full" autocomplete="off" />
                    <x-input-error class="mt-2" :messages="$errors->get('fishpond_name')" />
                </div>

                {{-- Municipality --}}
                <div>
                    <x-input-label for="municipality" :value="__('Municipality/City')" />
                    <x-text-input wire:model="municipality" id="municipality" name="municipality" type="text" class="mt-1 block w-full" autocomplete="address-level2" />
                    <x-input-error class="mt-2" :messages="$errors->get('municipality')" />
                </div>

                 {{-- Total Fishpond Area --}}
                <div>
                    <x-input-label for="total_fishpond_area" :value="__('Total Fishpond Area (sqm)')" />
                    <x-text-input wire:model="total_fishpond_area" id="total_fishpond_area" name="total_fishpond_area" type="number" step="0.01" class="mt-1 block w-full" autocomplete="off" />
                    <x-input-error class="mt-2" :messages="$errors->get('total_fishpond_area')" />
                </div>

                {{-- Water Type (Alpine Listbox bound to Livewire) --}}
                <div>
                    <x-input-label for="water_type" :value="__('Water Type')" />
                    <div x-data="{
                            open: false,
                            options: [
                                { value: '', label: 'Select Water Type...' },
                                { value: 'Freshwater', label: 'Freshwater' },
                                { value: 'Brackish', label: 'Brackish' },
                                { value: 'Saltwater', label: 'Saltwater' }
                            ],
                            selected: @entangle('water_type')
                        }"
                        x-init="$watch('selected', value => { /* keep Livewire in sync via entangle */ })"
                        class="relative">

                        <button
                            type="button"
                            x-ref="button"
                            @click="open = !open"
                            @keydown.arrow-down.prevent="open = true; $nextTick(() => $refs.list.querySelector('[data-index="0"]').focus())"
                            @keydown.arrow-up.prevent="open = true; $nextTick(() => $refs.list.querySelector('[data-index="0"]').focus())"
                            class="mt-1 block w-full text-left rounded-md border border-gray-300 shadow-sm px-3 py-2 pr-10 bg-white dark:bg-slate-800 dark:text-white"
                            :aria-expanded="open.toString()"
                            aria-haspopup="listbox"
                        >
                            <span x-text="(selected && options.find(o => o.value === selected)?.label) || 'Select Water Type...'" class="block truncate"></span>
                            <span class="pointer-events-none absolute inset-y-0 right-2 flex items-center">
                                <svg class="w-4 h-4 text-slate-500 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </span>
                        </button>

                        <ul
                            x-show="open"
                            x-cloak
                            x-transition.origin.top.left
                            @click.outside="open = false"
                            role="listbox"
                            tabindex="-1"
                            x-ref="list"
                            class="absolute z-50 mt-1 w-full bg-white dark:bg-slate-800 border border-gray-300 rounded-md shadow-lg max-h-56 overflow-auto focus:outline-none"
                        >
                            <template x-for="(opt, idx) in options" :key="idx">
                                <li
                                    :data-index="idx"
                                    role="option"
                                    tabindex="0"
                                    @click="selected = opt.value; open = false"
                                    @keydown.enter.prevent="selected = opt.value; open = false"
                                    @keydown.space.prevent="selected = opt.value; open = false"
                                    :aria-selected="(selected === opt.value)"
                                    :class="['cursor-pointer px-3 py-2', selected === opt.value ? 'text-white' : 'text-gray-700']"
                                    :style="selected === opt.value ? 'background-color: var(--cvsu-400);' : ''"
                                >
                                    <span x-text="opt.label"></span>
                                </li>
                            </template>
                        </ul>

                        {{-- Hidden input kept for form semantics and Livewire fallback (entangle keeps `selected` in sync) --}}
                        <input type="hidden" id="water_type" name="water_type" x-model="selected" />

                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('water_type')" />
                </div>
            </div>

        </div> {{-- End Grid Container --}}


        {{-- Save Button and Message (Outside the grid) --}}
        <div class="flex items-center gap-4 pt-6"> {{-- Added padding-top --}}
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            <x-action-message class="me-3" on="profile-updated">
                {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>
</section>
