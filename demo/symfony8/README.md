# SlideToConfirmBundle demo – Symfony 8

Demo app showing the "Select all" choice field (expanded checkboxes and multi-select). Frontend is built with **Vite** and uses the bundle’s Stimulus controller.

## Quick start

```bash
make up
# App: http://localhost:8055
```

`make up` runs Composer install, then `pnpm install` and `vite build` (with the bundle mounted at `/var/slide-to-confirm-bundle`). The app runs with `APP_ENV=dev` and `APP_DEBUG=1`; the **Web Profiler** toolbar is available at the bottom of each page.

**Language switch:** The locale is in the URL (`/en`, `/es`). Use the **Language** dropdown in the navbar to switch, or go directly to `http://localhost:8055/en` or `http://localhost:8055/es`.

## Makefile targets

- `make up` – Start container, Composer install, build assets (Vite)
- `make down` – Stop container
- `make restart` – Restart container
- `make build` – Rebuild image (no cache)
- `make install` – Composer + pnpm install and vite build
- `make assets` – Run `pnpm install && pnpm build` in container
- `make update-bundle` – Update bundle from path repo
- `make shell` – Shell in container
- `make test` – Run PHPUnit (if tests exist)
- `make cache-clear` – Clear Symfony cache (useful if you changed config/templates and didn’t restart)

**Refreshing template changes:** In dev, Twig cache is disabled and OPcache revalidates on every request, so changes to `.twig` files should appear on browser refresh. If they don’t, run `make cache-clear` or `make restart`.

## Local dev (without Docker)

From this directory:

```bash
pnpm install
pnpm build   # or pnpm dev for watch
```

The bundle is resolved as `../../assets`; in Docker, `BUNDLE_PATH=/var/slide-to-confirm-bundle/assets` is set for the build.
