<?php

Broadcast::channel('App.Models.User.{id}', \App\Broadcasting\UserChannel::class);
