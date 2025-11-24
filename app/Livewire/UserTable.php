<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination; // Make sure this is used

class UserTable extends Component
{
    use WithPagination; // Use pagination features

    public $showForm = false;
    public $editingUserId = null;

    public $showDeleteModal = false;
    public $deletingUserId = null;

    public $showFilters = false;

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
        'echo-private:admin.users,UserProfileUpdated' => 'handleUserProfileUpdated'
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
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->deletingUserId = null;
        $this->showDeleteModal = false;
    }

    public function destroyUser()
    {
        if ($this->deletingUserId) {
            $user = User::find($this->deletingUserId);
            if ($user) {
                $user->delete();
                session()->flash('message', 'User deleted successfully.'); // Changed to message for consistency
                $this->resetPage();
            } else {
                 session()->flash('error', 'User not found.');
            }
        } else {
            session()->flash('error', 'Could not determine user to delete.');
        }
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