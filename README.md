# laravel-email-cloak

[![Latest Version on Packagist](https://img.shields.io/packagist/v/perceptron-systems/laravel-email-cloak.svg?style=flat-square)](https://packagist.org/packages/perceptron-systems/laravel-email-cloak)
[![Tests](https://img.shields.io/github/actions/workflow/status/perceptron-systems/laravel-email-cloak/ci.yml?branch=main&label=tests&style=flat-square)](https://github.com/perceptron-systems/laravel-email-cloak/actions/workflows/ci.yml)
[![PHP Version](https://img.shields.io/packagist/dependency-v/perceptron-systems/laravel-email-cloak/php?style=flat-square)](https://packagist.org/packages/perceptron-systems/laravel-email-cloak)
[![Total Downloads](https://img.shields.io/packagist/dt/perceptron-systems/laravel-email-cloak.svg?style=flat-square)](https://packagist.org/packages/perceptron-systems/laravel-email-cloak)
[![License](https://img.shields.io/packagist/l/perceptron-systems/laravel-email-cloak.svg?style=flat-square)](LICENSE)

Obfuscation d'adresses e-mail pour Laravel — **0 JavaScript**, route `mailto:` signée, pensée pour Core Web Vitals et l'accessibilité.

Affiche une adresse e-mail visible, sélectionnable et cliquable par les visiteurs, en rendant son extraction automatique nettement plus coûteuse pour les robots collecteurs de spam.

## Pourquoi

Quand une obligation légale (mentions légales, RGPD, contact corporate) impose d'afficher une adresse e-mail en clair, les solutions habituelles ont chacune un défaut :

- **Image** → ni sélectionnable, ni accessible, ni indexable.
- **JavaScript** → impacte les Core Web Vitals et casse en environnement no-JS (AMP, certains crawlers, lecteurs basse conso).
- **Middleware d'output parsing** → ralentit chaque réponse et fragilise le rendu.

`laravel-email-cloak` prend le compromis assumé suivant : l'adresse reste **lisible, sélectionnable et copiable** par un humain, et l'on pousse au maximum la résistance aux scrapers sous cette contrainte.

## Installation

```bash
composer require perceptron-systems/laravel-email-cloak
```

Le ServiceProvider est auto-découvert.

Publier la configuration et le CSS optionnel :

```bash
php artisan vendor:publish --tag=email-cloak-config
php artisan vendor:publish --tag=email-cloak-assets
```

## Utilisation

### Directive Blade

```blade
@cloakedEmail('contact@monsite.fr')
```

Avec un libellé personnalisé (l'adresse n'apparaît alors nulle part dans le HTML rendu) :

```blade
@cloakedEmail('contact@monsite.fr', 'light', 'Nous écrire')
```

### Service injectable

```php
use PerceptronSystems\EmailCloak\EmailCloak;

public function show(EmailCloak $cloak)
{
    return view('contact', [
        'mail' => $cloak->render('contact@monsite.fr'),
    ]);
}
```

## Comment ça marche

| Couche | Effet |
|---|---|
| **Entités HTML décimales** | `contact@monsite.fr` devient `&#99;&#111;&#110;…` dans le HTML. Les regex naïves (`/[\w.]+@[\w.]+/`) ne matchent rien ; le navigateur restitue le texte normalement. |
| **Route proxy signée** | `href` pointe vers `/m?t={token}` au lieu de `mailto:`. Le token est `Crypt::encrypt(['email' => …, 'exp' => …])` — opaque, sans accès DB, avec TTL. |
| **Rate limit** | La route proxy est limitée par IP via le RateLimiter natif de Laravel (par défaut 30/min). Un crawler qui résout les tokens en masse est freiné. |
| **`aria-label` verbalisé** | Les lecteurs d'écran lisent « contact arobase monsite point fr ». L'adresse littérale n'apparaît dans aucun attribut. |
| **`X-Robots-Tag: noindex, nofollow`** | La route proxy n'est pas indexable. |
| **Validation côté proxy** | `filter_var(..., FILTER_VALIDATE_EMAIL)` + vérification d'expiration avant tout `mailto:` redirect. |

## Niveaux d'obfuscation

Configurable globalement dans `config/email-cloak.php`, ou par appel.

| Niveau | Sélection | Copie | Résistance bot |
|---|---|---|---|
| `light` *(défaut)* | ✅ | ✅ propre | Faible — entités + proxy |
| `balanced` | ✅ | ✅ avec décoys auto-strippés à la plupart des collages | Moyenne — entités + spans `display:none` poison + zero-width spaces + proxy |
| `paranoid` | ✅ | ❌ scramblée | Haute — caractères en spans réordonnés via `flex` `order`, copie inutilisable mais lecture humaine OK |

Surcharge par appel :

```blade
@cloakedEmail('contact@monsite.fr', 'paranoid')
```

## Configuration

Variables d'environnement disponibles :

```dotenv
EMAIL_CLOAK_LEVEL=light
EMAIL_CLOAK_ROUTE=/m
EMAIL_CLOAK_TTL=86400
EMAIL_CLOAK_RATE_LIMIT=30
EMAIL_CLOAK_CSS_CLASS=email-cloak
EMAIL_CLOAK_DECOY=NOSPAM-REMOVE-THIS
```

## Limites assumées

Cette bibliothèque ne prétend **pas** rendre l'adresse invisible aux bots qui rendent une page comme un navigateur (headless Chromium et assimilés). Sous la contrainte « visible + sélectionnable + cliquable », c'est mathématiquement impossible.

Ce qu'elle apporte :

- Bloque l'écrasante majorité des scrapers HTTP qui parsent le HTML brut sans rendre le DOM.
- Empêche la collecte directe via `mailto:` dans le `href`.
- Rend l'exploitation à grande échelle coûteuse (rate-limit, tokens chiffrés à courte durée de vie, headers anti-indexation).
- Reste accessible (ARIA, sélectionnable, sans dépendance JS).

## Tests

```bash
composer install
composer test
```

La suite vérifie notamment qu'**aucune occurrence littérale** de l'adresse, ni de `mailto:`, ni du caractère `@` ne se retrouve dans le HTML rendu au niveau `light`.

## Licence

MIT
