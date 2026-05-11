# laravel-email-cloak

[![GitHub Workflow Status (main)](https://img.shields.io/github/actions/workflow/status/perceptron-systems/laravel-email-cloak/ci.yml?branch=main&style=flat-square)](https://github.com/perceptron-systems/laravel-email-cloak/actions/workflows/ci.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/perceptron-systems/laravel-email-cloak.svg?style=flat-square)](https://packagist.org/packages/perceptron-systems/laravel-email-cloak)
[![Latest Version](https://img.shields.io/packagist/v/perceptron-systems/laravel-email-cloak.svg?style=flat-square)](https://packagist.org/packages/perceptron-systems/laravel-email-cloak)
[![License](https://img.shields.io/packagist/l/perceptron-systems/laravel-email-cloak.svg?style=flat-square)](LICENSE)

Email address obfuscation for Laravel — **zero JavaScript**, encrypted `mailto:` proxy, designed for Core Web Vitals and accessibility.

Displays an email address that is visible, selectable and clickable to visitors, while making automated extraction significantly more expensive for spam-harvesting bots.

## Why

When a legal obligation (legal notices, GDPR, corporate contact) requires displaying an email address in clear text, every common solution has a downside:

- **Image** → not selectable, not accessible, not indexable.
- **JavaScript** → hurts Core Web Vitals and breaks in no-JS environments (AMP, some crawlers, low-power readers).
- **Output-parsing middleware** → slows every response and makes rendering fragile.

`laravel-email-cloak` makes an explicit trade-off: the address remains **readable, selectable and copyable** by humans, and we push scraper resistance as far as we can under that constraint.

## Installation

```bash
composer require perceptron-systems/laravel-email-cloak
```

The service provider is auto-discovered.

Publish the configuration and the CSS:

```bash
php artisan vendor:publish --tag=email-cloak-config
php artisan vendor:publish --tag=email-cloak-assets
```

Then include the stylesheet in your layout:

```blade
<link rel="stylesheet" href="{{ asset('vendor/email-cloak/email-cloak.css') }}">
```

> **⚠ The CSS is required for the `balanced` (default) and `paranoid` levels.**
> Without it, `balanced` reveals the `NOSPAM-REMOVE-THIS` decoy spans on screen, and `paranoid` displays characters in the randomised DOM order — both unreadable for users.
> The `light` level is the only one that works without the stylesheet (you can then skip `--tag=email-cloak-assets`).

## Usage

### Blade directive

```blade
@cloakedEmail('contact@example.com')
```

With a custom label (the address never appears in the rendered HTML):

```blade
@cloakedEmail('contact@example.com', 'light', 'Contact us')
```

### Injectable service

```php
use Orsal\EmailCloak\EmailCloak;

public function show(EmailCloak $cloak)
{
    return view('contact', [
        'mail' => $cloak->render('contact@example.com'),
    ]);
}
```

## How it works

| Layer | Effect |
|---|---|
| **Decimal HTML entities** | `contact@example.com` becomes `&#99;&#111;&#110;…` in the source. Naive regexes (`/[\w.]+@[\w.]+/`) don't match; the browser displays the text normally. |
| **Encrypted proxy route** | `href` points to `/m?t={token}` instead of `mailto:`. The token is `Crypt::encrypt(['email' => …, 'exp' => …])` — opaque, stateless, expiring. |
| **Rate limit** | The proxy route is throttled per IP using Laravel's native RateLimiter (default 30/min). A crawler resolving tokens en masse is slowed. |
| **Verbalised `aria-label`** | Screen readers announce "contact at example dot com". The literal address never appears in any attribute. |
| **`X-Robots-Tag: noindex, nofollow`** | The proxy route is not indexable. |
| **Server-side validation** | `filter_var(..., FILTER_VALIDATE_EMAIL)` and expiry check before any `mailto:` redirect. |

## Obfuscation levels

Configurable globally in `config/email-cloak.php`, or per call.

| Level | Selection | Copy | Bot resistance |
|---|---|---|---|
| `light` | ✅ | ✅ clean | Low — entities + proxy |
| `balanced` *(default)* | ✅ | ✅ with decoys auto-stripped by most paste targets | Medium — entities + `display:none` poison spans around the `@` + proxy |
| `paranoid` | ✅ | ❌ scrambled | High — characters in spans reordered via `flex order`; copy is unusable but human reading is correct |

Per-call override:

```blade
@cloakedEmail('contact@example.com', 'paranoid')
```

## Configuration

Available environment variables:

```dotenv
EMAIL_CLOAK_LEVEL=balanced
EMAIL_CLOAK_ROUTE=/m
EMAIL_CLOAK_ROUTE_ENABLED=true
EMAIL_CLOAK_ROUTE_NAME=email-cloak.mailto
EMAIL_CLOAK_TTL=86400
EMAIL_CLOAK_RATE_LIMIT=30
EMAIL_CLOAK_CSS_CLASS=email-cloak
EMAIL_CLOAK_DECOY=NOSPAM-REMOVE-THIS
```

Set `EMAIL_CLOAK_ROUTE_ENABLED=false` if your application registers the proxy
itself (custom controller, authenticated guard, etc.). The published
`config/email-cloak.php` also exposes `route_middleware` to stack additional
middleware on top of the per-IP throttle (CSRF, auth, custom guards).

For non-English sites, override the `aria` map in the published
`config/email-cloak.php` (e.g. `' arobase '`, `' point '` for French).

## Acknowledged limits

This library does **not** claim to hide the address from bots that render the page like a browser (headless Chromium and similar). Under the constraint "visible + selectable + clickable" this is mathematically impossible.

What it does deliver:

- Blocks the overwhelming majority of HTTP scrapers that parse raw HTML without rendering the DOM.
- Prevents direct `mailto:` harvesting from `href`.
- Makes large-scale exploitation expensive (rate-limit, encrypted short-lived tokens, anti-indexation headers).
- Stays accessible (ARIA, selectable, no JS dependency).

## Testing

```bash
composer install
composer test
```

The suite verifies, among other things, that **no literal occurrence** of the address, of `mailto:`, or of the `@` character appears in the rendered HTML at any level.

## Licence

MIT
