<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use PerceptronSystems\EmailCloak\Http\Controllers\MailtoController;

Route::get(
    config('email-cloak.route_prefix', '/m'),
    MailtoController::class
)
    ->middleware(['throttle:email-cloak'])
    ->name('email-cloak.mailto');
