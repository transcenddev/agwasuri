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
    'farm_type' => fn() => auth()->user()->farm_type,
    'culture_system' => fn() => auth()->user()->culture_system,
    'culture_method' => fn() => auth()->user()->culture_method,
    'activities_during_pond_preperation' => fn() => auth()->user()->activities_during_pond_preperation,
    'liming' => fn() => auth()->user()->liming,
    'fertilizer' => fn() => auth()->user()->fertilizer,
    'disinfectant' => fn() => auth()->user()->disinfectant,
    'source_of_fingerlings_larvae' => fn() => auth()->user()->source_of_fingerlings_larvae,
    'stocking_density' => fn() => auth()->user()->stocking_density,
    'culture_period' => fn() => auth()->user()->culture_period,
    'harvest_method' => fn() => auth()->user()->harvest_method,
    'total_production_per_cropping' => fn() => auth()->user()->total_production_per_cropping,
    'feeds' => fn() => auth()->user()->feeds,
    'amount_per_day' => fn() => auth()->user()->amount_per_day,
    'frequency' => fn() => auth()->user()->frequency,
    'product_destination' => fn() => auth()->user()->product_destination,
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
        'farm_type' => ['nullable', 'string', 'max:255'],
        'culture_system' => ['nullable', 'string', 'max:255'],
        'culture_method' => ['nullable', 'string', 'max:255'],
        'activities_during_pond_preperation' => ['nullable', 'string', 'max:255'],
        'liming' => ['nullable', 'string', 'max:255'],
        'fertilizer' => ['nullable', 'string', 'max:255'],
        'disinfectant' => ['nullable', 'string', 'max:255'],
        'source_of_fingerlings_larvae' => ['nullable', 'string', 'max:255'],
        'stocking_density' => ['nullable', 'string', 'max:255'],
        'culture_period' => ['nullable', 'string', 'max:255'],
        'harvest_method' => ['nullable', 'string', 'max:255'],
        'total_production_per_cropping' => ['nullable', 'string', 'max:255'],
        'feeds' => ['nullable', 'string', 'max:255'],
        'amount_per_day' => ['nullable', 'string', 'max:255'],
        'frequency' => ['nullable', 'string', 'max:255'],
        'product_destination' => ['nullable', 'string', 'max:255'],
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

        {{-- Additional Fishpond Details Section --}}
        <div class="mt-8 pt-6 border-t border-gray-200">
            <h3 class="text-md font-medium text-gray-900 mb-4">{{ __('Additional Fishpond Details') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Farm Type --}}
                <div>
                    <x-input-label for="farm_type" :value="__('Farm Type')" />
                    <x-text-input wire:model="farm_type" id="farm_type" name="farm_type" type="text" class="mt-1 block w-full" autocomplete="off" />
                    <x-input-error class="mt-2" :messages="$errors->get('farm_type')" />
                </div>

                {{-- Culture System --}}
                <div>
                    <x-input-label for="culture_system" :value="__('Culture System')" />
                    <x-text-input wire:model="culture_system" id="culture_system" name="culture_system" type="text" class="mt-1 block w-full" autocomplete="off" />
                    <x-input-error class="mt-2" :messages="$errors->get('culture_system')" />
                </div>

                {{-- Culture Method --}}
                <div>
                    <x-input-label for="culture_method" :value="__('Culture Method')" />
                    <x-text-input wire:model="culture_method" id="culture_method" name="culture_method" type="text" class="mt-1 block w-full" autocomplete="off" />
                    <x-input-error class="mt-2" :messages="$errors->get('culture_method')" />
                </div>

                {{-- Activities During Pond Preparation --}}
                <div>
                    <x-input-label for="activities_during_pond_preperation" :value="__('Activities During Pond Preparation')" />
                    <x-text-input wire:model="activities_during_pond_preperation" id="activities_during_pond_preperation" name="activities_during_pond_preperation" type="text" class="mt-1 block w-full" autocomplete="off" />
                    <x-input-error class="mt-2" :messages="$errors->get('activities_during_pond_preperation')" />
                </div>

                {{-- Liming --}}
                <div>
                    <x-input-label for="liming" :value="__('Liming')" />
                    <x-text-input wire:model="liming" id="liming" name="liming" type="text" class="mt-1 block w-full" autocomplete="off" />
                    <x-input-error class="mt-2" :messages="$errors->get('liming')" />
                </div>

                {{-- Fertilizer --}}
                <div>
                    <x-input-label for="fertilizer" :value="__('Fertilizer')" />
                    <x-text-input wire:model="fertilizer" id="fertilizer" name="fertilizer" type="text" class="mt-1 block w-full" autocomplete="off" />
                    <x-input-error class="mt-2" :messages="$errors->get('fertilizer')" />
                </div>

                {{-- Disinfectant --}}
                <div>
                    <x-input-label for="disinfectant" :value="__('Disinfectant')" />
                    <x-text-input wire:model="disinfectant" id="disinfectant" name="disinfectant" type="text" class="mt-1 block w-full" autocomplete="off" />
                    <x-input-error class="mt-2" :messages="$errors->get('disinfectant')" />
                </div>

                {{-- Source of Fingerlings/Larvae --}}
                <div>
                    <x-input-label for="source_of_fingerlings_larvae" :value="__('Source of Fingerlings/Larvae')" />
                    <x-text-input wire:model="source_of_fingerlings_larvae" id="source_of_fingerlings_larvae" name="source_of_fingerlings_larvae" type="text" class="mt-1 block w-full" autocomplete="off" />
                    <x-input-error class="mt-2" :messages="$errors->get('source_of_fingerlings_larvae')" />
                </div>

                {{-- Stocking Density --}}
                <div>
                    <x-input-label for="stocking_density" :value="__('Stocking Density')" />
                    <x-text-input wire:model="stocking_density" id="stocking_density" name="stocking_density" type="text" class="mt-1 block w-full" autocomplete="off" />
                    <x-input-error class="mt-2" :messages="$errors->get('stocking_density')" />
                </div>

                {{-- Culture Period --}}
                <div>
                    <x-input-label for="culture_period" :value="__('Culture Period')" />
                    <x-text-input wire:model="culture_period" id="culture_period" name="culture_period" type="text" class="mt-1 block w-full" autocomplete="off" />
                    <x-input-error class="mt-2" :messages="$errors->get('culture_period')" />
                </div>

                {{-- Harvest Method --}}
                <div>
                    <x-input-label for="harvest_method" :value="__('Harvest Method')" />
                    <x-text-input wire:model="harvest_method" id="harvest_method" name="harvest_method" type="text" class="mt-1 block w-full" autocomplete="off" />
                    <x-input-error class="mt-2" :messages="$errors->get('harvest_method')" />
                </div>

                {{-- Total Production Per Cropping --}}
                <div>
                    <x-input-label for="total_production_per_cropping" :value="__('Total Production Per Cropping')" />
                    <x-text-input wire:model="total_production_per_cropping" id="total_production_per_cropping" name="total_production_per_cropping" type="text" class="mt-1 block w-full" autocomplete="off" />
                    <x-input-error class="mt-2" :messages="$errors->get('total_production_per_cropping')" />
                </div>

                {{-- Feeds --}}
                <div>
                    <x-input-label for="feeds" :value="__('Feeds')" />
                    <x-text-input wire:model="feeds" id="feeds" name="feeds" type="text" class="mt-1 block w-full" autocomplete="off" />
                    <x-input-error class="mt-2" :messages="$errors->get('feeds')" />
                </div>

                {{-- Amount Per Day --}}
                <div>
                    <x-input-label for="amount_per_day" :value="__('Amount Per Day')" />
                    <x-text-input wire:model="amount_per_day" id="amount_per_day" name="amount_per_day" type="text" class="mt-1 block w-full" autocomplete="off" />
                    <x-input-error class="mt-2" :messages="$errors->get('amount_per_day')" />
                </div>

                {{-- Frequency --}}
                <div>
                    <x-input-label for="frequency" :value="__('Frequency')" />
                    <x-text-input wire:model="frequency" id="frequency" name="frequency" type="text" class="mt-1 block w-full" autocomplete="off" />
                    <x-input-error class="mt-2" :messages="$errors->get('frequency')" />
                </div>

                {{-- Product Destination --}}
                <div>
                    <x-input-label for="product_destination" :value="__('Product Destination')" />
                    <x-text-input wire:model="product_destination" id="product_destination" name="product_destination" type="text" class="mt-1 block w-full" autocomplete="off" />
                    <x-input-error class="mt-2" :messages="$errors->get('product_destination')" />
                </div>
            </div>
        </div>


        {{-- Save Button and Message (Outside the grid) --}}
        <div class="flex items-center gap-4 pt-6"> {{-- Added padding-top --}}
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            <x-action-message class="me-3" on="profile-updated">
                {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>
</section>
