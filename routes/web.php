<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Orsal\EmailCloak\Http\Controllers\MailtoController;

if (config('email-cloak.route_enabled', true)) {
    Route::get(
        config('email-cloak.route_prefix', '/m'),
        MailtoController::class
    )
        ->middleware((array) config('email-cloak.route_middleware', ['throttle:email-cloak']))
        ->name(config('email-cloak.route_name', 'email-cloak.mailto'));
}
