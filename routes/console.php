<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
*/

// Retry failed webhook events every 5 minutes (max 3 attempts).
Schedule::command('webhook:retry-failed --max-attempts=3')
    ->everyFiveMinutes()
    ->withoutOverlapping();
