<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Illuminate\Database\Eloquent\Collection;

class UserFilter extends Component
{
    // Properties bound to the input fields for searching
    public string $fullName = '';
    public string $fishpondName = '';

    // Properties to hold the names of the *currently selected* user for the placeholder
    public ?string $selectedUserNameForPlaceholder = null;
    public ?string $selectedPondNameForPlaceholder = null;


    // Separate results and visibility flags
    public Collection $fullNameResults;
    public bool $showFullNameResults = false;
    public Collection $fishpondNameResults;
    public bool $showFishpondNameResults = false;

    public function mount()
    {
        $this->fullNameResults = new Collection();
        $this->fishpondNameResults = new Collection();
        // Optionally, load the initial user's name/pond into placeholder on mount if needed
        // $initialUser = User::find(Auth::id());
        // if ($initialUser) {
        //     $this->selectedUserNameForPlaceholder = $initialUser->first_name . ' ' . $initialUser->last_name;
        //     $this->selectedPondNameForPlaceholder = $initialUser->fishpond_name;
        // }
    }

    // When user starts typing again, clear the selection placeholders
    public function updatedFullName(string $value): void
    {
        $this->selectedUserNameForPlaceholder = null; // Clear placeholder
        $this->selectedPondNameForPlaceholder = null; // Clear placeholder
        if (empty($value)) {
            $this->resetFullNameSearch();
             // If clearing input should reset view to logged-in user
             // $this->dispatch('user-selected', userId: Auth::id());
        } else {
            $this->searchFullName();
        }
    }

    // When user starts typing again, clear the selection placeholders
    public function updatedFishpondName(string $value): void
    {
        $this->selectedUserNameForPlaceholder = null; // Clear placeholder
        $this->selectedPondNameForPlaceholder = null; // Clear placeholder
        if (empty($value)) {
            $this->resetFishpondNameSearch();
             // If clearing input should reset view to logged-in user
             // $this->dispatch('user-selected', userId: Auth::id());
        } else {
            $this->searchFishpondName();
        }
    }

    // Separate search logic for Full Name
    public function searchFullName(): void
    {
        if(empty($this->fullName)) {
             $this->resetFullNameSearch();
             return;
        }
        $userModel = new User();
        $primaryKeyName = $userModel->getKeyName();
        $query = User::query()->select($primaryKeyName, 'first_name', 'last_name', 'fishpond_name');
        // Build a cross-DB concat expression (SQLite uses ||, MySQL uses CONCAT)
        $driver = null;
        try {
            $driver = DB::getPdo() ? DB::getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME) : null;
        } catch (\Throwable $e) {
            $driver = null;
        }

        $concatExpr = ($driver === 'sqlite') ? "first_name || ' ' || last_name" : "CONCAT(first_name, ' ', last_name)";

        $query->where(function ($q) use ($concatExpr) {
            $q->where('first_name', 'like', '%' . $this->fullName . '%')
              ->orWhere('last_name', 'like', '%' . $this->fullName . '%')
              ->orWhereRaw("{$concatExpr} LIKE ?", ['%' . $this->fullName . '%']);
        });
        if (!empty($this->fishpondName)) {
             $query->where('fishpond_name', 'like', '%' . $this->fishpondName . '%');
        }
        $this->fullNameResults = $query->limit(5)->get();
        $this->showFullNameResults = $this->fullNameResults->isNotEmpty();
    }

    // Separate search logic for Fishpond Name
    public function searchFishpondName(): void
    {
        if(empty($this->fishpondName)) {
             $this->resetFishpondNameSearch();
             return;
        }
        $userModel = new User();
        $primaryKeyName = $userModel->getKeyName();
        // When searching fishpond names we want distinct pond names (grouped)
        // so the dropdown presents each matching pond once.
        $query = User::query()
            ->selectRaw("MIN({$primaryKeyName}) as {$primaryKeyName}, MIN(first_name) as first_name, MIN(last_name) as last_name, fishpond_name")
            ->where('fishpond_name', 'like', '%' . $this->fishpondName . '%')
            ->groupBy('fishpond_name');
        if (!empty($this->fullName)) {
            // Respect the same DB-driver aware concat expression used above
            $driver = null;
            try {
                $driver = DB::getPdo() ? DB::getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME) : null;
            } catch (\Throwable $e) {
                $driver = null;
            }
            $concatExpr = ($driver === 'sqlite') ? "first_name || ' ' || last_name" : "CONCAT(first_name, ' ', last_name)";

            $query->where(function ($q) use ($concatExpr) {
                $q->where('first_name', 'like', '%' . $this->fullName . '%')
                  ->orWhere('last_name', 'like', '%' . $this->fullName . '%')
                  ->orWhereRaw("{$concatExpr} LIKE ?", ['%' . $this->fullName . '%']);
            });
        }
        $this->fishpondNameResults = $query->limit(10)->get();
        $this->showFishpondNameResults = $this->fishpondNameResults->isNotEmpty();
    }


    // User selection updates placeholders and clears input values
    public function selectUser($userId, $selectedFullName, $selectedFishpondName): void
    {
        $this->dispatch('user-selected', userId: $userId);

        // Update the placeholder properties
        $this->selectedUserNameForPlaceholder = $selectedFullName;
        $this->selectedPondNameForPlaceholder = $selectedFishpondName;

        // Clear the actual input values
        $this->fullName = '';
        $this->fishpondName = '';

        // Hide the dropdowns (don't reset input value again)
        $this->resetFullNameSearch(false);
        $this->resetFishpondNameSearch(false);
    }

    // Reset Full Name search results
    public function resetFullNameSearch(bool $resetInputValue = true): void
    {
        $this->fullNameResults = new Collection();
        $this->showFullNameResults = false;
        if ($resetInputValue) {
            $this->fullName = '';
            // Also clear placeholder if input is fully reset
            $this->selectedUserNameForPlaceholder = null;
            $this->selectedPondNameForPlaceholder = null;
        }
    }

    // Reset Fishpond Name search results
    public function resetFishpondNameSearch(bool $resetInputValue = true): void
    {
        $this->fishpondNameResults = new Collection();
        $this->showFishpondNameResults = false;
        if ($resetInputValue) {
            $this->fishpondName = '';
             // Also clear placeholder if input is fully reset
             $this->selectedUserNameForPlaceholder = null;
             $this->selectedPondNameForPlaceholder = null;
        }
    }

    public function render()
    {
        return view('livewire.user-filter');
    }
}
