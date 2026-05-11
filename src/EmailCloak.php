<?php

declare(strict_types=1);

namespace Orsal\EmailCloak;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Support\HtmlString;
use InvalidArgumentException;
use Orsal\EmailCloak\Support\EmailEncoder;

class EmailCloak
{
    public function __construct(
        private readonly ConfigRepository $config,
        private readonly Encrypter $encrypter,
        private readonly UrlGenerator $urls,
    ) {}

    /**
     * Render an obfuscated email link as HTML.
     */
    public function render(string $email, ?string $level = null, ?string $label = null): HtmlString
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("EmailCloak::render() expects a valid email address, got: {$email}");
        }

        $level = $level ?? $this->config->get('email-cloak.level', 'light');
        $cssClass = $this->config->get('email-cloak.css_class', 'email-cloak');
        $aria = EmailEncoder::toAriaLabel(
            $email,
            $this->config->get('email-cloak.aria', [])
        );

        $href = $this->buildProxyUrl($email);
        $visible = $this->buildVisibleContent($email, $level, $label);

        $isScrambled = $level === 'paranoid' && $label === null;
        $cssClasses = $cssClass.($isScrambled ? ' email-cloak--scrambled' : '');

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
     * Absolute proxy URL with an encrypted token (email + expiry).
     */
    private function buildProxyUrl(string $email): string
    {
        $payload = [
            'email' => $email,
            'exp' => time() + (int) $this->config->get('email-cloak.ttl', 86400),
        ];

        $token = $this->encrypter->encrypt($payload);
        $prefix = (string) $this->config->get('email-cloak.route_prefix', '/m');

        return $this->urls->to($prefix).'?t='.rawurlencode($token);
    }

    /**
     * Visible content of the `<a>`. When a label is provided we use it as-is
     * (arbitrary text that does not look like an address). Otherwise we render
     * the email according to the obfuscation level.
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
     * Balanced level: entities + display:none decoy spans around the @.
     *
     * Decoy text is swallowed by browsers (display:none) and most paste targets,
     * but included by scrapers doing raw strip_tags. We deliberately avoid
     * zero-width characters here to keep copied addresses byte-clean in form
     * inputs and mail clients.
     */
    private function renderWithDecoys(string $email): string
    {
        $decoy = (string) $this->config->get('email-cloak.decoy', 'NOSPAM');
        [$user, $domain] = $this->splitEmail($email);

        return EmailEncoder::toHtmlEntities($user)
            .'<span data-cloak-decoy aria-hidden="true">'.e($decoy).'</span>'
            .'&#64;'
            .'<span data-cloak-decoy aria-hidden="true">'.e($decoy).'</span>'
            .EmailEncoder::toHtmlEntities($domain);
    }

    /**
     * Paranoid level: characters wrapped in spans reordered through flex `order`.
     * Visual order is correct; copy-paste is scrambled.
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
            $entity = '&#'.mb_ord($char, 'UTF-8').';';
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
