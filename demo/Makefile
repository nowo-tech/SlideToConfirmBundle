# Makefile to manage SlideToConfirmBundle demos (standard: up-X, down-X, etc.)
.PHONY: help release-check release-verify clean test-all test-coverage-all verify-all update-bundle-all
.PHONY: up-symfony8 down-symfony8
.PHONY: restart-symfony8 build-symfony8
.PHONY: install-symfony8 update-bundle-symfony8
.PHONY: shell-symfony8 logs-symfony8
.PHONY: test-symfony8 test-coverage-symfony8
.PHONY: verify-symfony8
.PHONY: up down restart build install update-bundle test test-coverage shell logs verify

DEMOS := symfony8

help:
	@echo "SlideToConfirmBundle - Demos"
	@echo ""
	@echo "Usage: make <target>"
	@echo "Demos: $(DEMOS)"
	@echo ""
	@echo "Per-demo: up-X, down-X, restart-X, build-X, install-X, update-bundle-X, shell-X, logs-X, test-X, test-coverage-X, verify-X"
	@echo "  up-symfony8     Start container (http://localhost:8055)"
	@echo "  down-symfony8 Stop container"
	@echo "  restart-symfony8   Restart container"
	@echo "  build-symfony8       Rebuild image (no cache)"
	@echo "  install-symfony8  composer install + assets"
	@echo "  update-bundle-symfony8  Update bundle from path repo"
	@echo "  shell-symfony8       Open shell in PHP container"
	@echo "  logs-symfony8         Show container logs"
	@echo "  test-symfony8         Run tests"
	@echo "  test-coverage-symfony8  Run tests with coverage"
	@echo "  verify-symfony8     Ensure up and check HTTP 200"
	@echo ""
	@echo "Generic (use DEMO=<name>): make up DEMO=symfony8"
	@echo "  up down restart build install update-bundle test test-coverage shell logs verify"
	@echo ""
	@echo "Aggregated:"
	@echo "  test-all           Run tests for all demos"
	@echo "  test-coverage-all  Run tests with coverage for all demos"
	@echo "  verify-all         Verify all demos (up + HTTP 200)"
	@echo "  release-check     Pre-release: test-coverage-all, release-verify"
	@echo "  release-verify    For each demo: up → healthcheck → down"
	@echo "  clean              Remove vendor, var, cache from all demos"
	@echo ""

# Per-demo targets (delegate to each demo's Makefile)
up-symfony8:
	@$(MAKE) -C symfony8 up

down-symfony8:
	@$(MAKE) -C symfony8 down

restart-symfony8:
	@$(MAKE) -C symfony8 restart

build-symfony8:
	@$(MAKE) -C symfony8 build

install-symfony8:
	@$(MAKE) -C symfony8 install

update-bundle-symfony8:
	@$(MAKE) -C symfony8 update-bundle

shell-symfony8:
	@$(MAKE) -C symfony8 shell

logs-symfony8:
	@$(MAKE) -C symfony8 logs

test-symfony8:
	@$(MAKE) -C symfony8 test

test-coverage-symfony8:
	@$(MAKE) -C symfony8 test-coverage

verify-symfony8:
	@$(MAKE) -C symfony8 verify

# Generic (require DEMO=<name>)
up:
	@if [ -z "$(DEMO)" ]; then echo "Error: use make up DEMO=symfony8"; exit 1; fi
	@$(MAKE) up-$(DEMO)

down:
	@if [ -z "$(DEMO)" ]; then echo "Error: use make down DEMO=symfony8"; exit 1; fi
	@$(MAKE) down-$(DEMO)

restart:
	@if [ -z "$(DEMO)" ]; then echo "Error: use make restart DEMO=symfony8"; exit 1; fi
	@$(MAKE) restart-$(DEMO)

build:
	@if [ -z "$(DEMO)" ]; then echo "Error: use make build DEMO=symfony8"; exit 1; fi
	@$(MAKE) build-$(DEMO)

install:
	@if [ -z "$(DEMO)" ]; then echo "Error: use make install DEMO=symfony8"; exit 1; fi
	@$(MAKE) install-$(DEMO)

update-bundle:
	@if [ -z "$(DEMO)" ]; then echo "Error: use make update-bundle DEMO=symfony8"; exit 1; fi
	@$(MAKE) update-bundle-$(DEMO)

test:
	@if [ -z "$(DEMO)" ]; then echo "Error: use make test DEMO=symfony8"; exit 1; fi
	@$(MAKE) test-$(DEMO)

test-coverage:
	@if [ -z "$(DEMO)" ]; then echo "Error: use make test-coverage DEMO=symfony8"; exit 1; fi
	@$(MAKE) test-coverage-$(DEMO)

shell:
	@if [ -z "$(DEMO)" ]; then echo "Error: use make shell DEMO=symfony8"; exit 1; fi
	@$(MAKE) shell-$(DEMO)

logs:
	@if [ -z "$(DEMO)" ]; then echo "Error: use make logs DEMO=symfony8"; exit 1; fi
	@$(MAKE) logs-$(DEMO)

verify:
	@if [ -z "$(DEMO)" ]; then echo "Error: use make verify DEMO=symfony8"; exit 1; fi
	@$(MAKE) verify-$(DEMO)

test-all:
	@for d in $(DEMOS); do $(MAKE) test-$$d || exit 1; done
	@echo "All demos passed tests."

test-coverage-all:
	@for d in $(DEMOS); do $(MAKE) test-coverage-$$d || exit 1; done
	@echo "All demos coverage done."

update-bundle-all:
	@for d in $(DEMOS); do $(MAKE) update-bundle-$$d || exit 1; done
	@echo "All demos updated bundle code."

verify-all:
	@for d in $(DEMOS); do $(MAKE) verify-$$d; $(MAKE) down-$$d || true; done
	@echo "Verify-all done."

# release-check: test-coverage-all, then release-verify (healthchecks)
release-check: update-bundle-all test-coverage-all release-verify
	@echo "All demos passed release-check."

release-verify:
	@for d in $(DEMOS); do \
		port=$$(grep '^PORT=' $$d/.env 2>/dev/null | cut -d= -f2 | tr -d '\r'); \
		[ -z "$$port" ] && port=$$(case $$d in symfony8) echo 8055;; *) echo 8000;; esac); \
		echo "Release verify: $$d (port $$port)..."; \
		$(MAKE) up-$$d; \
		echo "Waiting for $$d to be ready..."; sleep 12; \
		code=$$(curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:$$port 2>/dev/null || echo "000"); \
		$(MAKE) down-$$d 2>/dev/null || true; \
		if [ "$$code" != "200" ] && [ "$$code" != "302" ]; then echo "$$d healthcheck failed (HTTP $$code)"; exit 1; fi; \
		echo "$$d OK (HTTP $$code)"; \
	done
	@echo "All demos passed release-verify."

clean:
	@for d in $(DEMOS); do rm -rf $$d/vendor $$d/var $$d/.phpunit.cache $$d/coverage; echo "Cleaned $$d"; done
	@echo "Clean done."


# REQ-MAKE-008: update-deps-all (REQ-MAKE-008)
BUNDLE_ROOT := $(abspath $(dir $(lastword $(MAKEFILE_LIST)))/..)
# Optional: monorepo helper absent on standalone GitHub Actions checkout (REQ-MAKE-009).
-include $(BUNDLE_ROOT)/../.scripts/Makefile.demo-aggregate-update-deps.mk
