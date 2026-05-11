<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Crypt;

it('redirects a valid token to the mailto: URL', function () {
    $token = Crypt::encrypt(['email' => 'foo@bar.tld', 'exp' => time() + 3600]);

    $response = $this->get('/m?t=' . rawurlencode($token));

    $response->assertStatus(302);
    expect($response->headers->get('Location'))->toBe('mailto:foo@bar.tld');
    expect($response->headers->get('X-Robots-Tag'))->toBe('noindex, nofollow');
});

it('returns 410 Gone when the token is expired', function () {
    $token = Crypt::encrypt(['email' => 'foo@bar.tld', 'exp' => time() - 1]);

    $this->get('/m?t=' . rawurlencode($token))->assertStatus(410);
});

it('returns 404 when the token cannot be decrypted', function () {
    $this->get('/m?t=' . rawurlencode('not-a-valid-token'))->assertStatus(404);
});

it('returns 404 when the token is missing', function () {
    $this->get('/m')->assertStatus(404);
});

it('returns 404 when the decrypted payload misses required keys', function () {
    $token = Crypt::encrypt(['something' => 'else']);

    $this->get('/m?t=' . rawurlencode($token))->assertStatus(404);
});

it('returns 404 when the decrypted email is not a valid address', function () {
    $token = Crypt::encrypt(['email' => 'not-an-email', 'exp' => time() + 3600]);

    $this->get('/m?t=' . rawurlencode($token))->assertStatus(404);
});
