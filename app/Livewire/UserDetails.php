<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;

class UserDetails extends Component
{
    public $userId;
    public $userDetails;

    protected $listeners = ['loadUserDetails'];

    public function loadUserDetails($userId)
    {
        $this->userId = $userId;
        $this->userDetails = User::find($userId);
    }

    public function render()
    {
        return view('livewire.user-details');
    }
}