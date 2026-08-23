# SlideToConfirmBundle demos

Demo for **Symfony 8** that shows **slide-to-confirm / swipe-to-submit** use cases (pay, delete, publish, legal, cancel, batch, emergency, gate). Vite + TypeScript + Stimulus.

## Quick start (Docker)

From the **bundle root**:

```bash
make up-symfony8
```

Then open http://localhost:8055.

## Demos

| Demo     | Port | Description |
|----------|------|-------------|
| symfony8 | 8055 | Symfony 8 + use-case forms, Vite + Stimulus, Web Profiler (dev) |

Locale in the URL: `/en`, `/es`. Use-case query: `?case=payment` (and `delete`, `publish`, `legal`, `cancel`, `batch`, `emergency`, `gate`).
