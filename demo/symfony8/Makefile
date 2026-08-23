# Makefile for SlideToConfirmBundle Demo - Symfony 8.1
# Prefer Compose V2; absolute docker path avoids shadowing by local docker/ when PATH has "." (REQ-MAKE-010).
DOCKER_BIN := $(shell command -v docker 2>/dev/null)
COMPOSE_FILE := docker-compose.yml
ifeq ($(DOCKER_BIN),)
COMPOSE_BIN := docker-compose
else
COMPOSE_BIN := $(shell $(DOCKER_BIN) compose version >/dev/null 2>&1 && echo "$(DOCKER_BIN) compose" || echo "docker-compose")
endif
COMPOSE     := $(COMPOSE_BIN) -f $(COMPOSE_FILE)
SERVICE_PHP := php

# Pin the same pnpm as package.json "packageManager" (global latest pnpm 10+ rejects this lockfile's identity).
PNPM := npx --yes pnpm@10.15.0

.PHONY: help up down restart build install shell logs cache-clear update-bundle ensure-up ensure-install test test-coverage assets verify

help:
	@echo "SlideToConfirmBundle Demo - Symfony 8.1"
	@echo "  make up            Start container, install deps (Composer + pnpm) and build assets (http://localhost:$${PORT:-8055})"
	@echo "  make down          Stop container"
	@echo "  make restart       Restart container"
	@echo "  make build         Build image (no cache)"
	@echo "  make install       Install dependencies (Composer + pnpm + vite build)"
	@echo "  make ensure-up     Start container if needed (and install deps + assets on first start)"
	@echo "  make ensure-install ensure-up + composer install + assets"
	@echo "  make assets        pnpm install + Vite build (Pentatrion; needs ensure-up)"
	@echo "  make update-bundle Update bundle from path repo"
	@echo "  make shell         Open shell in PHP container"
	@echo "  make logs          Show container logs"
	@echo "  make test          Run tests"
	@echo "  make test-coverage Run tests with coverage"
	@echo "  make verify        Ensure up and check HTTP 200"
	@echo ""

up:
	@if [ ! -f .env ]; then cp .env.example .env 2>/dev/null || (echo "APP_ENV=dev" > .env; echo "APP_DEBUG=1" >> .env; echo "APP_SECRET=change-me" >> .env; echo "PORT=8055" >> .env); fi
	$(COMPOSE) up -d
	@echo "Waiting for container to be ready..."
	@sleep 5
	@echo "Installing dependencies..."
	@$(COMPOSE) exec -T -e COMPOSER_MEMORY_LIMIT=-1 $(SERVICE_PHP) composer install --no-interaction
	@$(MAKE) assets
	@PORT=$$(grep "^PORT=" .env 2>/dev/null | cut -d= -f2 | tr -d '\r'); \
	[ -z "$$PORT" ] && PORT=$$(grep "^PORT=" .env.example 2>/dev/null | cut -d= -f2 | tr -d '\r'); \
	echo "Demo started at: http://localhost:$$PORT"
down:
	$(COMPOSE) down

restart: ensure-up
	$(COMPOSE) restart

build:
	$(COMPOSE) build --no-cache

ensure-up:
	@if ! $(COMPOSE) exec -T $(SERVICE_PHP) true 2>/dev/null; then \
		echo "Container not ready (or restarting). Stopping and starting fresh..."; \
		$(COMPOSE) down 2>/dev/null; \
		$(COMPOSE) up -d; \
		sleep 5; \
		$(COMPOSE) exec -T -e COMPOSER_MEMORY_LIMIT=-1 $(SERVICE_PHP) composer install --no-interaction; \
		$(COMPOSE) exec -T $(SERVICE_PHP) sh -c '$(PNPM) install && $(PNPM) rebuild esbuild && $(PNPM) run build'; \
	fi

ensure-install: ensure-up
	$(COMPOSE) exec -T -e COMPOSER_MEMORY_LIMIT=-1 $(SERVICE_PHP) composer install --no-interaction
	@$(MAKE) assets

install: ensure-install

assets: ensure-up
	$(COMPOSE) exec -T $(SERVICE_PHP) sh -c '$(PNPM) install && $(PNPM) rebuild esbuild && $(PNPM) run build'
	@echo "Restarting FrankenPHP worker so Pentatrion reloads entrypoints.json..."
	$(COMPOSE) restart $(SERVICE_PHP)

shell: ensure-up
	$(COMPOSE) exec $(SERVICE_PHP) sh

logs:
	$(COMPOSE) logs -f

cache-clear: ensure-up
	$(COMPOSE) exec -T $(SERVICE_PHP) php bin/console cache:clear

update-bundle: ensure-up
	$(COMPOSE) exec -T $(SERVICE_PHP) composer update nowo-tech/slide-to-confirm-bundle --with-all-dependencies

# No -T so PHPUnit gets a TTY and shows colors in console
test: ensure-install
	$(COMPOSE) exec $(SERVICE_PHP) vendor/bin/phpunit 2>/dev/null || true

test-coverage: ensure-install
	$(COMPOSE) exec $(SERVICE_PHP) vendor/bin/phpunit --coverage-text 2>/dev/null || true

verify: ensure-up
	@port=$$(grep '^PORT=' .env 2>/dev/null | cut -d= -f2 | tr -d '\r'); [ -z "$$port" ] && port=8055; \
	code=$$(curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:$$port 2>/dev/null || echo "000"); \
	if [ "$$code" = "200" ] || [ "$$code" = "302" ]; then echo "OK (HTTP $$code)"; else echo "Failed (HTTP $$code)"; exit 1; fi


# REQ-MAKE-008: update-deps (REQ-MAKE-008)
BUNDLE_ROOT := $(abspath $(dir $(lastword $(MAKEFILE_LIST)))/../..)
# Optional: monorepo helper absent on standalone GitHub Actions checkout (REQ-MAKE-009).
-include $(BUNDLE_ROOT)/../.scripts/Makefile.demo-update-deps.mk
