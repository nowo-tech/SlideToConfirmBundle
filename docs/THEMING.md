# Theming

The slider uses BEM classes under `nowo-slide-to-confirm` and CSS custom properties:

| Token | Role |
| ----- | ---- |
| `--nowo-slide-track-bg` | Track background |
| `--nowo-slide-thumb-bg` | Thumb fill |
| `--nowo-slide-text` | Track label color |
| `--nowo-slide-confirmed-bg` | Track after confirm |

Variants (`--danger`, `--payment`, `--legal`, `--success`) override thumb/confirmed colors.

## Overriding bundle template files

Copy to the host app:

```text
templates/bundles/NowoSlideToConfirmBundle/Form/_slide_to_confirm_widget.html.twig
```

A file at that override path **always wins** and **will not pick up vendor changes** for that path until you delete or merge it (REQ-TWIG-001 freeze rule). Prefer CSS tokens, `form_theme`, and profile options when you still want upstream widget fixes on `composer update`.

Keep `data-slide-to-confirm-target` and `data-controller="slide-to-confirm"` so the script can bind.

## Form themes

Set `nowo_slide_to_confirm.form_theme` to your Symfony layout. See [CONFIGURATION.md](CONFIGURATION.md#form-theme-symfony-layouts).
