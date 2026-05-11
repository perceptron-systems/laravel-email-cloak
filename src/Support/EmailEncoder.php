<?php

declare(strict_types=1);

namespace PerceptronSystems\EmailCloak\Support;

class EmailEncoder
{
    /**
     * Encode chaque caractère en entité HTML décimale.
     *
     * Les scrapers qui regex sur `@` ou `mailto:` ne matchent pas, le
     * navigateur restitue le texte normalement.
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
     * Verbalise les caractères spéciaux d'une adresse pour les lecteurs
     * d'écran sans exposer la forme littérale aux bots.
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
