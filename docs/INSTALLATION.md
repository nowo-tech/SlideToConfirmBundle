# Installation

This guide covers installing SlideToConfirmBundle in a Symfony 6, 7, or 8 application.

## Requirements

- **PHP** `>=8.1 <8.6`
- **Symfony** `^6.0 || ^7.0 || ^8.0`
- **symfony/form**, **symfony/twig-bundle**, **symfony/translation**, **symfony/validator**
- **Stimulus** optional (`symfony/stimulus-bundle` or `@hotwired/stimulus`)
- **Vite + pnpm** to rebuild the bundle IIFE, or **Pentatrion Vite** (`pentatrion/vite-bundle` + `vite-plugin-symfony`) if the host app compiles the Stimulus controller (that is what the demo uses)

The Symfony 8.1 demo additionally needs **PHP 8.4+**.

## Install with Composer

```bash
composer require nowo-tech/slide-to-confirm-bundle
```

## Register the bundle

### With Symfony Flex

This bundle ships a Flex recipe in `.symfony/recipe/`. When the recipe is available, Flex registers the bundle and copies `config/packages/nowo_slide_to_confirm.yaml`.

### Manual registration

1. **Register the bundle** in `config/bundles.php`:

```php
<?php

return [
    // ...
    Nowo\SlideToConfirmBundle\NowoSlideToConfirmBundle::class => ['all' => true],
];
```

2. **Form theme**: The bundle prepends its theme from `nowo_slide_to_confirm.form_theme`. To use Bootstrap 5:

```yaml
# config/packages/nowo_slide_to_confirm.yaml
nowo_slide_to_confirm:
    form_theme: 'bootstrap_5_layout.html.twig'

# config/packages/twig.yaml
twig:
    form_themes:
        - 'bootstrap_5_layout.html.twig'
```

3. **Assets**: `php bin/console assets:install`, then include CSS + JS (see [USAGE.md](USAGE.md#including-the-frontend-assets)) or register `slide-to-confirm` in Stimulus.

4. **Optional configuration**: [CONFIGURATION.md](CONFIGURATION.md).

## Twig Extra Bundle (REQ-TWIG-004)

Host applications **must** install and enable Twig Extra:

```bash
composer require twig/extra-bundle twig/string-extra
```

Register `Twig\Extra\TwigExtraBundle\TwigExtraBundle`. Demos already include it. `make check-twig-extra` guards this contract.
