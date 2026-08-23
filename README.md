# Slide To Confirm Bundle

[![CI](https://github.com/nowo-tech/SlideToConfirmBundle/actions/workflows/ci.yml/badge.svg)](https://github.com/nowo-tech/SlideToConfirmBundle/actions/workflows/ci.yml) [![Packagist Version](https://img.shields.io/packagist/v/nowo-tech/slide-to-confirm-bundle.svg?style=flat)](https://packagist.org/packages/nowo-tech/slide-to-confirm-bundle) [![Packagist Downloads](https://img.shields.io/packagist/dt/nowo-tech/slide-to-confirm-bundle.svg)](https://packagist.org/packages/nowo-tech/slide-to-confirm-bundle) [![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE) [![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4?logo=php)](https://php.net) [![Symfony](https://img.shields.io/badge/Symfony-6.0%2B%20%7C%207.4%2B%20%7C%208.0%20%7C%208.1%2B-000000?logo=symfony)](https://symfony.com) [![GitHub stars](https://img.shields.io/github/stars/nowo-tech/slide-to-confirm-bundle.svg?style=social&label=Star)](https://github.com/nowo-tech/SlideToConfirmBundle) [![Coverage](https://img.shields.io/badge/Coverage-100%25-brightgreen)](#tests-and-coverage)

> ⭐ **Found this useful?** Give it a **star** on [GitHub](https://github.com/nowo-tech/SlideToConfirmBundle) so more developers can find it.

**Symfony FormType for slide-to-confirm / swipe-to-submit** — a submit control that must be dragged to the end of a track before the form is posted. Same family as PhoneInput, PasswordToggle, and SelectAllChoice: a drop-in field type, Twig themes, Stimulus or a standalone script. For **Symfony 6, 7 and 8** · PHP 8.1+.

![FrankenPHP Friendly Worker Mode](docs/images/frankenphp-friendly.png)

This bundle is **FrankenPHP worker mode friendly**.

## Table of contents

- [Quick search terms](#quick-search-terms)
- [Features](#features)
- [Installation](#installation)
- [Requirements](#requirements)
- [Configuration](#configuration)
- [Usage](#usage)
- [Demo](#demo)
- [Development](#development)
- [Documentation](#documentation)
- [Tests and coverage](#tests-and-coverage)
- [License](#license)
- [Author](#author)

## Quick search terms

Looking for **Symfony slide to confirm**, **swipe to submit form type**, **slide to pay Symfony**, **slide to delete account**, **iOS slide to unlock form**, **Stimulus swipe submit**, **SlideToConfirmType**, **SwipeToSubmitType**? You're in the right place.

## Features

- ✅ **`SlideToConfirmType`** — checkbox-backed slider; completing the swipe confirms and optionally submits
- ✅ **`SwipeToSubmitType`** — semantic alias for “this field is the submit”
- ✅ **Named profiles** — `default`, `payment`, `danger`, `legal`, `publish`, `gate` (REQ-CFG-001)
- ✅ **Use cases** — payments, irreversible deletes, publish/go-live, legal consent, subscription cancel, batch approve, emergency logout, gate-then-submit (see [docs/USAGE.md](docs/USAGE.md#use-cases))
- ✅ **Keyboard and RTL** — slider role, arrows, Home/End, pointer + touch
- ✅ **Track fill** — the travelled portion of the track uses the same colour as the thumb
- ✅ **Server validation** — required fields add `IsTrue`; an incomplete POST is invalid
- ✅ **Works with or without Stimulus** — built IIFE + MutationObserver, or a Stimulus controller
- ✅ **TypeScript + Vite + pnpm** — bundle IIFE is built with Vite; the Symfony 8 demo uses **Pentatrion Vite** (`pentatrion/vite-bundle` + `vite-plugin-symfony`) and **pnpm only**
- ✅ Compatible with **Symfony 7 and 8** and **FrankenPHP**

## Installation

```bash
composer require nowo-tech/slide-to-confirm-bundle
```

**1. Register the bundle** in `config/bundles.php`:

```php
<?php

return [
  // ...
  Nowo\SlideToConfirmBundle\NowoSlideToConfirmBundle::class => ['all' => true],
];
```

**2. Form theme**: The bundle **automatically** adds its form theme from the `form_theme` option (see Configuration). Set `form_theme` in `config/packages/nowo_slide_to_confirm.yaml` to match your app (e.g. `bootstrap_5_layout.html.twig`).

**3. Include the frontend assets** using the named asset package:

```twig
<link rel="stylesheet" href="{{ asset(nowo_slide_to_confirm_asset_path('slide-to-confirm.css'), nowo_slide_to_confirm_asset_package()) }}">
<script src="{{ asset(nowo_slide_to_confirm_asset_path('slide-to-confirm.js'), nowo_slide_to_confirm_asset_package()) }}" defer></script>
```

**4. (Optional) Translations** — domain `NowoSlideToConfirmBundle` with the seven required locales (`en`, `es`, `it`, `fr`, `pt`, `de`, `nl`).

Full steps: [docs/INSTALLATION.md](docs/INSTALLATION.md).

## Requirements

- PHP >= 8.1
- **Symfony 6, 7 or 8** (`^6.0 || ^7.0 || ^8.0`), including the mandatory floor **7.4**, **8.0**, and **8.1**
- **Stimulus** optional — use the built script, or register the controller
- **Vite** to rebuild assets (or use the pre-built files in `src/Resources/public`)

## Configuration

```yaml
nowo_slide_to_confirm:
  default_profile: default
  form_theme: 'form_div_layout.html.twig'
  translation_domain: NowoSlideToConfirmBundle
  debug: false
  profiles:
    default:
      text: form.slide_to_confirm
      confirmed_text: form.confirmed
      variant: default
      threshold: 0.85
      submit_on_confirm: true
      reset_on_release: true
```

See [docs/CONFIGURATION.md](docs/CONFIGURATION.md) for built-in profiles and Symfony form themes.

## Usage

```php
use Nowo\SlideToConfirmBundle\Form\Type\SlideToConfirmType;

$builder
    ->add('amount', MoneyType::class)
    ->add('confirm', SlideToConfirmType::class, [
        'profile' => 'payment',
    ]);
```

Use cases (pay, delete, publish, legal, cancel, batch, emergency, gate): [docs/USAGE.md](docs/USAGE.md#use-cases).

## Demo

The Symfony 8.1 demo is in `demo/symfony8`. Run from the bundle root: `make up-symfony8` (http://localhost:8055). See [demo/README.md](demo/README.md).

The demos use **FrankenPHP**. Default `FRANKENPHP_MODE=worker` (Caddyfile with `worker { file; watch }`). Set `FRANKENPHP_MODE=classic` for per-request PHP so Twig/asset changes show on refresh (see [docs/DEMO-FRANKENPHP.md](docs/DEMO-FRANKENPHP.md)).

## Development

Run tests and QA with Docker: `make up && make install && make test` (or `make test-coverage`, `make qa`). Without Docker: `composer install && composer test`. See [Makefile](Makefile) for all targets.

## Documentation

- [Installation](docs/INSTALLATION.md)
- [Configuration](docs/CONFIGURATION.md)
- [Usage](docs/USAGE.md)
- [Contributing](docs/CONTRIBUTING.md)
- [Code of Conduct](CODE_OF_CONDUCT.md)
- [Changelog](docs/CHANGELOG.md)
- [Upgrading](docs/UPGRADING.md)
- [Release](docs/RELEASE.md)
- [Security](docs/SECURITY.md)
- [Engram](docs/ENGRAM.md)
- [Spec-driven development](docs/SPEC-DRIVEN-DEVELOPMENT.md)
- [GitHub Spec Kit](docs/SPEC-KIT.md)

### Additional documentation

- [GitHub Actions CI requirements](docs/GITHUB_CI.md)
- [PSR evaluation (REQ-CS-007)](docs/PSR.md)
- [Theming](docs/THEMING.md) — CSS tokens, form themes, template overrides
- [Demo with FrankenPHP](docs/DEMO-FRANKENPHP.md)
- [GitHub About fields](docs/GITHUB.md)

## Tests and coverage

- Tests: PHPUnit (PHP), Vitest (TS/JS)
- PHP: 100%
- TS/JS: 100%

## License

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.

## Author

Created by [Nowo.tech](https://nowo.tech)
