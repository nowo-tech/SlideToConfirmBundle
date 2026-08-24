# Upgrading

## Table of contents


- [From 1.0.0 to 1.1.0](#from-100-to-110)
- [Unreleased](#unreleased)
- [1.0.0 (2026-08-23)](#100-2026-08-23)

## From 1.0.0 to 1.1.0

Review the [CHANGELOG](CHANGELOG.md) entry. PHP **8.2+** may now be required.

```bash
composer update nowo-tech/slide-to-confirm-bundle
```

## From 1.0.0 to 1.1.0

Review the [CHANGELOG](CHANGELOG.md) entry. PHP **8.2+** may now be required.

```bash
composer update nowo-tech/slide-to-confirm-bundle
```

## Unreleased

No upgrade notes yet.

## 1.0.0 (2026-08-23)

First public release. There is no upgrade path from a previous tagged version.

### Host app checklist

1. **PHP / Symfony:** PHP `>=8.1 <8.6`, Symfony `^6 || ^7 || ^8`.
2. **Twig Extra:** install and enable `twig/extra-bundle` and `twig/string-extra` (REQ-TWIG-004).
3. **Assets — pick one:**
   - Standalone IIFE: `php bin/console assets:install`, then include `slide-to-confirm.css` / `slide-to-confirm.js` with the named package `nowo_slide_to_confirm` (see [USAGE.md](USAGE.md#including-the-frontend-assets)).
   - Stimulus + Vite: import the controller and CSS from the bundle sources. The Symfony 8 demo uses **Pentatrion Vite** + **pnpm** (`vite_entry_link_tags` / `vite_entry_script_tags`). Do not use npm or yarn in that stack.
4. **Form theme:** set `nowo_slide_to_confirm.form_theme` to the same Symfony layout as the host app. Do not also add `@NowoSlideToConfirmBundle/Form/slide_to_confirm_theme*.html.twig` to `twig.form_themes`.
5. **Gate profile:** `submit_on_confirm` is `false`; keep a separate submit button and enable it after `nowo-slide-to-confirm:confirmed`.
