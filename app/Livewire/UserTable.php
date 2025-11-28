<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Livewire\Component;
use Livewire\WithPagination; // Make sure this is used
use PDOException;

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
        'menuAction' => 'handleMenuAction',
        // allow other code to request a component refresh
        'refreshUsers' => '$refresh',
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
        try {
            // Diagnostic: record the value Livewire sees on entry to this method
            Log::info('destroyUser entry', ['deleting' => $this->deletingUserId, 'deleteConfirmName' => $this->deleteConfirmName]);

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
            try {
                $user->delete();
            } catch (QueryException $qe) {
                // Likely a foreign-key constraint or related-data issue. Log and show friendly message.
                Log::warning('user delete failed - query exception', ['user_id' => $this->deletingUserId, 'error' => $qe->getMessage()]);
                session()->flash('error', 'Cannot delete user: related records exist. Remove or reassign related data first.');
                return;
            } catch (PDOException $pe) {
                // Database-level connection issues (lost connection, refused, etc.)
                Log::warning('user delete failed - PDO exception', ['user_id' => $this->deletingUserId, 'error' => $pe->getMessage()]);
                session()->flash('error', 'Database unavailable — please try again later.');
                return;
            }
            session()->flash('message', 'User deleted successfully.');
            // Clear any prior error flash so only the success message is shown
            session()->forget('error');
            $this->resetPage();
            $this->cancelDelete();
            // Ensure the component refreshes immediately for the current client
            // Emit a refresh event to listeners (emitSelf not available here)
            $this->emit('refreshUsers');
        } catch (\Throwable $e) {
            // Log full exception so the 500 from Livewire can be diagnosed
            Log::error('destroyUser exception', [
                'deleting' => $this->deletingUserId,
                'deleteConfirmName' => $this->deleteConfirmName,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('error', 'Server error while deleting user. Check logs for details.');
        }
    }

    /**
     * Called from client to set typed confirmation and run destroy in one step.
     * This ensures the client-supplied typed value is present on the server
     * before the server-side check runs.
     */
    public function clientDestroyUser($userId, $typed)
    {
        // Quick DB connectivity check so we can surface a friendly message instead
        // of letting Livewire bubble a hard 500 when the DB isn't reachable.
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            Log::warning('clientDestroyUser - DB connection check failed', [
                'user_id_arg' => $userId,
                'typed_received' => $typed,
                'deletingUserId' => $this->deletingUserId,
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', 'Database unavailable — please try again later.');
            return;
        }

        // Log the typed value received from client for diagnostics
        Log::info('clientDestroyUser invoked', ['user_id_arg' => $userId, 'typed_received' => $typed, 'deletingUserId' => $this->deletingUserId]);

        // Ensure server-side state matches the explicit client request to avoid race conditions
        $this->deletingUserId = $userId;
        $this->deleteConfirmName = $typed;

        try {
            $this->destroyUser();
        } catch (\Throwable $e) {
            // Already logged inside destroyUser; surface a friendly message to the UI
            Log::error('clientDestroyUser exception', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            session()->flash('error', 'Server error while attempting deletion. Check logs for details.');
            return;
        }
    }

    /**
     * Simpler client flow for the modal that binds the typed input with wire:model.defer.
     * Called from Blade: wire:click="clientDestroyUserFromUI({{ $deletingUserId }})"
     */
    public function clientDestroyUserFromUI($userId)
    {
        // Quick DB connectivity check so we can surface a friendly message instead
        // of letting Livewire bubble a hard 500 when the DB isn't reachable.
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            Log::warning('clientDestroyUserFromUI - DB connection check failed', [
                'user_id_arg' => $userId,
                'deleteConfirmName' => $this->deleteConfirmName,
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', 'Database unavailable — please try again later.');
            return;
        }

        // Ensure server-side state matches the explicit client request to avoid race conditions
        $this->deletingUserId = $userId;
        // $this->deleteConfirmName is already populated by wire:model.defer from the Blade input

        try {
            $this->destroyUser();
        } catch (\Throwable $e) {
            Log::error('clientDestroyUserFromUI exception', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            session()->flash('error', 'Server error while attempting deletion. Check logs for details.');
            return;
        }
    }

    /**
     * Simple confirm-based delete invoked directly from Blade buttons.
     * This method performs a direct delete (no typed-name confirmation)
     * and dispatches a `user-deleted` event for front-end listeners.
     */
    public function deleteUser($userId)
    {
        try {
            $user = User::find($userId);
            if (! $user) {
                session()->flash('error', 'User not found.');
                return;
            }

            // Prevent deleting the currently authenticated admin
            $current = Auth::user();
            if ($current && $current->user_id === $user->user_id) {
                session()->flash('error', 'You cannot delete the account you are currently signed in with.');
                return;
            }

            try {
                $user->delete();
            } catch (QueryException $qe) {
                Log::warning('deleteUser - query exception', ['user_id' => $userId, 'error' => $qe->getMessage()]);
                session()->flash('error', 'Cannot delete user: related records exist.');
                return;
            } catch (PDOException $pe) {
                Log::warning('deleteUser - PDO exception', ['user_id' => $userId, 'error' => $pe->getMessage()]);
                session()->flash('error', 'Database unavailable — please try again later.');
                return;
            }

            session()->flash('message', 'User deleted successfully.');
            // Clear any prior error flash so only the success message is shown
            session()->forget('error');
            $this->resetPage();

            // Notify JS listeners and other Livewire components
            $this->emit('user-deleted', $userId);
            $this->dispatchBrowserEvent('user-deleted', ['user_id' => $userId]);
        } catch (\Throwable $e) {
            Log::error('deleteUser exception', ['user_id' => $userId, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            session()->flash('error', 'Server error while deleting user. Check logs for details.');
        }
    }

    // --- End Delete Logic ---

    public function render()
    {
        // Base query
        $query = User::query();

        // Apply filters
        if (!empty($this->filterName)) {
            $value = $this->filterName;
            $query->where(function($q) use ($value) {
                $q->where('first_name', 'like', '%'.$value.'%')
                  ->orWhere('last_name', 'like', '%'.$value.'%')
                  ->orWhere('email', 'like', '%'.$value.'%')
                  ->orWhere('fishpond_name', 'like', '%'.$value.'%')
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%'.$value.'%']);
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