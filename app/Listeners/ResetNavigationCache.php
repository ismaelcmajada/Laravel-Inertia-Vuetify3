<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Cache;

class ResetNavigationCache
{
    public function handle(Login $event)
    {
        Cache::forget('navigation_routes');
    }
}

