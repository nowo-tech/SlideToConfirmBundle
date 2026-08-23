# Makefile for SlideToConfirmBundle - development and QA (Docker)
COMPOSE_FILE := docker-compose.yml
# Prefer Compose V2 plugin (GitHub Actions / modern Docker Desktop); fall back to docker-compose V1 (REQ-MAKE-010).
COMPOSE_BIN  := $(shell docker compose version >/dev/null 2>&1 && echo "docker compose" || echo "docker-compose")
COMPOSE      := $(COMPOSE_BIN) -f $(COMPOSE_FILE)
SERVICE_PHP  := php
RUN          := $(COMPOSE) exec -T $(SERVICE_PHP)

.PHONY: help up down down-dev shell install test test-coverage coverage-check coverage-php-percent cs-check cs-fix qa clean ensure-up check-no-cursor-coauthor check-open-prs strip-cursor-coauthor-from-history check-twig-extra
.PHONY: release-check release-check-demos demo-smoke composer-sync assets build rector rector-dry phpstan update validate validate-translations
.PHONY: assets-test assets-dev assets-watch assets-clean
.PHONY: up-symfony8 down-symfony8 setup-hooks

help:
	@echo "SlideToConfirmBundle - Development Commands (Docker)"
	@echo ""
	@echo "Usage: make <target>"
	@echo ""
	@echo "Targets:"
	@echo "  up             Start Docker container and install deps (PHP + TS)"
	@echo "  down           Stop container"
	@echo "  down-dev       Stop root compose (dev) and remove orphans"
	@echo "  build          Rebuild Docker image (no cache)"
	@echo "  shell          Open shell in container"
	@echo "  install        Install Composer dependencies"
	@echo "  assets         Build frontend (pnpm + Vite IIFE; in Docker)"
	@echo "  test           Run PHPUnit tests"
	@echo "  test-coverage  Run tests with code coverage (PCOV, console)"
	@echo "  coverage-check Fail if PHP Lines coverage is under 100%"
	@echo "  cs-check       Check code style (PHP-CS-Fixer)"
	@echo "  cs-fix         Fix code style"
	@echo "  rector         Apply Rector refactoring"
	@echo "  rector-dry     Rector dry-run (no changes)"
	@echo "  phpstan        Run PHPStan static analysis"
	@echo "  qa             Run all QA (cs-check + test)"
	@echo "  release-check  Pre-release: open PRs, cs, phpstan, coverage-check, demos"
	@echo "  demo-smoke     Demo healthchecks (release-verify)"
	@echo "  check-open-prs Fail if unresolved open PRs (REQ-REL-003)"
	@echo "  composer-sync  Validate composer.json and align composer.lock (no install)"
	@echo "  clean          Remove vendor, cache, coverage"
	@echo "  update         Update Composer dependencies"
	@echo "  validate       Validate composer.json (composer validate --strict)"
	@echo ""
	@echo "Bundle-specific (assets):"
	@echo "  assets-test     Run Vitest with coverage (goal 100%%, output in console)"
	@echo "  assets-dev      Build assets in development mode"
	@echo "  assets-watch    Watch assets for changes"
	@echo "  assets-clean    Clean built assets"
	@echo ""
	@echo "Demos:"
	@echo "  up-symfony8    Start Symfony 8.1 demo (http://localhost:8055)"
	@echo "  down-symfony8  Stop Symfony 8.1 demo"
	@echo "  (or: make -C demo or make -C demo/symfony8)"
	@echo ""

ensure-up:
	@if ! $(COMPOSE) exec -T $(SERVICE_PHP) true 2>/dev/null; then \
		echo "Container not running. Starting docker compose..."; \
		$(COMPOSE) up -d; \
		sleep 2; \
	fi

up:
	$(COMPOSE) up -d
	@sleep 3
	@$(MAKE) install
	@$(MAKE) assets
	@echo "Ready. Run make shell to enter the container."

down:
	$(COMPOSE) down

down-dev:
	$(COMPOSE) down --remove-orphans

shell: ensure-up
	$(COMPOSE) exec $(SERVICE_PHP) sh

install: ensure-up
	$(RUN) composer install

# No -T so PHPUnit gets a TTY and can show colors in console
test: ensure-up
	$(COMPOSE) exec $(SERVICE_PHP) composer test

test-coverage: ensure-up
	$(COMPOSE) exec $(SERVICE_PHP) composer test-coverage | tee coverage-php.txt
	./.scripts/php-coverage-percent.sh coverage-php.txt

coverage-check: test-coverage
	@pct=$$(sed 's/\x1B\[[0-9;]*[A-Za-z]//g' coverage-php.txt | awk '/^[[:space:]]*Lines:[[:space:]]+/ { gsub(/%/, "", $$2); print $$2; exit }'); \
	awk -v p="$$pct" 'BEGIN { if (p+0 < 100) { printf "ERROR: PHP Lines coverage %s%% < 100%%\n", p; exit 1 } printf "Coverage check OK: %s%%\n", p }'

cs-check: install
	$(RUN) composer cs-check

cs-fix: install
	$(RUN) composer cs-fix

qa: install
	$(RUN) composer qa

rector: ensure-up
	$(RUN) composer rector

rector-dry: ensure-up
	$(RUN) composer rector-dry

phpstan: ensure-up
	$(RUN) composer phpstan

update: ensure-up
	$(RUN) composer update --no-interaction

validate: ensure-up
	$(RUN) composer validate --strict

check-open-prs:
	@chmod +x .scripts/check-open-prs.sh
	@GH_REPO=nowo-tech/SlideToConfirmBundle ./.scripts/check-open-prs.sh

demo-smoke:
	@if [ -f demo/Makefile ]; then $(MAKE) -C demo release-verify; else echo "No demo/Makefile — skip demo-smoke"; fi


check-twig-extra:
	@chmod +x .scripts/check-twig-extra.sh
	@./.scripts/check-twig-extra.sh
release-check: check-no-cursor-coauthor check-open-prs check-twig-extra ensure-up composer-sync cs-fix cs-check rector-dry phpstan coverage-check assets-test release-check-demos

release-check-demos:
	@$(MAKE) -C demo release-check

up-symfony8:
	$(MAKE) -C demo/symfony8 up

down-symfony8:
	$(MAKE) -C demo/symfony8 down

build:
	$(COMPOSE) build --no-cache

composer-sync: ensure-up
	$(RUN) composer validate --strict
	$(RUN) composer update --no-install

clean: ensure-up
	$(RUN) sh -c 'rm -rf vendor .phpunit.cache coverage coverage.xml .php-cs-fixer.cache'

assets: ensure-up
	$(RUN) sh -lc 'CI=true pnpm install && CI=true pnpm run build'

assets-test: ensure-up
	$(RUN) sh -lc 'CI=true pnpm install && CI=true pnpm run test:coverage' | tee coverage-ts.txt
	./.scripts/ts-coverage-percent.sh coverage-ts.txt

assets-dev: ensure-up
	$(RUN) sh -lc 'CI=true pnpm install && CI=true pnpm exec vite'

assets-watch: assets-dev

assets-clean:
	rm -rf dist .vite node_modules/.vite 2>/dev/null || true
	@echo "Assets build artifacts cleaned."

validate-translations: ensure-up
	python3 .scripts/check-translation-key-parity.py
	$(RUN) vendor/bin/yaml-lint src/Resources/translations

setup-hooks:
	@chmod +x .githooks/pre-commit 2>/dev/null || true
	@chmod +x .githooks/commit-msg 2>/dev/null || true
	@git config core.hooksPath .githooks
	@echo "✅ Git hooks installed (.githooks — includes commit-msg for REQ-GIT-001)."

# REQ-MAKE-008: update-deps (REQ-MAKE-008)
BUNDLE_ROOT := $(abspath $(dir $(lastword $(MAKEFILE_LIST))))
# Optional: monorepo helper absent on standalone GitHub Actions checkout (REQ-MAKE-009).
-include $(BUNDLE_ROOT)/../.scripts/Makefile.update-deps.mk

check-no-cursor-coauthor:
	@chmod +x .scripts/check-no-cursor-coauthor.sh
	@./.scripts/check-no-cursor-coauthor.sh HEAD

strip-cursor-coauthor-from-history:
	@chmod +x .scripts/strip-cursor-coauthor-from-history.sh
	@./.scripts/strip-cursor-coauthor-from-history.sh master

twig-lint: ensure-up
	@$(COMPOSE) exec -T $(SERVICE_PHP) composer twig:lint || $(COMPOSE) exec -T $(SERVICE_PHP) ./vendor/bin/twig-cs-fixer lint --config=.twig-cs-fixer.php
