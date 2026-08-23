# Demo applications with FrankenPHP (development and production)

This document describes how the bundle’s demo applications run under **FrankenPHP** in Docker, and how to reproduce **development** (no cache, changes visible on refresh) and **production** (worker mode, cache enabled) configurations. The same approach can be used in other Symfony bundles or applications that ship a FrankenPHP-based demo.

## Contents

- [Overview](#overview)
- [What the demos include](#what-the-demos-include)
- [Development configuration](#development-configuration)
- [Production configuration](#production-configuration)
- [Switching classic vs worker (`FRANKENPHP_MODE`)](#switching-classic-vs-worker-frankenphp_mode)
- [Reproducing in another bundle](#reproducing-in-another-bundle)
- [Troubleshooting](#troubleshooting)

---

## Overview

**The `demo/` folder is not shipped when the bundle is installed** (e.g. via `composer require nowo-tech/slide-to-confirm-bundle`). It is excluded from the Composer package (via `archive.exclude` in the bundle’s `composer.json`). The demo applications exist only in the bundle’s source repository and are intended for development, testing, and documentation. To run or modify the demos, use a clone of the bundle repository.

The demos use:

- **FrankenPHP** (Caddy + PHP) in a single container.
- **Docker Compose** with the app and (optionally) the parent bundle mounted as volumes.
- A **Caddyfile** for the HTTP server and PHP integration.
- An **entrypoint** script that prepares the app (Composer, cache) and starts FrankenPHP.

The main difference between development and production is:

| Aspect | Development | Production |
|--------|-------------|------------|
| FrankenPHP worker mode | **Off** (one PHP process per request) | **On** (workers keep app in memory) |
| Twig cache | **Off** | **On** (default) |
| OPcache revalidation | Every request (`revalidate_freq=0`) | Default (e.g. 2 seconds) |
| HTTP cache headers | `no-store`, `no-cache` | Omitted or cache-friendly |
| Symfony cache on startup | Cleared | Not cleared (or warmup only) |
| `APP_ENV` / `APP_DEBUG` | `dev` / `1` | `prod` / `0` |

### This repository’s demo (`demo/symfony8`)

The bundled demos ship **two** Caddyfiles:

- `docker/frankenphp/Caddyfile` — **worker** mode (`php_server` + `worker { file; watch }`) for production-like `FRANKENPHP_MODE=worker` (default in `.env.example`).
- `docker/frankenphp/Caddyfile.dev` — **classic** mode (no worker) so each request is a fresh PHP process.

`docker/entrypoint.sh` copies the matching file to `/etc/frankenphp/Caddyfile` from `FRANKENPHP_MODE`. Use `classic` for comfortable Twig/asset refresh; use `worker` to exercise FrankenPHP workers.

---

## What the demos include

The demo applications are configured for **local development and debugging**:

- **Symfony Web Profiler** (`Symfony\Bundle\WebProfilerBundle\WebProfilerBundle`) — enabled in `dev` and `test` environments. Provides the debug toolbar and profiler at the bottom of each page.
- **Nowo Twig Inspector** (`nowo-tech/twig-inspector-bundle`) and **Nowo Hot Reload** (`nowo-tech/hot-reload-bundle`) — required together on FrankenPHP demos (dev/test only; Caddyfile Mercure + `hot_reload`, plus `worker { watch }` in worker mode). Do not enable Hot Reload in production.
- **Symfony Debug bundle** (`Symfony\Bundle\DebugBundle\DebugBundle`) — enabled in `dev` and `test`. Required for the profiler and improved error pages.
- **Nowo Twig Inspector** (`Nowo\TwigInspectorBundle\NowoTwigInspectorBundle`) — enabled in `dev` and `test`. Allows inspecting which Twig template and block produced each part of the HTML (e.g. via comments in the output or a browser overlay).

Example `config/bundles.php` (aligned with `demo/symfony8`):

```php
<?php

declare(strict_types=1);

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class       => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class                 => ['all' => true],
    Symfony\Bundle\DebugBundle\DebugBundle::class               => ['dev' => true, 'test' => true],
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class   => ['dev' => true, 'test' => true],
    Symfony\UX\StimulusBundle\StimulusBundle::class             => ['all' => true],
    Nowo\SlideToConfirmBundle\NowoSlideToConfirmBundle::class => ['all' => true],
    Nowo\TwigInspectorBundle\NowoTwigInspectorBundle::class     => ['dev' => true, 'test' => true],
];
```

In **production** (`APP_ENV=prod`), only bundles registered for `all` or `prod` are loaded, so Web Profiler, Debug and Twig Inspector are not active.

---

## Development configuration

Goal: every change to PHP, Twig or config is visible on the next browser refresh without restarting the container. No long-lived PHP workers; cache disabled or revalidated on every request.

### 1. Caddyfile (development)

Do **not** use FrankenPHP worker mode. Use plain `php_server` so each HTTP request is handled by a new PHP process. Add cache-busting headers so the browser does not serve a cached response.

File: `docker/frankenphp/Caddyfile`

```caddyfile
{
    skip_install_trust
}

:80 {
    root * /app/public
    encode zstd br gzip
    # Disable cache in development so changes are reflected immediately
    header Cache-Control "no-store, no-cache, must-revalidate, max-age=0"
    header Pragma "no-cache"
    # No worker mode: each request runs in a fresh PHP process so Twig/template changes are visible on refresh
    php_server
}
```

Important: there must be **no** `worker` directive inside `php_server`. If you use `worker /app/public/index.php 2`, PHP runs in long-lived workers and template changes will not appear until workers are restarted.

### 2. PHP configuration (development)

Force OPcache to recheck file modification time on every request so that recompiled Twig templates in `var/cache` are picked up immediately.

File: `docker/php-dev.ini`

```ini
; Recheck compiled PHP files every request so Twig-compiled templates in var/cache are always fresh
opcache.revalidate_freq=0
```

This file is mounted into the container (see step 4). Do **not** use this in production if you want normal OPcache behaviour.

### 3. Twig configuration (development only)

Disable Twig’s compiled template cache in the dev environment so Twig recompiles from source on each request.

File: `config/packages/dev/twig.yaml`

```yaml
# Disable Twig cache in dev so template changes are visible on refresh
twig:
  cache: false
```

This file is loaded only when `APP_ENV=dev`. Do not add `cache: false` in `config/packages/twig.yaml` for all environments unless you really want no Twig cache everywhere.

### 4. Docker Compose (development)

Mount the Caddyfile and the PHP dev ini so you can change them without rebuilding the image. Set `APP_ENV=dev` and `APP_DEBUG=1`.

Example: `docker-compose.yml`

```yaml
name: my-bundle-demo

services:
  php:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: my-demo-php
    working_dir: /app
    restart: unless-stopped
    volumes:
      - .:/app
      # If the demo lives inside a bundle repo, mount the bundle root for path repos
      # - ../..:/var/my-bundle
      - ./docker/frankenphp/Caddyfile:/etc/frankenphp/Caddyfile:ro
      - ./docker/php-dev.ini:/usr/local/etc/php/conf.d/99-dev.ini:ro
      - composer-cache:/root/.composer/cache
    environment:
      - COMPOSER_ALLOW_SUPERUSER=1
      - APP_ENV=dev
      - APP_DEBUG=1
      - APP_SECRET=change-me
    ports:
      - "${PORT:-8055}:80"
    networks:
      - demo-network

volumes:
  composer-cache:
    driver: local

networks:
  demo-network:
    driver: bridge
```

Notes:

- `./docker/frankenphp/Caddyfile` — use the development Caddyfile from the repo (no worker, no-cache headers).
- `./docker/php-dev.ini` — must exist and contain `opcache.revalidate_freq=0` as above.
- `APP_ENV=dev` and `APP_DEBUG=1` ensure the dev front controller, Web Profiler, and Twig Inspector are used.

### 5. Entrypoint (development-friendly)

Clear the Symfony cache on startup when in dev so the first request after a restart does not use stale cache.

File: `docker/entrypoint.sh`

```sh
#!/bin/sh
set -e

cd /app
mkdir -p var/cache var/log var
chmod -R 777 var 2>/dev/null || true

# Ensure .env exists
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
    else
        echo "APP_ENV=dev" > .env
        echo "APP_SECRET=change-me" >> .env
    fi
fi

if [ ! -f vendor/autoload_runtime.php ]; then
    composer install --no-interaction
fi

# Clear Symfony cache on startup in dev so template/config changes are reflected
if [ "${APP_ENV:-}" = "dev" ] && [ -f bin/console ]; then
    php bin/console cache:clear --no-warmup 2>/dev/null || true
fi

exec frankenphp run --config /etc/frankenphp/Caddyfile --adapter caddyfile
```

### 6. Start the demo (development)

```bash
docker compose up -d
# Optional: install deps and build assets if your Makefile does that
# make up
```

After editing a Twig template or PHP file, refresh the browser; changes should appear without restarting the container.

---

## Production configuration

Goal: maximum performance with worker mode and caching. No cache-busting headers; Twig and OPcache use their default caching behaviour.

### 1. Caddyfile (production)

Enable FrankenPHP worker mode so the application is booted once per worker and kept in memory. Omit the no-cache headers (or set cache-friendly ones if you want).

File: `docker/frankenphp/Caddyfile` (production variant)

```caddyfile
{
    skip_install_trust
}

:80 {
    root * /app/public
    encode zstd br gzip
    php_server {
        worker /app/public/index.php 2
    }
}
```

- `worker /app/public/index.php 2` — run 2 worker processes. Increase the number (e.g. `4`) if you have more CPU cores and more traffic.
- Do **not** add `header Cache-Control "no-store"` etc. in production unless you explicitly want to disable HTTP caching.

### 2. PHP configuration (production)

Either do not mount a custom `php-dev.ini`, or use a production ini that does **not** set `opcache.revalidate_freq=0`. Rely on the image’s default OPcache settings (e.g. `opcache.revalidate_freq=2`).

If you use a single `docker-compose.yml` for both dev and prod, you can:

- In production, **do not** mount `php-dev.ini`, so the container uses only the image’s default PHP config; or
- Mount a different file, e.g. `docker/php-prod.ini`, that does not override `opcache.revalidate_freq`.

Example `docker/php-prod.ini` (optional, only if you need specific production values):

```ini
; Production: use default OPcache behaviour (do not set revalidate_freq=0)
; opcache.enable=1
; opcache.revalidate_freq=2
```

Or simply omit any custom OPcache ini in production.

### 3. Twig configuration (production)

Do **not** create or enable `config/packages/dev/twig.yaml` for production. In `APP_ENV=prod`, Twig uses the default cache (e.g. `var/cache/prod/twig`). No extra config is required.

### 4. Docker Compose (production)

Use a production Caddyfile (with worker), do **not** mount `php-dev.ini`, and set `APP_ENV=prod` and `APP_DEBUG=0`.

Example: `docker-compose.prod.yml` (or override)

```yaml
name: my-bundle-demo-prod

services:
  php:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: my-demo-php
    working_dir: /app
    restart: unless-stopped
    volumes:
      - .:/app
      # Mount the *production* Caddyfile (with worker)
      - ./docker/frankenphp/Caddyfile.prod:/etc/frankenphp/Caddyfile:ro
      # Do NOT mount php-dev.ini in production
      - composer-cache:/root/.composer/cache
    environment:
      - COMPOSER_ALLOW_SUPERUSER=1
      - APP_ENV=prod
      - APP_DEBUG=0
      - APP_SECRET=your-production-secret
    ports:
      - "${PORT:-8055}:80"
    networks:
      - demo-network

volumes:
  composer-cache:
    driver: local

networks:
  demo-network:
    driver: bridge
```

Store the production Caddyfile as `docker/frankenphp/Caddyfile.prod` with the `worker` directive as in [Caddyfile (production)](#1-caddyfile-production) and mount it over `/etc/frankenphp/Caddyfile`.

### 5. Entrypoint (production)

The same entrypoint can be used. The line that clears cache only runs when `APP_ENV=dev`, so in production the cache is not cleared on startup. Optionally, in production you can run `cache:warmup` instead of (or in addition to) not clearing:

```sh
if [ "${APP_ENV:-}" = "prod" ] && [ -f bin/console ]; then
    php bin/console cache:warmup --env=prod 2>/dev/null || true
fi
```

### 6. Build and run (production)

```bash
# Build the image (no cache if you need a clean build)
docker compose -f docker-compose.prod.yml build

# Run
docker compose -f docker-compose.prod.yml up -d
```

Ensure the application is installed (e.g. `composer install --no-dev`) and the cache is warmed (e.g. in the Dockerfile or entrypoint) so the first request is fast.

---

## Switching classic vs worker (`FRANKENPHP_MODE`)

- **Development:** Use the development Caddyfile (no `worker`), mount `php-dev.ini`, set `APP_ENV=dev` and `APP_DEBUG=1`. Use `config/packages/dev/twig.yaml` with `cache: false`.
- **Production:** Use the production Caddyfile (with `worker`), do not mount `php-dev.ini`, set `APP_ENV=prod` and `APP_DEBUG=0`. Rely on default Twig cache.

You can:

1. Keep two Caddyfiles (e.g. `Caddyfile` for dev and `Caddyfile.prod` for prod) and mount the correct one via Compose.
2. Or use two Compose files (e.g. `docker-compose.yml` for dev and `docker-compose.prod.yml` for prod) that differ by env vars and mounted files.

After changing the Caddyfile or env, restart the container so FrankenPHP and the app pick up the new config:

```bash
docker compose restart
```

---

## Reproducing in another bundle

To replicate this setup in another bundle or app:

1. **Dockerfile** — base image `dunglas/frankenphp:1-php8.4-alpine` (or your PHP version), install Composer and any extensions, copy the **default** Caddyfile and entrypoint into the image.
2. **Two Caddyfiles** — `docker/frankenphp/Caddyfile` (dev: `php_server` only, no-cache headers) and `docker/frankenphp/Caddyfile.prod` (prod: `php_server { worker ... }`, no no-cache headers).
3. **Entrypoint** — create `var/cache`, optional `.env` from `.env.example`, `composer install` if needed, and in dev run `cache:clear --no-warmup`. Then `exec frankenphp run --config /etc/frankenphp/Caddyfile --adapter caddyfile`.
4. **Dev-only files** — `docker/php-dev.ini` with `opcache.revalidate_freq=0`; `config/packages/dev/twig.yaml` with `twig.cache: false`.
5. **Compose** — dev: mount Caddyfile and `php-dev.ini`, set `APP_ENV=dev`, `APP_DEBUG=1`. Prod: mount `Caddyfile.prod`, do not mount `php-dev.ini`, set `APP_ENV=prod`, `APP_DEBUG=0`.
6. **Bundles** — enable `WebProfilerBundle`, `DebugBundle`, and optionally `NowoTwigInspectorBundle` only for `dev` and `test` in `bundles.php`.

This gives you a reproducible development setup (changes visible on refresh) and a production-ready setup (workers + cache).

---

## Troubleshooting

### Changes to Twig or PHP do not appear on refresh

- Ensure **worker mode is off** in the Caddyfile you are using (no `worker` inside `php_server`).
- Ensure `config/packages/dev/twig.yaml` exists and sets `twig.cache: false`.
- Ensure `docker/php-dev.ini` exists and is mounted, with `opcache.revalidate_freq=0`.
- Restart the container after changing the Caddyfile or mounted ini: `docker compose restart`.
- Hard-refresh the browser (e.g. Ctrl+Shift+R) or try a private window.

### Web Profiler or Twig Inspector not visible

- Check `APP_ENV=dev` and `APP_DEBUG=1` in the container environment.
- Ensure `WebProfilerBundle`, `DebugBundle` and (if used) `NowoTwigInspectorBundle` are enabled for `dev` in `config/bundles.php`.
- Clear the Symfony cache: `docker compose exec php php bin/console cache:clear`.

### Production: slow first request or “cache cold”

- Warm the cache in the Dockerfile or entrypoint: `php bin/console cache:warmup --env=prod`.
- Ensure OPcache is enabled and not forced to revalidate every request (do not use `php-dev.ini` in production).

### Caddyfile changes have no effect

- The Caddyfile is read when FrankenPHP starts. Restart the container: `docker compose restart`.
- If the Caddyfile is copied in the Dockerfile and not mounted, rebuild the image or switch to mounting it (as in the examples above).
