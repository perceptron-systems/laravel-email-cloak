<?php

declare(strict_types=1);

namespace PerceptronSystems\EmailCloak\Support;

class EmailEncoder
{
    /**
     * Encode each character as a decimal HTML entity.
     *
     * Scrapers grepping for `@` or `mailto:` will not match; the browser
     * renders the text normally.
     */
    public static function toHtmlEntities(string $value): string
    {
        $out = '';

        foreach (mb_str_split($value, 1, 'UTF-8') as $char) {
            $out .= '&#'.mb_ord($char, 'UTF-8').';';
        }

        return $out;
    }

    /**
     * Verbalise an address's special characters for screen readers without
     * exposing the literal form to bots reading the attribute.
     *
     * @param  array<string, string>  $replacements
     */
    public static function toAriaLabel(string $email, array $replacements): string
    {
        return trim(str_replace(
            array_keys($replacements),
            array_values($replacements),
            $email
        ));
    }
}
