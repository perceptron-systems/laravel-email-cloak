<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Niveau d'obfuscation par défaut
    |--------------------------------------------------------------------------
    |
    | Surchargeable au cas par cas via @cloakedEmail($email, 'paranoid').
    |
    | - light     : entités HTML décimales + route proxy + aria-label
    | - balanced  : light + spans décoy display:none + zero-width spaces
    | - paranoid  : balanced + ordre DOM scramblé via flex `order` (copie KO)
    |
    */
    'level' => env('EMAIL_CLOAK_LEVEL', 'balanced'),

    /*
    |--------------------------------------------------------------------------
    | Route proxy mailto
    |--------------------------------------------------------------------------
    |
    | URI sur laquelle est exposée la route de redirection mailto.
    | Le token chiffré est passé en query string `?t=`.
    |
    */
    'route_prefix' => env('EMAIL_CLOAK_ROUTE', '/m'),

    /*
    |--------------------------------------------------------------------------
    | Durée de vie du token chiffré (secondes)
    |--------------------------------------------------------------------------
    |
    | Au-delà de cette durée, le proxy renvoie 410 Gone.
    |
    */
    'ttl' => (int) env('EMAIL_CLOAK_TTL', 86400),

    /*
    |--------------------------------------------------------------------------
    | Rate limit par IP (requêtes / minute)
    |--------------------------------------------------------------------------
    */
    'rate_limit' => (int) env('EMAIL_CLOAK_RATE_LIMIT', 30),

    /*
    |--------------------------------------------------------------------------
    | Classe CSS appliquée au lien
    |--------------------------------------------------------------------------
    */
    'css_class' => env('EMAIL_CLOAK_CSS_CLASS', 'email-cloak'),

    /*
    |--------------------------------------------------------------------------
    | Verbalisation des caractères pour l'aria-label
    |--------------------------------------------------------------------------
    |
    | Lu par les lecteurs d'écran. Évite d'exposer l'adresse littérale aux
    | bots qui parseraient l'attribut aria-label.
    |
    */
    'aria' => [
        '@' => ' arobase ',
        '.' => ' point ',
        '-' => ' tiret ',
        '_' => ' souligné ',
        '+' => ' plus ',
    ],

    /*
    |--------------------------------------------------------------------------
    | Texte poison injecté en spans décoy (niveau balanced/paranoid)
    |--------------------------------------------------------------------------
    |
    | Inséré entre user et domain dans des <span style="display:none">. Inclus
    | par les scrapers qui font strip_tags brut, ignoré par les navigateurs au
    | rendu et au copier-coller.
    |
    */
    'decoy' => env('EMAIL_CLOAK_DECOY', 'NOSPAM-REMOVE-THIS'),

];
