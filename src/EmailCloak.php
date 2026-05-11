<?php

declare(strict_types=1);

namespace PerceptronSystems\EmailCloak;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Support\HtmlString;
use PerceptronSystems\EmailCloak\Support\EmailEncoder;

class EmailCloak
{
    public function __construct(
        private readonly ConfigRepository $config,
        private readonly Encrypter $encrypter,
        private readonly UrlGenerator $urls,
    ) {
    }

    /**
     * Génère le HTML d'un lien email obfusqué.
     */
    public function render(string $email, ?string $level = null, ?string $label = null): HtmlString
    {
        $level = $level ?? $this->config->get('email-cloak.level', 'light');
        $cssClass = $this->config->get('email-cloak.css_class', 'email-cloak');
        $aria = EmailEncoder::toAriaLabel(
            $email,
            $this->config->get('email-cloak.aria', [])
        );

        $href = $this->buildProxyUrl($email);
        $visible = $this->buildVisibleContent($email, $level, $label);

        $cssClasses = $cssClass . ($level === 'paranoid' ? ' email-cloak--scrambled' : '');

        $html = sprintf(
            '<a href="%s" class="%s" aria-label="%s" rel="nofollow noopener">%s</a>',
            e($href),
            e($cssClasses),
            e($aria),
            $visible
        );

        return new HtmlString($html);
    }

    /**
     * URL absolue de la route proxy avec token chiffré (email + expiration).
     */
    private function buildProxyUrl(string $email): string
    {
        $payload = [
            'email' => $email,
            'exp' => time() + (int) $this->config->get('email-cloak.ttl', 86400),
        ];

        $token = $this->encrypter->encrypt($payload);
        $prefix = (string) $this->config->get('email-cloak.route_prefix', '/m');

        return $this->urls->to($prefix) . '?t=' . rawurlencode($token);
    }

    /**
     * Contenu visible du `<a>`. Si un label est fourni, on l'utilise tel quel
     * (texte arbitraire qui ne ressemble pas à une adresse). Sinon on rend
     * l'email selon le niveau d'obfuscation.
     */
    private function buildVisibleContent(string $email, string $level, ?string $label): string
    {
        if ($label !== null) {
            return e($label);
        }

        return match ($level) {
            'paranoid' => $this->renderScrambled($email),
            'balanced' => $this->renderWithDecoys($email),
            default => EmailEncoder::toHtmlEntities($email),
        };
    }

    /**
     * Niveau balanced : entités + spans décoy display:none + zero-width spaces.
     */
    private function renderWithDecoys(string $email): string
    {
        $decoy = (string) $this->config->get('email-cloak.decoy', 'NOSPAM');
        [$user, $domain] = $this->splitEmail($email);

        $zeroWidth = '&#8203;';

        return EmailEncoder::toHtmlEntities($user)
            . '<span data-cloak-decoy aria-hidden="true">' . e($decoy) . '</span>'
            . $zeroWidth
            . '&#64;'
            . $zeroWidth
            . '<span data-cloak-decoy aria-hidden="true">' . e($decoy) . '</span>'
            . EmailEncoder::toHtmlEntities($domain);
    }

    /**
     * Niveau paranoid : caractères dans des spans réordonnés via flex `order`.
     * Lecture visuelle correcte, copier-coller scramblé.
     */
    private function renderScrambled(string $email): string
    {
        $chars = mb_str_split($email, 1, 'UTF-8');
        $count = count($chars);

        $indexes = range(0, $count - 1);
        shuffle($indexes);

        $spans = [];

        foreach ($indexes as $domPosition => $visualPosition) {
            $char = $chars[$visualPosition];
            $entity = '&#' . mb_ord($char, 'UTF-8') . ';';
            $spans[] = sprintf(
                '<span style="order:%d">%s</span>',
                $visualPosition,
                $entity
            );
        }

        return implode('', $spans);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitEmail(string $email): array
    {
        $at = strrpos($email, '@');

        if ($at === false) {
            return [$email, ''];
        }

        return [substr($email, 0, $at), substr($email, $at + 1)];
    }
}
