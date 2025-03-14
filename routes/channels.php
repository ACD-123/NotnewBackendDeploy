<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('messages.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
}, ['guards' => 'api']);

Broadcast::channel('offers.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
}, ['guards' => 'api']);

Broadcast::channel('channel-test', function ($user, $id) {
    return (int) $user->id === (int) $id;
}, ['guards' => 'api']);

Broadcast::channel('chat-channel-{id}', function ($chat,$id) {
    return (int) $chat->uid === (int) $id;
});
Broadcast::channel('bid-channel-{id}', function ($bid,$id) {
    return (int) $bid->product_id === (int) $id;
});
Broadcast::channel('notification-channel-{id}', function ($notification,$id) {
    return (int) $notification->user_id === (int) $id;
});
