#!/bin/sh
set -e


# FRANKENPHP_MODE: classic | worker (REQ-DEMO-010). Default: worker.
# Set via .env / Compose only — not baked into the image ENV.
MODE="${FRANKENPHP_MODE:-worker}"
CADDY_SRC_DIR="/app/docker/frankenphp"
CADDY_DEST="/etc/frankenphp/Caddyfile"
case "$MODE" in
	classic)
		if [ -f "$CADDY_SRC_DIR/Caddyfile.dev" ]; then
			cp "$CADDY_SRC_DIR/Caddyfile.dev" "$CADDY_DEST"
		elif [ -f /app/Caddyfile.dev ]; then
			cp /app/Caddyfile.dev "$CADDY_DEST"
		fi
		;;
	worker)
		if [ -f "$CADDY_SRC_DIR/Caddyfile" ]; then
			cp "$CADDY_SRC_DIR/Caddyfile" "$CADDY_DEST"
		elif [ -f /app/Caddyfile ]; then
			cp /app/Caddyfile "$CADDY_DEST"
		fi
		;;
	*)
		echo "Unknown FRANKENPHP_MODE=$MODE (expected classic|worker)" >&2
		exit 1
		;;
esac
echo "FrankenPHP mode: $MODE"

cd /app
mkdir -p var/cache var/log var
chmod -R 777 var 2>/dev/null || true

# Ensure .env exists so Symfony does not throw PathException (e.g. when started without make up)
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
        echo "Created .env from .env.example"
    else
        echo "APP_ENV=dev" > .env
        echo "APP_SECRET=change-me" >> .env
        echo "PORT=8055" >> .env
        echo "Created minimal .env"
    fi
fi

if [ ! -f vendor/autoload_runtime.php ]; then
    echo "Installing dependencies..."
    composer install --no-interaction
    echo "Composer install done."
fi

if [ ! -f public/build/entrypoints.json ] && [ ! -f public/build/.vite/entrypoints.json ]; then
    echo "Building frontend assets (public/build/entrypoints.json missing)..."
    if ! command -v pnpm >/dev/null 2>&1; then
        echo "ERROR: pnpm is required to build demo assets (see packageManager in package.json)." >&2
        exit 1
    fi
    pnpm install
    pnpm rebuild esbuild
    pnpm run build
    echo "Frontend assets built with pnpm (Pentatrion Vite)."
fi

# Clear Symfony cache on startup in dev so template/config changes are reflected
if [ "${APP_ENV:-}" = "dev" ] && [ -f bin/console ]; then
    php bin/console cache:clear --no-warmup 2>/dev/null || true
fi

exec frankenphp run --config "$CADDY_DEST" --adapter caddyfile
