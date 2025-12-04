<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('tms-loads', function ($user) {
    return (bool) $user;
});
