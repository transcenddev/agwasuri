<?php

namespace App\Observers;

use App\Models\User;
use App\Events\UserProfileUpdated;

class UserObserver
{
    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        if ($user->wasChanged('water_type')) {
            $changes = ['water_type' => $user->water_type];
            event(new UserProfileUpdated($user, $changes));
        }
    }
}
