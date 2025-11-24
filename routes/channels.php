<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('admin.users', function ($user) {
    // Only allow admin users to subscribe to the admin channel
    return $user && method_exists($user, 'isAdmin') && $user->isAdmin();
});
