<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders the x-email-cloak::cloaked-email component without exposing the address', function () {
    $email = 'contact@example.com';

    $html = Blade::render(
        '<x-email-cloak::cloaked-email :email="$email" />',
        ['email' => $email]
    );

    expect($html)
        ->not->toContain($email)
        ->not->toContain('mailto:')
        ->toContain('aria-label="contact at example dot com"');
});

it('forwards level and label props to the underlying renderer', function () {
    $html = Blade::render(
        '<x-email-cloak::cloaked-email email="foo@bar.tld" level="paranoid" label="Write to us" />'
    );

    expect($html)
        ->toContain('email-cloak--scrambled')
        ->toContain('>Write to us</a>');
});
