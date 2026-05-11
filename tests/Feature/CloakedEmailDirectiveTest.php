<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Orsal\EmailCloak\EmailCloak;

it('renders without the literal email anywhere in the markup', function () {
    $email = 'contact@monsite.fr';

    $html = app(EmailCloak::class)->render($email)->toHtml();

    expect($html)->not->toContain($email);
    expect($html)->not->toContain('mailto:');
    expect($html)->not->toContain('@');
});

it('encodes every character of the email as a decimal HTML entity', function () {
    $email = 'a@b.c';

    $html = app(EmailCloak::class)->render($email)->toHtml();

    expect($html)
        ->toContain('&#97;')
        ->toContain('&#64;')
        ->toContain('&#98;')
        ->toContain('&#46;')
        ->toContain('&#99;');
});

it('emits a verbalised aria-label instead of the raw address', function () {
    $email = 'guillaume@orsal.net';

    $html = app(EmailCloak::class)->render($email)->toHtml();

    expect($html)
        ->toContain('aria-label="guillaume at orsal dot net"')
        ->not->toContain('aria-label="guillaume@orsal.net"');
});

it('points href to the configured proxy route with an opaque token', function () {
    config()->set('email-cloak.route_prefix', '/m');
    $email = 'foo@bar.tld';

    $html = app(EmailCloak::class)->render($email)->toHtml();

    expect($html)->toMatch('#href="https?://[^"]*/m\?t=[^"]+"#');
    expect($html)->not->toContain($email);
});

it('uses a custom label when provided and still hides the address from the link', function () {
    $email = 'support@perceptron-systems.com';

    $html = app(EmailCloak::class)->render($email, label: 'Contact us')->toHtml();

    expect($html)
        ->toContain('>Contact us</a>')
        ->not->toContain($email);
});

it('throws when render() receives an invalid email', function () {
    app(EmailCloak::class)->render('not-an-email');
})->throws(InvalidArgumentException::class);

it('compiles the @cloakedEmail blade directive', function () {
    $compiled = Blade::compileString("@cloakedEmail('foo@bar.tld')");

    expect($compiled)->toContain('Orsal\\EmailCloak\\EmailCloak');
    expect($compiled)->toContain("render('foo@bar.tld')");
});

it('injects display:none decoy spans at balanced level', function () {
    $html = app(EmailCloak::class)->render('foo@bar.tld', 'balanced')->toHtml();

    expect($html)
        ->toContain('data-cloak-decoy')
        ->toContain('aria-hidden="true"');
});

it('scrambles characters into ordered flex spans at paranoid level', function () {
    $html = app(EmailCloak::class)->render('foo@bar.tld', 'paranoid')->toHtml();

    expect($html)
        ->toContain('email-cloak--scrambled')
        ->toMatch('/<span style="order:\d+">/');
});
