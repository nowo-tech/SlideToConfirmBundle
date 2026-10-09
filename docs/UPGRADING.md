# Upgrading

## Table of contents

- [Unreleased](#unreleased)
- [To 1.1.5](#to-115)
- [To 1.1.4](#to-114)
- [To 1.1.3](#to-113)
- [To 1.1.2](#to-112)
- [From 1.1.0 to 1.1.1](#from-110-to-111)
- [From 1.0.0 to 1.1.0](#from-100-to-110)
- [1.0.0 (2026-08-23)](#100-2026-08-23)

## Unreleased

## To 1.1.5

From **1.1.4** — dependency updates.

```bash
composer update nowo-tech/slide-to-confirm-bundle
```

No breaking changes. No application upgrade steps.

## To 1.1.4

From **1.1.3** — Spec/docs only (FR-9 CSP asset loading). **No runtime change.**

```bash
composer update nowo-tech/slide-to-confirm-bundle
```

## To 1.1.3

From **1.1.2** — CSP-safe standalone IIFE (no runtime `<style>` inject) + demo Playwright screenshots (REQ-DEMO-013).

```bash
composer update nowo-tech/slide-to-confirm-bundle
php bin/console assets:install
php bin/console cache:clear
```

- **Required:** keep loading **both** `slide-to-confirm.css` and `slide-to-confirm.js` (CSS via `<link>`). Hosts that previously forked a CSP-safe JS copy can switch back to the package asset.
- Demo e2e / screenshots are maintainer tooling only.

## To 1.1.2

From **1.1.1** — REQ-CS-008 Igor FrankenPHP worker audit (igor-php require-dev, igor.json, make igor).

```bash
composer update nowo-tech/slide-to-confirm-bundle
php bin/console cache:clear
```

- No application upgrade steps for require-dev Igor tooling (REQ-CS-008). Consumers do not pull `igor-php/igor-php` transitively.


## From 1.1.0 to 1.1.1

From **1.1.0** — No application upgrade steps. Documentation and FrankenPHP worker audit only.

```bash
composer update nowo-tech/slide-to-confirm-bundle
```

FrankenPHP worker (kernel reused / `FRANKENPHP_RESET_KERNEL` unset or `0`):

- No config changes; see [FRANKENPHP-WORKER-AUDIT.md](FRANKENPHP-WORKER-AUDIT.md).
- Host subclasses of `SlideToConfirmType` must stay stateless (or implement `ResetInterface`) and must not store the current form, user or request in properties.

## From 1.0.0 to 1.1.0

Review the [CHANGELOG](CHANGELOG.md) entry. PHP **8.2+** is required.

```bash
composer update nowo-tech/slide-to-confirm-bundle
```

## 1.0.0 (2026-08-23)

First public release. There is no upgrade path from a previous tagged version.

### Host app checklist

1. **PHP / Symfony:** PHP `>=8.2 <8.6`, Symfony `^6 || ^7 || ^8`.
2. **Twig Extra:** install and enable `twig/extra-bundle` and `twig/string-extra` (REQ-TWIG-004).
3. **Assets — pick one:**
   - Standalone IIFE: `php bin/console assets:install`, then include `slide-to-confirm.css` / `slide-to-confirm.js` with the named package `nowo_slide_to_confirm` (see [USAGE.md](USAGE.md#including-the-frontend-assets)).
   - Stimulus + Vite: import the controller and CSS from the bundle sources. The Symfony 8 demo uses **Pentatrion Vite** + **pnpm** (`vite_entry_link_tags` / `vite_entry_script_tags`). Do not use npm or yarn in that stack.
4. **Form theme:** set `nowo_slide_to_confirm.form_theme` to the same Symfony layout as the host app. Do not also add `@NowoSlideToConfirmBundle/Form/slide_to_confirm_theme*.html.twig` to `twig.form_themes`.
5. **Gate profile:** `submit_on_confirm` is `false`; keep a separate submit button and enable it after `nowo-slide-to-confirm:confirmed`.
