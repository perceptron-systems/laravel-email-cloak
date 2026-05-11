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

it('forwards the level prop to the underlying renderer', function () {
    $html = Blade::render(
        '<x-email-cloak::cloaked-email email="foo@bar.tld" level="paranoid" />'
    );

    expect($html)
        ->toContain('email-cloak--scrambled')
        ->toMatch('/<span style="order:\d+">/');
});

it('forwards the label prop to the underlying renderer', function () {
    $html = Blade::render(
        '<x-email-cloak::cloaked-email email="foo@bar.tld" label="Write to us" />'
    );

    expect($html)
        ->toContain('>Write to us</a>')
        ->not->toContain('foo@bar.tld');
});
