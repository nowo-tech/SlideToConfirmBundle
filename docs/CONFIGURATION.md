# Configuration

Root alias: `nowo_slide_to_confirm`.

## Table of contents

- [Canonical YAML](#canonical-yaml)
- [Keys](#keys)
- [Built-in profiles](#built-in-profiles)
- [Form theme (Symfony layouts)](#form-theme-symfony-layouts)
- [Translations](#translations)

## Canonical YAML

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
            hint: form.hint.default
            variant: default
            threshold: 0.85
            submit_on_confirm: true
            reset_on_release: true
        danger:
            text: form.slide_to_delete
            confirmed_text: form.deleted
            hint: form.hint.danger
            variant: danger
            threshold: 0.92
            submit_on_confirm: true
            reset_on_release: true
        payment:
            text: form.slide_to_pay
            confirmed_text: form.paid
            hint: form.hint.payment
            variant: payment
            threshold: 0.9
            submit_on_confirm: true
            reset_on_release: true
        legal:
            text: form.slide_to_agree
            confirmed_text: form.agreed
            hint: form.hint.legal
            variant: legal
            threshold: 0.95
            submit_on_confirm: true
            reset_on_release: true
        publish:
            text: form.slide_to_publish
            confirmed_text: form.published
            hint: form.hint.publish
            variant: success
            threshold: 0.85
            submit_on_confirm: true
            reset_on_release: true
        gate:
            text: form.slide_to_unlock
            confirmed_text: form.unlocked
            hint: form.hint.gate
            variant: default
            threshold: 0.85
            submit_on_confirm: false
            reset_on_release: false
```

`default_profile` **must** exist as a key under `profiles` (REQ-CFG-001). Partial profile maps are merged onto the built-in set.

## Keys

| Key | Type | Default | Description |
| --- | ---- | ------- | ----------- |
| `default_profile` | string | `default` | Profile used when the form option `profile` is omitted. |
| `profiles` | map | built-in set | Complete settings blocks per name. |
| `form_theme` | string | `form_div_layout.html.twig` | Symfony layout the bundle theme `{% use %}`s. |
| `translation_domain` | string | `NowoSlideToConfirmBundle` | Domain for slider strings. |
| `debug` | bool | `false` | Frontend console debug logs. |

Per-profile keys: `text`, `confirmed_text`, `hint`, `variant` (`default` \| `danger` \| `success` \| `payment` \| `legal`), `threshold` (`0.5`–`1.0`), `submit_on_confirm`, `reset_on_release`.

## Built-in profiles

See [USAGE.md](USAGE.md#named-profiles) for when to pick each profile.

## Form theme (Symfony layouts)

Set `form_theme` to the **same** layout your app uses. The extension prepends the matching bundle theme:

| `form_theme` | Bundle theme |
| ------------ | ------------ |
| `form_div_layout.html.twig` | `slide_to_confirm_theme.html.twig` |
| `form_table_layout.html.twig` | `slide_to_confirm_theme_table.html.twig` |
| `bootstrap_5_layout.html.twig` | `slide_to_confirm_theme_bootstrap5.html.twig` |
| `bootstrap_5_horizontal_layout.html.twig` | `slide_to_confirm_theme_bootstrap5_horizontal.html.twig` |
| `bootstrap_4_layout.html.twig` | `slide_to_confirm_theme_bootstrap4.html.twig` |
| `bootstrap_4_horizontal_layout.html.twig` | `slide_to_confirm_theme_bootstrap4_horizontal.html.twig` |
| `bootstrap_3_layout.html.twig` | `slide_to_confirm_theme_bootstrap3.html.twig` |
| `bootstrap_3_horizontal_layout.html.twig` | `slide_to_confirm_theme_bootstrap3_horizontal.html.twig` |
| `foundation_5_layout.html.twig` | `slide_to_confirm_theme_foundation5.html.twig` |
| `foundation_6_layout.html.twig` | `slide_to_confirm_theme_foundation6.html.twig` |
| `tailwind_2_layout.html.twig` | `slide_to_confirm_theme_tailwind2.html.twig` |

Do **not** also list `@NowoSlideToConfirmBundle/Form/slide_to_confirm_theme*.html.twig` in `twig.form_themes`.

## Translations

Domain **`NowoSlideToConfirmBundle`**. Required locales (REQ-I18N-002): `en`, `es`, `it`, `fr`, `pt`, `de`, `nl`.

Override in the host app with files of the same domain, or pass `text` / `hint` per field.
