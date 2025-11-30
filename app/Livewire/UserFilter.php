<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;

class UserFilter extends Component
{
    // Properties bound to the input fields for searching
    public string $fullName = '';
    public string $fishpondName = '';
    // Omni search input
    public string $omni = '';

    // Properties to hold the names of the *currently selected* user for the placeholder
    public ?string $selectedUserNameForPlaceholder = null;
    public ?string $selectedPondNameForPlaceholder = null;


    // Separate results and visibility flags
    public Collection $fullNameResults;
    public bool $showFullNameResults = false;
    public Collection $fishpondNameResults;
    public bool $showFishpondNameResults = false;
    // Merged omnisearch results (use Support collection for mixed array shape)
    public SupportCollection $omniResults;
    public bool $showOmniResults = false;

    // Selected user id exposed via query string for bookmarkable URLs
    public ?int $selectedUserId = null;

    protected $queryString = [
        // use `user` as the query param name for readability
        'selectedUserId' => ['as' => 'user', 'except' => ''],
    ];

    public function mount(): void
    {
        $this->fullNameResults = new Collection();
        $this->fishpondNameResults = new Collection();
        $this->omniResults = new SupportCollection();

        try {
            $this->selectedUserId = request('user') ?? session('selected_user');
        } catch (\Throwable $e) {
            $this->selectedUserId = session('selected_user');
        }

        // NEW: hydrate placeholders from selected user
        if ($this->selectedUserId) {
            if ($u = User::find($this->selectedUserId)) {
                $this->selectedUserNameForPlaceholder = trim($u->first_name . ' ' . $u->last_name);
                $this->selectedPondNameForPlaceholder = $u->fishpond_name;
            }
        }
    }

    // When user starts typing again, clear the selection placeholders
    public function updatedFullName(string $value): void
    {
        $this->selectedUserNameForPlaceholder = null; // Clear placeholder
        $this->selectedPondNameForPlaceholder = null; // Clear placeholder
        // clear omnisearch when typing into field
        $this->omni = '';
        $this->omniResults = new Collection();
        $this->showOmniResults = false;
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
        // clear omnisearch when typing into field
        $this->omni = '';
        $this->omniResults = new Collection();
        $this->showOmniResults = false;
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
        // DB-agnostic full-name concat
        try {
            $driver = DB::getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);
        } catch (\Throwable $e) {
            $driver = null;
        }
        $fullNameExpr = $driver === 'sqlite' ? "first_name || ' ' || last_name" : "CONCAT(first_name, ' ', last_name)";

        $query->where(function ($q) use ($fullNameExpr) {
            $q->where('first_name', 'like', '%' . $this->fullName . '%')
              ->orWhere('last_name', 'like', '%' . $this->fullName . '%')
              ->orWhereRaw("{$fullNameExpr} LIKE ?", ['%' . $this->fullName . '%']);
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
        $query = User::query()->select($primaryKeyName, 'first_name', 'last_name', 'fishpond_name');
        $query->where('fishpond_name', 'like', '%' . $this->fishpondName . '%');
        if (!empty($this->fullName)) {
            $query->where(function ($q) {
                $q->where('first_name', 'like', '%' . $this->fullName . '%')
                  ->orWhere('last_name', 'like', '%' . $this->fullName . '%')
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $this->fullName . '%']);
            });
        }
        // Get all matching rows, then collapse to unique fishpond names so the
        // dropdown shows each pond once (avoid multiple users with the same pond)
        $results = $query->get();
        $this->fishpondNameResults = $results
            ->filter(fn($u) => !empty($u->fishpond_name))
            ->unique(fn($u) => mb_strtolower(trim($u->fishpond_name)))
            ->values();

        $this->showFishpondNameResults = $this->fishpondNameResults->isNotEmpty();
    }

    /**
     * Updated omni search value handler. Performs two targeted queries (users and ponds),
     * merges results with type markers and exposes them to the view.
     */
    public function updatedOmni(string $value): void
    {
        $value = trim($value);
        // Clear other inputs when using omni to avoid conflicting dropdowns
        if (!empty($value)) {
            $this->fullName = '';
            $this->fishpondName = '';
        } else {
            $this->omniResults = new Collection();
            $this->showOmniResults = false;
            return;
        }

        $limitPerType = 6;

        // Users: prefer starts-with matches first
        $users = User::query()
            ->select('user_id', 'first_name', 'last_name', 'fishpond_name')
            ->where(function ($q) use ($value) {
                $q->where('first_name', 'like', $value . '%')
                  ->orWhere('last_name', 'like', $value . '%');
            })
            ->limit($limitPerType)
            ->get();

        // If not enough starts-with matches, add contains matches
        if ($users->count() < $limitPerType) {
            $needed = $limitPerType - $users->count();
            $more = User::query()
                ->select('user_id', 'first_name', 'last_name', 'fishpond_name')
                ->where(function ($q) use ($value) {
                    $q->where('first_name', 'like', '%' . $value . '%')
                      ->orWhere('last_name', 'like', '%' . $value . '%');
                })
                ->whereNotIn('user_id', $users->pluck('user_id'))
                ->limit($needed)
                ->get();
            $users = $users->concat($more);
        }

        // Ponds: search fishpond_name starts-with then contains
        $ponds = User::query()
            ->select('user_id', 'first_name', 'last_name', 'fishpond_name')
            ->where('fishpond_name', 'like', $value . '%')
            ->whereNotNull('fishpond_name')
            ->limit($limitPerType)
            ->get();

        if ($ponds->count() < $limitPerType) {
            $needed = $limitPerType - $ponds->count();
            $moreP = User::query()
                ->select('user_id', 'first_name', 'last_name', 'fishpond_name')
                ->where('fishpond_name', 'like', '%' . $value . '%')
                ->whereNotNull('fishpond_name')
                ->whereNotIn('user_id', $ponds->pluck('user_id'))
                ->limit($needed)
                ->get();
            $ponds = $ponds->concat($moreP);
        }

        // Map to unified shape
        $mappedUsers = $users->map(fn($u) => [
            'type' => 'user',
            'id' => $u->user_id,
            'label' => trim($u->first_name . ' ' . $u->last_name),
            'meta' => $u->fishpond_name,
        ]);

        $mappedPonds = $ponds->filter(fn($u) => !empty($u->fishpond_name))
            ->unique(fn($u) => mb_strtolower(trim($u->fishpond_name)))
            ->map(fn($u) => [
                'type' => 'pond',
                'id' => $u->user_id,
                'label' => $u->fishpond_name,
                'meta' => trim($u->first_name . ' ' . $u->last_name),
            ]);

        $merged = $mappedUsers->concat($mappedPonds)->take(10);

        $this->omniResults = collect($merged);
        $this->showOmniResults = $this->omniResults->isNotEmpty();
    }


    // User selection updates placeholders and clears input values
    public function selectUser($userId): void
    {
        // Persist selection so other pages/components can read it
        try { session(['selected_user' => $userId]); } catch (\Throwable $e) {}

        // Update query-string bound property so URL is bookmarkable
        $this->selectedUserId = (int) $userId;

        // Dispatch event for same-page Livewire consumers
        $this->dispatch('user-selected', userId: $userId);

        // Update the placeholder properties if we can resolve the user
        try {
            $u = User::find($userId);
            if ($u) {
                $this->selectedUserNameForPlaceholder = trim($u->first_name . ' ' . $u->last_name);
                $this->selectedPondNameForPlaceholder = $u->fishpond_name;
            }
        } catch (\Throwable $e) {}

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
