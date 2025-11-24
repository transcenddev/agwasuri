<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class UserProfileUpdated implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public User $user;
    public array $changes;

    public function __construct(User $user, array $changes = [])
    {
        $this->user = $user;
        $this->changes = $changes;
    }

    public function broadcastOn()
    {
        // Broadcast to admin channel and user's personal channel if needed
        return new PrivateChannel('admin.users');
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->user->user_id,
            'water_type' => $this->user->water_type,
            'changes' => $this->changes,
        ];
    }

    public function broadcastAs()
    {
        return 'UserProfileUpdated';
    }
}
