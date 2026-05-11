<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default obfuscation level
    |--------------------------------------------------------------------------
    |
    | May be overridden per call: @cloakedEmail($email, 'paranoid').
    |
    | - light     : decimal HTML entities + proxy route + verbalised aria-label
    | - balanced  : light + display:none decoy spans around the @
    | - paranoid  : balanced + DOM order scrambled via flex `order` (copy KO)
    |
    */
    'level' => env('EMAIL_CLOAK_LEVEL', 'balanced'),

    /*
    |--------------------------------------------------------------------------
    | Mailto proxy route
    |--------------------------------------------------------------------------
    |
    | URI exposing the redirect endpoint. The encrypted token is passed in the
    | `?t=` query string.
    |
    | Set `route_enabled` to false if your application registers the proxy
    | itself (e.g. behind authentication or under a custom controller).
    |
    | `route_middleware` lets you stack additional middleware on top of the
    | per-IP throttler — for instance to require a CSRF token or restrict to
    | a specific guard.
    |
    */
    'route_enabled' => (bool) env('EMAIL_CLOAK_ROUTE_ENABLED', true),

    'route_prefix' => env('EMAIL_CLOAK_ROUTE', '/m'),

    'route_name' => env('EMAIL_CLOAK_ROUTE_NAME', 'email-cloak.mailto'),

    'route_middleware' => ['throttle:email-cloak'],

    /*
    |--------------------------------------------------------------------------
    | Encrypted token TTL (seconds)
    |--------------------------------------------------------------------------
    |
    | After this duration, the proxy returns 410 Gone.
    |
    */
    'ttl' => (int) env('EMAIL_CLOAK_TTL', 86400),

    /*
    |--------------------------------------------------------------------------
    | Per-IP rate limit (requests / minute)
    |--------------------------------------------------------------------------
    |
    | A real user rarely resolves more than a handful of mailto: links per
    | minute (page with several team contacts, accidental clicks, NAT). Bots
    | doing mass harvesting want orders of magnitude more. A low default
    | catches the latter without inconveniencing the former.
    |
    */
    'rate_limit' => (int) env('EMAIL_CLOAK_RATE_LIMIT', 10),

    /*
    |--------------------------------------------------------------------------
    | CSS class applied to the link
    |--------------------------------------------------------------------------
    */
    'css_class' => env('EMAIL_CLOAK_CSS_CLASS', 'email-cloak'),

    /*
    |--------------------------------------------------------------------------
    | Special character verbalisation for aria-label
    |--------------------------------------------------------------------------
    |
    | Read by screen readers. Avoids exposing the literal address to bots
    | parsing the aria-label attribute. Override these for non-English
    | speaking sites (e.g. " arobase ", " point " for French).
    |
    */
    'aria' => [
        '@' => ' at ',
        '.' => ' dot ',
        '-' => ' dash ',
        '_' => ' underscore ',
        '+' => ' plus ',
    ],

    /*
    |--------------------------------------------------------------------------
    | Poison text injected in decoy spans (balanced/paranoid levels)
    |--------------------------------------------------------------------------
    |
    | Inserted between user and domain in <span style="display:none"> nodes.
    | Included by scrapers doing raw strip_tags, ignored by browsers at render
    | time and at copy-paste.
    |
    */
    'decoy' => env('EMAIL_CLOAK_DECOY', 'NOSPAM-REMOVE-THIS'),

];
