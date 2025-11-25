<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination; // Make sure this is used

class UserTable extends Component
{
    use WithPagination; // Use pagination features

    public $showForm = false;
    public $editingUserId = null;

    public $showDeleteModal = false;
    public $deletingUserId = null;
    public $deletingUserName = null; // full name for confirmation UI
    public $deleteConfirmName = null; // input typed by admin to confirm

    public $showFilters = false;

    // Selected user for details modal
    public $selectedUser = null;
    // Filter properties
    public $filterName = '';
    public $filterEmail = '';
    public $filterAccountType = '';
    public $filterBarangay = '';
    public $filterMunicipality = '';
    public $filterProvince = '';
    public $filterWaterType = '';
    public $filterSpecies = '';
    public $filterFishpondName = ''; // Added: Filter for Fishpond Name

    protected $listeners = [
        'userSaved' => 'handleUserSaved',
        'closeForm' => 'closeForm',
        // Livewire Echo listener for admin broadcasts
        'echo-private:admin.users,UserProfileUpdated' => 'handleUserProfileUpdated',
        // events from teleported menus
        'menuAction' => 'handleMenuAction'
    ];

    // Reset pagination when any filter is updated
    public function updated($propertyName)
    {
        if (in_array($propertyName, [
            'filterName', 'filterEmail', 'filterAccountType',
            'filterBarangay', 'filterMunicipality', 'filterProvince', 'filterWaterType',
            'filterSpecies', 'filterFishpondName' // Added fishpond name
        ])) {
            $this->resetPage();
        }
    }

    public function mount()
    {
        // Optional: Initialize filters from query string if needed
    }

    public function handleUserSaved()
    {
        $this->closeForm();
        session()->flash('message', 'User saved successfully.');
        $this->resetPage();
    }

    public function handleUserProfileUpdated($payload)
    {
        // Payload: ['user_id' => ..., 'water_type' => ..., 'changes' => [...]]
        // Trigger a re-render so updated data is fetched in render().
        // If desired, we could update an in-memory row instead.
        $this->resetPage();
        // Dispatch a browser event so admin clients can log and debug incoming payloads
        $this->dispatchBrowserEvent('user-profile-updated', $payload);
    }

    // Show details modal for a specific user
    public function showDetails($userId)
    {
        $this->selectedUser = User::find($userId);
    }

    // Close details modal
    public function closeDetails()
    {
        $this->selectedUser = null;
    }

    public function closeForm()
    {
        $this->showForm = false;
        $this->editingUserId = null;
        $this->resetValidation();
    }

    public function createUser()
    {
        $this->editingUserId = null;
        $this->showForm = true;
    }

    public function editUser($userId)
    {
        $this->editingUserId = $userId;
        $this->showForm = true;
    }

    // --- Filter Logic ---

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    // Added: Reset all filter properties
    public function resetFilters()
    {
        $this->reset([
            'filterName', 'filterEmail', 'filterAccountType',
            'filterBarangay', 'filterMunicipality', 'filterProvince', 'filterWaterType',
            'filterSpecies', 'filterFishpondName' // Added fishpond name
        ]);
        $this->resetPage(); // Reset pagination as well
    }

    // --- End Filter Logic ---

    // --- Delete Logic ---

    public function confirmDelete($userId)
    {
        $this->deletingUserId = $userId;
        $user = User::find($userId);
        $this->deletingUserName = $user ? ($user->first_name . ' ' . $user->last_name) : null;
        $this->deleteConfirmName = null;
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->deletingUserId = null;
        $this->deletingUserName = null;
        $this->deleteConfirmName = null;
        $this->showDeleteModal = false;
    }

    // Handle actions emitted by teleported action menus
    public function handleMenuAction($action, $userId)
    {
        if ($action === 'edit') {
            $this->editUser($userId);
            return;
        }

        if ($action === 'delete') {
            $this->confirmDelete($userId);
            return;
        }
    }

    public function destroyUser()
    {
        if (! $this->deletingUserId) {
            session()->flash('error', 'Could not determine user to delete.');
            return;
        }

        $user = User::find($this->deletingUserId);
        if (! $user) {
            session()->flash('error', 'User not found.');
            $this->cancelDelete();
            return;
        }

        // Disallow deleting currently authenticated admin (safety)
        $current = Auth::user();
        if ($current && $current->user_id === $user->user_id) {
            session()->flash('error', 'You cannot delete the account you are currently signed in with.');
            $this->cancelDelete();
            return;
        }

        // Require type-to-confirm matching full name
        $expected = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
        // Normalize whitespace and compare case-insensitively to be more forgiving
        $normalize = function ($s) {
            $s = trim((string) $s);
            // replace consecutive whitespace (including NBSP) with single space
            $s = preg_replace('/[\s\x{00A0}]+/u', ' ', $s);
            return mb_strtolower($s);
        };

        $given = $normalize($this->deleteConfirmName ?? '');
        $expectNorm = $normalize($expected);

        if ($given !== $expectNorm) {
            // Log for diagnostics (no sensitive info beyond names)
            Log::info('Delete confirmation mismatch', ['user_id' => $this->deletingUserId, 'given' => $this->deleteConfirmName, 'expected' => $expected]);
            session()->flash('error', 'Confirmation name does not match. Type the full name to confirm deletion.');
            return;
        }

        // All good — delete
        $user->delete();
        session()->flash('message', 'User deleted successfully.');
        $this->resetPage();
        $this->cancelDelete();
    }

    // --- End Delete Logic ---

    public function render()
    {
        // Base query
        $query = User::query();

        // Apply filters
        if (!empty($this->filterName)) {
            $query->where(function($q) {
                $q->where('first_name', 'like', '%'.$this->filterName.'%')
                  ->orWhere('last_name', 'like', '%'.$this->filterName.'%');
            });
        }
        if (!empty($this->filterEmail)) {
            $query->where('email', 'like', '%' . $this->filterEmail . '%');
        }
        if (!empty($this->filterAccountType)) {
            $query->where('account_type', $this->filterAccountType);
        }
         if (!empty($this->filterBarangay)) {
            $query->where('barangay', 'like', '%' . $this->filterBarangay . '%');
        }
        if (!empty($this->filterMunicipality)) {
            $query->where('municipality', 'like', '%' . $this->filterMunicipality . '%');
        }
        if (!empty($this->filterProvince)) {
            $query->where('province', 'like', '%' . $this->filterProvince . '%');
        }
         if (!empty($this->filterWaterType)) {
            $query->where('water_type', $this->filterWaterType);
        }
        if (!empty($this->filterSpecies)) {
            $query->where('species_cultured', 'like', '%' . $this->filterSpecies . '%');
        }
        // Added: Filter for Fishpond Name
        if (!empty($this->filterFishpondName)) {
            $query->where('fishpond_name', 'like', '%' . $this->filterFishpondName . '%');
        }


        return view('livewire.user-table', [
            'users' => $query->paginate(10) // Fetch paginated and filtered users
        ]);
    }
}