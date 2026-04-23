<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are used
| to determine if an authenticated user can listen to the channel.
|
*/

// Public channels - no authentication required
Broadcast::channel('daytour-inventory', function () {
    return true;
});

Broadcast::channel('daytour-inventory.{id}', function () {
    return true;
});

Broadcast::channel('staycation-inventory', function () {
    return true;
});

Broadcast::channel('staycation-inventory.{id}', function () {
    return true;
});

Broadcast::channel('booking-updates', function () {
    return true;
});

Broadcast::channel('activity-updates', function () {
    return true;
});

// Private channels - require authentication
Broadcast::channel('booking.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('admin.notifications', function ($user) {
    return $user->user_type === 'admin';
});
