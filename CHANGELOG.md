# Changelog

All notable changes to `laravel-email-cloak` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Blade directive `@cloakedEmail($email, ?$level, ?$label)`.
- Anonymous Blade component `<x-email-cloak::cloaked-email :email :level :label />`.
- Three obfuscation levels: `light` (decimal HTML entities + proxy), `balanced` (default — adds `display:none` decoy spans around the `@`), `paranoid` (characters scrambled in DOM order, reordered visually via flex `order`).
- Encrypted, stateless mailto proxy route (`/m?t={token}`) with TTL, per-IP rate limit (default 30/min), `X-Robots-Tag: noindex, nofollow` and server-side `FILTER_VALIDATE_EMAIL` check before redirect.
- Verbalised `aria-label` (configurable map; defaults to English `at` / `dot` / `dash` / `underscore` / `plus`).
- Configurable proxy route: `route_enabled`, `route_prefix`, `route_name`, `route_middleware` — host application can disable the auto-registered route or stack additional middleware (CSRF, auth, custom guards).
- Publishable config (`email-cloak-config`), CSS (`email-cloak-assets`) and views (`email-cloak-views`).
- Test suite (Pest 4 + Orchestra Testbench): unit, feature and integration tests asserting that no literal email or `@` character is leaked at any level.
- CI matrix: PHP 8.3 / 8.4 / 8.5 × Laravel 11 / 12 / 13, plus Pint and PHPStan (Larastan, level 8) jobs.
