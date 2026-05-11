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
    | - balanced  : light + display:none decoy spans + zero-width spaces
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
    */
    'route_prefix' => env('EMAIL_CLOAK_ROUTE', '/m'),

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
    */
    'rate_limit' => (int) env('EMAIL_CLOAK_RATE_LIMIT', 30),

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
