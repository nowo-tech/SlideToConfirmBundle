# SlideToConfirmBundle demos

Demo for **Symfony 8.1** that shows **slide-to-confirm / swipe-to-submit** use cases (pay, delete, publish, legal, cancel, batch, emergency, gate). **Pentatrion Vite** + TypeScript + Stimulus. JavaScript deps: **pnpm only**.

## Quick start (Docker)

From the **bundle root**:

```bash
make up-symfony8
```

Then open http://localhost:8055.

## Demos

| Demo     | Port | Description |
|----------|------|-------------|
| symfony8 | 8055 | Symfony 8.1 + use-case forms, Pentatrion Vite + Stimulus, Web Profiler (dev) |

Locale in the URL: `/en`, `/es`. Use-case query: `?case=payment` (and `delete`, `publish`, `legal`, `cancel`, `batch`, `emergency`, `gate`).

How to reuse each case in a host app: [docs/USE-CASES.md](../docs/USE-CASES.md).
