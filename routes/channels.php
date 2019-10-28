<?php

use App\Models\User;

Broadcast::channel('App.Models.User.{id}', function (User $user, $id) {
    return $user->id == $id;
});
