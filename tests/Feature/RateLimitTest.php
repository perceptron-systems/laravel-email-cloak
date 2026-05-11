<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Crypt;

it('returns 429 once the per-IP rate limit is exceeded', function () {
    config()->set('email-cloak.rate_limit', 3);

    $token = Crypt::encrypt(['email' => 'foo@bar.tld', 'exp' => time() + 3600]);
    $url = '/m?t='.rawurlencode($token);

    for ($i = 0; $i < 3; $i++) {
        $this->get($url)->assertStatus(302);
    }

    $this->get($url)->assertStatus(429);
});

it('counts invalid token requests against the rate limit too', function () {
    config()->set('email-cloak.rate_limit', 2);

    $url = '/m?t=garbage';

    $this->get($url)->assertStatus(404);
    $this->get($url)->assertStatus(404);
    $this->get($url)->assertStatus(429);
});
