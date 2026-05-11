<?php

declare(strict_types=1);

namespace PerceptronSystems\EmailCloak\Http\Controllers;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MailtoController
{
    public function __construct(private readonly Encrypter $encrypter)
    {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $token = $request->query('t');

        if (! is_string($token) || $token === '') {
            abort(404);
        }

        try {
            $payload = $this->encrypter->decrypt($token);
        } catch (DecryptException) {
            abort(404);
        }

        if (! is_array($payload) || ! isset($payload['email'], $payload['exp'])) {
            abort(404);
        }

        if ((int) $payload['exp'] < time()) {
            abort(410);
        }

        if (! filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
            abort(404);
        }

        return redirect()
            ->away('mailto:' . $payload['email'], 302)
            ->withHeaders(['X-Robots-Tag' => 'noindex, nofollow']);
    }
}
