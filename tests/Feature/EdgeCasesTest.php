<?php

declare(strict_types=1);

use Orsal\EmailCloak\EmailCloak;

it('rejects emails with non-ASCII characters in the local part', function () {
    app(EmailCloak::class)->render('dévenu@example.com');
})->throws(InvalidArgumentException::class);

it('accepts punycode (IDN) domains', function () {
    $email = 'info@xn--mnchen-3ya.de';

    $html = app(EmailCloak::class)->render($email, 'light')->toHtml();

    expect($html)
        ->not->toContain($email)
        ->toContain('&#120;')   // 'x'
        ->toContain('&#110;');  // 'n'
});

it('handles very long local parts without truncation', function () {
    $local = str_repeat('a', 64);
    $email = $local.'@example.com';

    $html = app(EmailCloak::class)->render($email, 'light')->toHtml();

    expect(substr_count($html, '&#97;'))->toBeGreaterThanOrEqual(64);
    expect($html)->not->toContain($email);
});

it('escapes HTML metacharacters injected via the decoy config', function () {
    config()->set('email-cloak.decoy', '<script>alert(1)</script>');

    $html = app(EmailCloak::class)->render('foo@bar.tld', 'balanced')->toHtml();

    expect($html)
        ->not->toContain('<script>')
        ->toContain('&lt;script&gt;');
});

it('escapes accents and quotes in the decoy without breaking attributes', function () {
    config()->set('email-cloak.decoy', 'évidemment-"NOSPAM"');

    $html = app(EmailCloak::class)->render('foo@bar.tld', 'balanced')->toHtml();

    expect($html)
        ->toContain('évidemment')
        ->toContain('&quot;NOSPAM&quot;');
});

it('renders paranoid mode for UTF-8-heavy domains via punycode', function () {
    $email = 'a@xn--bcher-kva.de';

    $html = app(EmailCloak::class)->render($email, 'paranoid')->toHtml();

    expect($html)
        ->toContain('email-cloak--scrambled')
        ->toMatch('/<span style="order:\d+">/');
});

it('handles a custom French aria-label map without altering the address', function () {
    config()->set('email-cloak.aria', [
        '@' => ' arobase ',
        '.' => ' point ',
    ]);

    $html = app(EmailCloak::class)->render('contact@example.com')->toHtml();

    expect($html)
        ->toContain('aria-label="contact arobase example point com"')
        ->not->toContain('contact@example.com');
});
