<?php

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

use function Livewire\Volt\state;

state([
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

    $validated = $this->validate([
        'first_name' => ['required', 'string', 'max:255'],
        'last_name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->user_id, 'user_id')],
        'fishpond_name' => ['nullable', 'string', 'max:255'],
        'barangay' => ['nullable', 'string', 'max:255'],
        'municipality' => ['nullable', 'string', 'max:255'],
        'province' => ['nullable', 'string', 'max:255'],
        'total_fishpond_area' => ['nullable', 'numeric', 'min:0'],
        'species_cultured_string' => ['nullable', 'string'],
        'water_type' => ['nullable', 'string', Rule::in(['Freshwater', 'Brackish', 'Saltwater'])],
    ]);

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

                {{-- Water Type --}}
                <div>
                    <x-input-label for="water_type" :value="__('Water Type')" />
                    <select wire:model="water_type" id="water_type" name="water_type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">Select Water Type...</option>
                        <option value="Freshwater">Freshwater</option>
                        <option value="Brackish">Brackish</option>
                        <option value="Saltwater">Saltwater</option>
                    </select>
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
