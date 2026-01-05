<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;
use Illuminate\Validation\Rule; // Import Rule facade

class UserForm extends Component
{
    // User properties
    public $userId = null;
    public $first_name = '';
    public $last_name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = ''; // For password confirmation on create
    public $account_type = 'user'; // Default account type
    public $fishpond_name = '';
    public $barangay = '';
    public $municipality = '';
    public $province = '';
    public $total_fishpond_area = null;
    public $species_cultured_string = ''; // Use a string for input
    public $water_type = '';
    public $farm_type = '';
    public $culture_system = '';
    public $culture_method = '';
    public $activities_during_pond_preperation = '';
    public $liming = '';
    public $fertilizer = '';
    public $disinfectant = '';
    public $source_of_fingerlings_larvae = '';
    public $stocking_density = '';
    public $culture_period = '';
    public $harvest_method = '';
    public $total_production_per_cropping = '';
    public $feeds = '';
    public $amount_per_day = '';
    public $frequency = '';
    public $product_destination = '';
    public $api_key = ''; // Changed from remember_token

    public $isEditing = false;

    // This makes the component re-render and re-evaluate rules when account_type changes
    // Necessary for requiredIf to work dynamically based on selection
    public function updatedAccountType($value)
    {
        // You could potentially reset user-specific fields here if needed
        // e.g., if switching from 'user' to 'admin', clear address fields?
        // $this->reset(['barangay', 'municipality', ...]);
    }

    protected function rules()
    {
        $isUserAccount = $this->account_type === 'user';

        // Base rules
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $this->userId . ',user_id', // Ignore current user on edit
            'account_type' => 'required|string|in:user,admin',

            // Conditional rules based on account_type
            'fishpond_name' => ['nullable', 'string', 'max:255'],
            'barangay' => [Rule::requiredIf($isUserAccount), 'nullable', 'string', 'max:255'],
            'municipality' => [Rule::requiredIf($isUserAccount), 'nullable', 'string', 'max:255'],
            'province' => [Rule::requiredIf($isUserAccount), 'nullable', 'string', 'max:255'],
            'total_fishpond_area' => [Rule::requiredIf($isUserAccount), 'nullable', 'numeric', 'min:0'],
            'species_cultured_string' => [Rule::requiredIf($isUserAccount), 'nullable', 'string'],
            'water_type' => [
                 Rule::requiredIf($isUserAccount), // Conditionally required
                 'nullable', // Still allow null if admin
                  Rule::in(['Freshwater', 'Brackish', 'Saltwater']),
            ],
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

            'api_key' => [
                'nullable',
                'string',
                'max:100',
                 Rule::unique('users', 'api_key')->ignore($this->userId, 'user_id'), // Check uniqueness, ignore self on edit
             ],
        ];

        // Password rules: required on create, optional on edit
        if ($this->isEditing) {
            $rules['password'] = 'nullable|min:8|confirmed'; // Optional on edit, requires confirmation if entered
        } else {
            $rules['password'] = 'required|min:8|confirmed'; // Required on create
        }

        return $rules;
    }

    public function mount($userId = null)
    {
        if ($userId) {
            $user = User::findOrFail($userId);
            $this->userId = $user->user_id;
            $this->first_name = $user->first_name;
            $this->last_name = $user->last_name;
            $this->email = $user->email;
            // Don't load password
            $this->account_type = $this->normalizeAccountType($user->account_type); // Load normalized account type
            $this->fishpond_name = $user->fishpond_name;
            $this->barangay = $user->barangay;
            $this->municipality = $user->municipality;
            $this->province = $user->province;
            $this->total_fishpond_area = $user->total_fishpond_area;
            $this->species_cultured_string = is_array($user->species_cultured) ? implode(', ', $user->species_cultured) : $user->species_cultured;
            $this->water_type = $user->water_type;
            $this->farm_type = $user->farm_type;
            $this->culture_system = $user->culture_system;
            $this->culture_method = $user->culture_method;
            $this->activities_during_pond_preperation = $user->activities_during_pond_preperation;
            $this->liming = $user->liming;
            $this->fertilizer = $user->fertilizer;
            $this->disinfectant = $user->disinfectant;
            $this->source_of_fingerlings_larvae = $user->source_of_fingerlings_larvae;
            $this->stocking_density = $user->stocking_density;
            $this->culture_period = $user->culture_period;
            $this->harvest_method = $user->harvest_method;
            $this->total_production_per_cropping = $user->total_production_per_cropping;
            $this->feeds = $user->feeds;
            $this->amount_per_day = $user->amount_per_day;
            $this->frequency = $user->frequency;
            $this->product_destination = $user->product_destination;
            $this->api_key = $user->api_key;
            $this->isEditing = true;
        } else {
            $this->account_type = 'user'; // Ensure default is set for create form
            // $this->generateApiKey();
        }
    }

    public function generateApiKey()
    {
        $this->api_key = Str::random(32);
    }

    public function save()
    {
        $validatedData = $this->validate();

        $normalizedAccountType = $this->normalizeAccountType($validatedData['account_type']);

        $speciesArray = !empty($validatedData['species_cultured_string'])
            ? array_map('trim', explode(',', $validatedData['species_cultured_string']))
            : [];

        // Prepare data, ensuring nulls are saved for admin if fields are empty
        $userData = [
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'email' => $validatedData['email'],
            'account_type' => $normalizedAccountType,
            'fishpond_name' => $validatedData['fishpond_name'] ?? null,
            'barangay' => $validatedData['barangay'] ?? null,
            'municipality' => $validatedData['municipality'] ?? null,
            'province' => $validatedData['province'] ?? null,
            'total_fishpond_area' => $validatedData['total_fishpond_area'] ?? null,
            'species_cultured' => $speciesArray,
            'water_type' => $validatedData['water_type'] ?? null,
            'farm_type' => $validatedData['farm_type'] ?? null,
            'culture_system' => $validatedData['culture_system'] ?? null,
            'culture_method' => $validatedData['culture_method'] ?? null,
            'activities_during_pond_preperation' => $validatedData['activities_during_pond_preperation'] ?? null,
            'liming' => $validatedData['liming'] ?? null,
            'fertilizer' => $validatedData['fertilizer'] ?? null,
            'disinfectant' => $validatedData['disinfectant'] ?? null,
            'source_of_fingerlings_larvae' => $validatedData['source_of_fingerlings_larvae'] ?? null,
            'stocking_density' => $validatedData['stocking_density'] ?? null,
            'culture_period' => $validatedData['culture_period'] ?? null,
            'harvest_method' => $validatedData['harvest_method'] ?? null,
            'total_production_per_cropping' => $validatedData['total_production_per_cropping'] ?? null,
            'feeds' => $validatedData['feeds'] ?? null,
            'amount_per_day' => $validatedData['amount_per_day'] ?? null,
            'frequency' => $validatedData['frequency'] ?? null,
            'product_destination' => $validatedData['product_destination'] ?? null,
            'api_key' => $validatedData['api_key'] ?? null,
        ];

        if (!empty($validatedData['password'])) {
            $userData['password'] = Hash::make($validatedData['password']);
        }

        if ($this->isEditing) {
            $user = User::findOrFail($this->userId);
            $user->update($userData);
            session()->flash('message', 'User updated successfully.');
        } else {
            User::create($userData);
            session()->flash('message', 'User created successfully.');
        }

        $this->dispatch('userSaved');
    }


    public function cancel()
    {
         $this->dispatch('closeForm');
    }

    private function normalizeAccountType($value): string
    {
        $normalized = strtolower(trim((string) $value));

        if ($normalized === 'admin' || str_starts_with($normalized, 'admin')) {
            return 'admin';
        }

        return 'user';
    }

    public function render()
    {
        return view('livewire.user-form');
    }
}
