<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Cache;

class ResetModelsCache
{
    public function handle(Login $event)
    {
        Cache::forget('scanned_models');
    }
}
