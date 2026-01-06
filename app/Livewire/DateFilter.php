<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\WaterQualityData;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Use Log for reporting potential issues
use Livewire\Attributes\On;
use Livewire\Component;

class DateFilter extends Component
{
    public ?string $startDate = null;
    public ?string $endDate = null;

    public ?string $startTime = null; // HH:MM (24h)
    public ?string $endTime = null;   // HH:MM (24h)

    public ?string $minAllowedDate = null;
    public ?string $maxAllowedDate = null;

    public ?int $currentUserId = null;
    public bool $isAdminView = false;
    public bool $showFilter = true; // Default to true

    #[On('user-selected')]
    public function updateUser($userId)
    {
        // When a user is selected (primarily for admin), update the target user
        // Non-admins technically don't *need* this, but it doesn't hurt.
        // For admins, this is the signal to fetch data and show the filter.

        // Ensure userId is treated as an integer
        $userId = (int) $userId;
        if ($userId <= 0) {
             Log::warning('DateFilter: Invalid userId received in updateUser.', ['userId' => $userId]);
             // If admin view, potentially reset state or show message?
             // If we hide based on currentUserId in view, this case might need UI feedback
             return;
        }

        $this->currentUserId = $userId;

        // Fetch the date range for the now-selected user
        $this->fetchDateRangeForUser($this->currentUserId);

        // IMPORTANT: Do NOT automatically set startDate and endDate to min/max.
        // Set them to null to indicate no date filter is applied initially.
        $this->startDate = null;
        $this->endDate = null;

        $this->emitDateRange(); // Dispatch the initial null dates for the selected user
    }

    private function fetchDateRangeForUser(int $userId): void
    {
         $minMax = WaterQualityData::where('user_id', $userId)
             ->selectRaw('MIN(DATE(recorded_at)) as min_date, MAX(DATE(recorded_at)) as max_date')
             ->first();

         if ($minMax && $minMax->min_date) {
             $this->minAllowedDate = $minMax->min_date;
             $this->maxAllowedDate = $minMax->max_date ?? $minMax->min_date;
         } else {
             $this->minAllowedDate = null;
             $this->maxAllowedDate = null;
         }
         Log::info('DateFilter: Fetched date range.', ['userId' => $userId, 'min' => $this->minAllowedDate, 'max' => $this->maxAllowedDate]);
    }

    public function updated($propertyName): void
    {
        if (! in_array($propertyName, ['startDate', 'endDate', 'startTime', 'endTime'])) {
            return;
        }

        // Normalize inputs to plain Y-m-d and HH:MM (no timezone conversion)
        foreach (['startDate', 'endDate'] as $prop) {
            $val = $this->$prop;
            if ($val) {
                try {
                    $d = Carbon::createFromFormat('Y-m-d', $val);
                    $this->$prop = $d->format('Y-m-d'); // keep as date-only string
                } catch (\Exception $e) {
                    Log::warning('DateFilter: invalid date format', ['property' => $prop, 'value' => $val]);
                    $this->$prop = null;
                }
            }
        }

        foreach (['startTime', 'endTime'] as $prop) {
            $val = $this->$prop;
            if ($val === '') { $this->$prop = null; continue; }
            if ($val) {
                try {
                    $t = Carbon::createFromFormat('H:i', $val);
                    $this->$prop = $t->format('H:i');
                } catch (\Exception $e) {
                    Log::warning('DateFilter: invalid time format', ['property' => $prop, 'value' => $val]);
                    $this->$prop = null;
                }
            }
        }

        // Bounds & consistency checks (local only, do not emit)
        if ($this->startDate && $this->endDate && $this->endDate < $this->startDate) {
            $this->endDate = $this->startDate;
        }
        if ($this->startDate && $this->minAllowedDate && $this->startDate < $this->minAllowedDate) {
            $this->startDate = $this->minAllowedDate;
        }
        if ($this->startDate && $this->maxAllowedDate && $this->startDate > $this->maxAllowedDate) {
            $this->startDate = $this->maxAllowedDate;
        }
        if ($this->endDate && $this->minAllowedDate && $this->endDate < $this->minAllowedDate) {
            $this->endDate = $this->minAllowedDate;
        }

        // IMPORTANT: Do not emit here. Emission happens when user clicks Apply.
    }

    /**
     * Apply the currently selected date filters and emit them to listeners.
     */
    public function applyFilters(): void
    {
        // Ensure current user is selected for admin view
        if ($this->isAdminView && $this->currentUserId === null) {
            Log::warning('DateFilter: applyFilters called without selected user (admin).');
            return;
        }

        // Final normalization & validation
        foreach (['startDate', 'endDate'] as $prop) {
            $val = $this->$prop;
            if ($val) {
                try {
                    $d = Carbon::createFromFormat('Y-m-d', $val);
                    $this->$prop = $d->format('Y-m-d');
                } catch (\Exception $e) {
                    Log::warning('DateFilter: applyFilters invalid date', ['property' => $prop, 'value' => $val]);
                    $this->$prop = null;
                }
            }
        }

        // Enforce bounds again
        if ($this->startDate && $this->minAllowedDate && $this->startDate < $this->minAllowedDate) {
            $this->startDate = $this->minAllowedDate;
        }
        if ($this->endDate && $this->maxAllowedDate && $this->endDate > $this->maxAllowedDate) {
            $this->endDate = $this->maxAllowedDate;
        }
        if ($this->startDate && $this->endDate && $this->endDate < $this->startDate) {
            $this->endDate = $this->startDate;
        }

        $this->emitDateRange();
        Log::info('DateFilter: applyFilters emitted date range.', ['start' => $this->startDate, 'end' => $this->endDate]);
    }

    /**
     * Clear selected filters and emit nulls.
     */
    #[On('clear-filters')]
    public function clearFilters(): void
    {
        $this->startDate = null;
        $this->endDate = null;
        $this->startTime = null;
        $this->endTime = null;

        $this->emitDateRange();
        Log::info('DateFilter: clearFilters emitted null date range.');
    }

    protected function emitDateRange(): void
    {
        $bounds = $this->computeBounds();

        // Dispatch with both legacy date-only fields and precise datetime bounds
        $this->dispatch('date-range-updated',
            startDate: $this->startDate,
            endDate: $this->endDate,
            startTime: $this->startTime,
            endTime: $this->endTime,
            startDateTime: $bounds['start'],
            endDateTime: $bounds['end']
        );
        Log::info('DateFilter: Emitting date range.', [
            'start' => $this->startDate,
            'end' => $this->endDate,
            'startDateTime' => $bounds['start'],
            'endDateTime' => $bounds['end'],
        ]);
    }

    private function computeBounds(): array
    {
        $start = null; $end = null;

        if ($this->startDate) {
            try {
                $start = Carbon::parse($this->startDate)->startOfDay();
                if ($this->startTime) {
                    $start->setTimeFromTimeString($this->startTime);
                }
            } catch (\Throwable $e) {
                $start = null;
            }
        }

        if ($this->endDate) {
            try {
                $end = Carbon::parse($this->endDate)->endOfDay();
                if ($this->endTime) {
                    $end->setTimeFromTimeString($this->endTime);
                }
            } catch (\Throwable $e) {
                $end = null;
            }
        }

        return [
            'start' => $start?->toDateTimeString(),
            'end' => $end?->toDateTimeString(),
        ];
    }

    public function mount()
    {
        Log::info('DateFilter: Mounting component.');

        $loggedInUser = Auth::user();

        // **New Check:** Ensure we have a user object AND a valid ID
        if (!$loggedInUser || !$loggedInUser->getKey()) { // Use getKey() for robustness
            Log::error('DateFilter: Mount - Failed to get authenticated user or user ID is missing.', [
                'hasUserObject' => !is_null($loggedInUser),
                'userId' => $loggedInUser?->getKey() // Safely try to get ID for logging
            ]);
             // Stop execution if we don't have a valid user ID
             // Depending on requirements, you might want to set defaults or redirect
            return;
        }

        // Now we know $loggedInUser and its ID are valid
        $this->currentUserId = $loggedInUser->getKey(); // Assign the valid ID
        Log::info('DateFilter: Mount - User authenticated.', ['userId' => $this->currentUserId]);

        $this->isAdminView = $loggedInUser->isAdmin();
        Log::info('DateFilter: Mount - Role check.', ['isAdminView' => $this->isAdminView]);

        if ($this->isAdminView) {
            // Admin view: Initialize empty, don't fetch data until user is selected
            $this->currentUserId = null; // Explicitly null for admin initially AFTER logging it above
            $this->minAllowedDate = null;
            $this->maxAllowedDate = null;
            $this->startDate = null;
            $this->endDate = null;
            Log::info('DateFilter: Mount - Admin view initialized. Filter state depends on view logic now.');
            // Do NOT emit date range here for admin initially
        } else {
            // Non-admin view: Load data for the logged-in user immediately
            // We already confirmed $this->currentUserId is valid above
            if (!is_int($this->currentUserId)) {
                // This check is now technically redundant due to the check at the start,
                // but kept for safety / clarity if $currentUserId were manipulated elsewhere.
                Log::error('DateFilter: Mount - User ID is not an integer for non-admin.', ['userId' => $this->currentUserId]);
                return;
            }

            $this->fetchDateRangeForUser($this->currentUserId);

            // Set dates to null initially
            $this->startDate = null;
            $this->endDate = null;

            Log::info('DateFilter: Mount - Non-admin initialized. Emitting initial null date range.');
            $this->emitDateRange(); // Dispatch the initial null dates
        }
    }

    public function render()
    {
        // Pass necessary properties to the view
        return view('livewire.date-filter', [
            'isAdminView' => $this->isAdminView,
            'currentUserId' => $this->currentUserId, // Ensure currentUserId is available
            'minAllowedDate' => $this->minAllowedDate,
            'maxAllowedDate' => $this->maxAllowedDate,
        ]);
    }
}
