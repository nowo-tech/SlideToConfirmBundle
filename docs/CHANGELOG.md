# Changelog

All notable changes to this project are documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## Table of contents

- [[Unreleased]](#unreleased)
- [[1.2.2] - 2026-10-25](#122---2026-10-25)
- [[1.2.1] - 2026-10-21](#121---2026-10-21)
- [[1.2.0] - 2026-10-13](#120---2026-10-13)
- [[1.1.6] - 2026-10-13](#116---2026-10-13)
- [[1.1.5] - 2026-10-09](#115---2026-10-09)
- [[1.1.4] - 2026-10-01](#114---2026-10-01)
- [[1.1.3] - 2026-10-01](#113---2026-10-01)
- [[1.1.2] - 2026-09-27](#112---2026-09-27)
- [[1.1.1] - 2026-09-25](#111---2026-09-25)
- [[1.1.0] - 2026-08-24](#110---2026-08-24)
- [[1.0.0] - 2026-08-23](#100---2026-08-23)

## [Unreleased]

### Changed

- PHPStan config renamed `phpstan.neon` → `phpstan.neon.dist` (REQ-CS-003); a local `phpstan.neon` is gitignored and, when present, overrides it.
- Docs: USAGE states that host and demo forms are Symfony Form Types rendered with `form_start` / the `not child.rendered` child loop / `form_end`, and that raw `<form>` / `<input>` must not be reintroduced; `check-no-raw-html-form` enforces it in `release-check` (REQ-TWIG-005 rule 9).

## [1.2.2] - 2026-10-25

Reproducible JS bundle: the build stamp is a hash of the sources instead of the build time. See [UPGRADING](UPGRADING.md#to-122).

### Changed

- **Assets:** reproducible bundle build. `__SLIDE_TO_CONFIRM_BUILD_TIME__` now holds a build id (a hash of the TypeScript/CSS sources and `pnpm-lock.yaml`) instead of the build time, so rebuilding unchanged sources (`make assets`, `make release-check`) no longer rewrites the committed JS; the console line reads `script loaded, build: <id>`.
- Developer tooling: `make release-check` runs the full REQ-MAKE-002 chain with `ensure-up` first, the new `check-no-raw-html-form` gate (REQ-TWIG-005) after the Cursor co-author, open-PR and Twig `extra` gates, then composer-sync, cs-fix, cs-check, rector-dry, phpstan, igor, coverage-check, the demo release-check and the TypeScript coverage run (`assets-test`).
- Internal: applied the pending Rector suggestion (`RepeatedOrEqualToInArrayRector` in `SlideConfirmedValidator`, strict `in_array`); no behaviour change. `make rector-dry` is clean again.

## [1.2.1] - 2026-10-21

Dependency refresh (bundle and demos), php-cs-fixer ParallelConfig(2). See [UPGRADING](UPGRADING.md#to-121).

### Changed

- Dependencies refreshed (Composer and pnpm/npm lock files of the bundle and its demos, campaign 2026-10-19); shipped assets rebuilt.
- `.php-cs-fixer.dist.php`: `new ParallelConfig(2)` (REQ-CS-001).
- Documentation: wave-1 REQ compliance — canonical issue templates, tables of contents on long docs, Spec Kit baseline rewritten with domain-grouped requirement IDs, non-goals and validation commands, categorized code inventory (44/44).

## [1.2.0] - 2026-10-13

### Fixed

- `nowo_slide_to_confirm.form_theme` is now validated against the supported layouts (`enumNode`); an unknown value fails at container compile time instead of silently falling back to the div theme.
- An unknown `profile` form option is now rejected while resolving the field options (`InvalidOptionsException` when the field is built) instead of throwing a 500 only when the view is rendered.
- CI/tests: the Symfony 6.4 cell now pins every Symfony component of the graph (incl. transitive `symfony/console`, `symfony/validator`, ...) to the cell constraint and fails if any component escapes the cell major; on PHP 8.4/8.5 it previously mixed `symfony/form` 6.4 with 8.x components and died on incompatible signatures.

### Changed

- Minimum Symfony version is now 6.4 (`^6.4 || ^7.0 || ^8.0`), matching what CI tests; a Symfony 6.4 cell was added to the CI matrix.
- Development: `vitest` and `@vitest/coverage-v8` upgraded to 4.x.

## [1.1.6] - 2026-10-13

### Changed

- Development: `composer.json` pins `config.platform.php` to 8.2.0 so the committed lock stays installable on the minimum PHP; CI overrides the platform per matrix cell.

### Dependencies

- `vite` 8.3.2 → 8.3.4 (frontend tooling; #25).
- `jsdom` 30.1.1 → 30.1.2 (frontend tooling; #26).

## [1.1.5] - 2026-10-09

### Dependencies

- Bundle lock: `twig/twig` v3.30.0 and Symfony patch releases.
- Dev tooling: `igor-php/igor-php` ^0.10 (v0.10.1, Dependabot #23), `nowo-tech/phpstan-frankenphp` v1.2.3 (#24), `phpstan/phpstan` 2.3.1, `phpstan/phpstan-symfony` 2.1.0, `rector/rector` 2.7.0, `phpunit/phpunit` 10.5.66.
- JS dev: Vite 8.3.2 (#22).
- Demo (Symfony 8): `symfony/stimulus-bundle` v3.5.1, `twig/twig` v3.30.0, `twig/extra-bundle` v3.29.0, `nowo-tech/hot-reload-bundle` v1.5.5, `nowo-tech/twig-inspector-bundle` v1.1.7.

## [1.1.4] - 2026-10-01

### Added

- **FR-9 (baseline spec):** standalone IIFE MUST NOT inject CSS at runtime; hosts load CSS via `<link>` and JS via `<script>`. Documented in README features, INSTALLATION, and success criteria.

### Notes

- **No runtime change** versus **1.1.3** (CSP-safe IIFE already shipped there). This tag locks the requirement in the Spec Kit baseline.

## [1.1.3] - 2026-10-01

### Added

- **REQ-DEMO-013:** Playwright e2e under `demo/symfony8/e2e/` (`make test-e2e`), `demo-screenshots` target, and README gallery with full demo context (navbar + case pills + slide widget; `docs/images/demo/overview.png`, `interaction.png`).

### Fixed

- **CSP-safe standalone IIFE:** `slide-to-confirm.ts` no longer imports the stylesheet. Vite was embedding CSS as a runtime `document.createElement('style')` append, which hosts with `style-src-elem` nonces (e.g. Beacon) reject. Load `slide-to-confirm.css` via `<link>` as documented; JS and CSS remain separate published assets.

## [1.1.2] - 2026-09-27

### Added

- **REQ-CS-008:** `igor-php/igor-php` (require-dev only), root `igor.json`, Composer/`Makefile` `igor` target, and `release-check` wiring for FrankenPHP worker-state audit.

## [1.1.1] - 2026-09-25

### Added

- **FrankenPHP worker audit** — `docs/FRANKENPHP-WORKER-AUDIT.md` for kernel reuse (`FRANKENPHP_RESET_KERNEL` unset/false); linked from README, USAGE, DEMO-FRANKENPHP.
- Spec **FR-8** / success criterion for worker + PHPStan classic / worker-no-kernel-reset rulesets.

### Changed

- PHPStan includes `ruleset-worker-no-kernel-reset.neon` (targets `FRANKENPHP_RESET_KERNEL` unset/false).
- `NowoSlideToConfirmBundle` caches its DI extension in `__construct` (boot-once, not request state) so the stricter worker-no-kernel-reset ruleset stays clean.

### Notes

- **No API or configuration changes.** Runtime services were already safe under worker mode without kernel reset; this release documents and locks that guarantee.

## [1.1.0] - 2026-08-24

### Added

- add slide-to-confirm validator and expand form widget coverage.
- Usage docs: complete FormType + controller + Twig example, Stimulus Vite snippet, frontend events, and a dedicated [USE-CASES.md](USE-CASES.md) with copy-paste examples for all eight profiles.
- Demo UI catalogues and locale switcher for all seven required locales (`en`, `es`, `it`, `fr`, `pt`, `de`, `nl`).

### Changed

- Raise minimum PHP to **8.2** and sync README badge (REQ-SF-001).
- **Docs:** Spec Kit baseline refresh.
- **Style:** PHP CS Fixer alignment.

### Fixed

- Required fields now translate `form.error.not_confirmed` from `NowoSlideToConfirmBundle` (it was looked up in the `validators` domain).
- The widget falls back to the `messages` domain when a `text` / `confirmed_text` / `hint` key is missing from the bundle catalogue (demo cancel/batch/emergency labels).

### Notes

- **No API or configuration changes** for integrators unless noted above.

## [1.0.0] - 2026-08-23

First public release.

### Added

- `SlideToConfirmType` and `SwipeToSubmitType` (checkbox-backed slide-to-confirm / swipe-to-submit).
- Named profiles: `default`, `payment`, `danger`, `legal`, `publish`, `gate`.
- Stimulus controller and standalone IIFE (TypeScript + Vite + **pnpm**).
- Track fill: the travelled portion of the track uses the same colour as the thumb while dragging (`--nowo-slide-progress`).
- Form themes for Symfony div/table, Bootstrap 3–5, Foundation 5–6, Tailwind 2.
- Translations: `en`, `es`, `it`, `fr`, `pt`, `de`, `nl`.
- Symfony 8.1 demo (`demo/symfony8`, port 8055) with **Pentatrion Vite** (`pentatrion/vite-bundle` + `vite-plugin-symfony`), **pnpm**, and FrankenPHP (`FRANKENPHP_MODE=worker|classic`).
- Use-case demos: pay, delete, publish, legal, cancel, batch, emergency, gate.

### Notes

- PHP `>=8.1 <8.6`, Symfony `^6.0 || ^7.0 || ^8.0` (CI covers 7.0, 7.4, 8.0, 8.1).
- JavaScript tooling is **pnpm only** (`npm` / `yarn` are rejected).
- Host apps can ship the prebuilt IIFE (`assets:install`) or compile the Stimulus controller with their own Vite/Pentatrion entry.

[Unreleased]: https://github.com/nowo-tech/SlideToConfirmBundle/compare/v1.2.2...HEAD
[1.2.2]: https://github.com/nowo-tech/SlideToConfirmBundle/releases/tag/v1.2.2
[1.2.1]: https://github.com/nowo-tech/SlideToConfirmBundle/releases/tag/v1.2.1
[1.2.0]: https://github.com/nowo-tech/SlideToConfirmBundle/releases/tag/v1.2.0
[1.1.6]: https://github.com/nowo-tech/SlideToConfirmBundle/releases/tag/v1.1.6
[1.1.5]: https://github.com/nowo-tech/SlideToConfirmBundle/releases/tag/v1.1.5
[1.1.4]: https://github.com/nowo-tech/SlideToConfirmBundle/releases/tag/v1.1.4
[1.1.3]: https://github.com/nowo-tech/SlideToConfirmBundle/releases/tag/v1.1.3
[1.1.2]: https://github.com/nowo-tech/SlideToConfirmBundle/releases/tag/v1.1.2
[1.1.1]: https://github.com/nowo-tech/SlideToConfirmBundle/releases/tag/v1.1.1
[1.1.0]: https://github.com/nowo-tech/SlideToConfirmBundle/releases/tag/v1.1.0
[1.0.0]: https://github.com/nowo-tech/SlideToConfirmBundle/releases/tag/v1.0.0
