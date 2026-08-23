# SlideToConfirmBundle demo — Symfony 8.1

Requires **PHP 8.4+**. Pins Symfony **8.1.\*** (latest stable 8.x).

Demo app showing slide-to-confirm / swipe-to-submit use cases. Front-end uses **[Pentatrion Vite](https://symfony-vite.pentatrion.com/)** (`pentatrion/vite-bundle` + `vite-plugin-symfony`) and the bundle’s Stimulus controller. **pnpm only** (`npm` / `yarn` are rejected).

## Quick start

```bash
make up
# App: http://localhost:8055
```

`make up` runs Composer install, then `pnpm install` and `pnpm run build`. Twig loads the Vite entry with `vite_entry_link_tags('app')` / `vite_entry_script_tags('app')`. The bundle sources are resolved via the `@bundle` Vite alias (`../../src/Resources/assets` on the host, `/var/slide-to-confirm-bundle/src/Resources/assets` in Docker).

**Language switch:** The locale is in the URL (`/en`, `/es`). Use the **Language** dropdown in the navbar to switch, or go directly to `http://localhost:8055/en` or `http://localhost:8055/es`.

## Makefile targets

- `make up` – Start container, Composer install, build assets (Vite)
- `make down` – Stop container
- `make restart` – Restart container
- `make build` – Rebuild image (no cache)
- `make install` – Composer + pnpm install and vite build
- `make assets` – Run `pnpm install && pnpm run build` in the container (Pentatrion Vite)
- `make update-bundle` – Update bundle from path repo
- `make shell` – Shell in container
- `make test` – Run PHPUnit (if tests exist)
- `make cache-clear` – Clear Symfony cache (useful if you changed config/templates and didn’t restart)

**Refreshing template changes:** In dev, Twig cache is disabled and OPcache revalidates on every request, so changes to `.twig` files should appear on browser refresh. If they don’t, run `make cache-clear` or `make restart`.

## Local dev (without Docker)

From this directory:

```bash
pnpm install
pnpm run build   # or pnpm dev for the Vite dev server
```

The bundle is resolved as `../../src/Resources/assets` (`@bundle`). Do not use `npm` or `yarn`.
