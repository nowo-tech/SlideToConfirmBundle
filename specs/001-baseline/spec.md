# Baseline specification — SlideToConfirmBundle

**Feature branch**: `001-baseline`  
**Package**: `nowo-tech/slide-to-confirm-bundle`  
**Code inventory**: [`code-inventory.md`](code-inventory.md) (44/44 files under `src/`)  
**Related docs**: [`docs/SPEC-DRIVEN-DEVELOPMENT.md`](../../docs/SPEC-DRIVEN-DEVELOPMENT.md), [`docs/USAGE.md`](../../docs/USAGE.md), [`docs/CONFIGURATION.md`](../../docs/CONFIGURATION.md), [`docs/THEMING.md`](../../docs/THEMING.md), [`docs/FRANKENPHP-WORKER-AUDIT.md`](../../docs/FRANKENPHP-WORKER-AUDIT.md)

## Summary

Symfony bundle providing `SlideToConfirmType` and `SwipeToSubmitType`: a checkbox-backed slide-to-confirm widget that optionally submits the parent form. Configuration root `nowo_slide_to_confirm` (`default_profile` + named `profiles`); form themes for the common CSS frameworks; a CSP-safe standalone IIFE (`slide-to-confirm.js` + `slide-to-confirm.css`) and a Stimulus controller sharing the same init logic.

## User Scenarios

### US-01 — Slide to confirm checkout (P1)

As a shopper, I drag the slider to confirm a sensitive action; the hidden checkbox becomes checked and the parent form may submit.

- **Given** a form with a `SlideToConfirmType` field and `submit_on_confirm: true`, **When** the thumb passes the threshold, **Then** the checkbox is checked and `requestSubmit()` is called.
- **Given** a required field, **When** the form is posted without completing the slide, **Then** validation fails (`SlideConfirmed` / `IsTrue`).

### US-02 — Gate mode (P2)

As a host app, I use the `gate` profile to unlock a separate submit button without auto-submitting the form.

- **Given** `profile: gate`, **When** the slide completes, **Then** the form is not submitted and the host can enable its own submit button.

### US-03 — Accessible and CSP-safe widget (P2)

As an integrator with a strict Content-Security-Policy, I load the widget without inline styles and keyboard users can operate it.

- **Given** the standalone assets, **When** the page loads, **Then** no `<style>` element is injected at runtime and the CSS comes from `<link>`.
- **Given** keyboard focus on the thumb, **When** the user presses arrows / Home / End / Enter / Space (also in RTL), **Then** the slider moves or confirms.

## Functional requirements

Grouped by domain; the legacy numeric IDs (FR-1 … FR-9) are kept in brackets for older references.

### Bundle and configuration

- **FR-BUNDLE-001**: `NowoSlideToConfirmBundle` registers the extension and `TwigPathsPass` (Twig namespace with app overrides first).
- **FR-CFG-001**: `Configuration` exposes `default_profile` and `profiles`; `SlideToConfirmExtension` loads `services.yaml` and prepends the form theme and asset package. **[FR-4]** Named profiles `default`, `payment`, `danger`, `legal`, `publish`, `gate` are available (`SlideToConfirmProfileRegistry`).

### Form types and validation

- **FR-FORM-001**: `SlideToConfirmType` (CheckboxType parent, unmapped, required by default) and its alias `SwipeToSubmitType`; `SlideToConfirmVariant` lists the visual variants. **[FR-1]** Completing the slide sets the checkbox to true.
- **FR-FORM-002**: **[FR-2]** When `submit_on_confirm` is true, the widget calls `HTMLFormElement.requestSubmit()`. **[FR-5]** `gate` does not auto-submit; the host may unlock a separate submit button.
- **FR-VAL-001**: **[FR-3]** Required fields add `IsTrue` / `SlideConfirmed` (`SlideConfirmedValidator`) so an incomplete POST is invalid.

### Twig and themes

- **FR-TWIG-001**: `NowoSlideToConfirmTwigExtension` exposes `nowo_slide_to_confirm_asset_path()` / `nowo_slide_to_confirm_asset_package()`.
- **FR-TWIG-002**: Widget block `_slide_to_confirm_widget.html.twig` plus form themes (default, Bootstrap 3/4/5 incl. horizontal, Foundation 5/6, table, Tailwind 2).

### Frontend

- **FR-UI-001**: **[FR-6]** Keyboard (arrows, Home, End, Enter/Space) and RTL are supported (`slide-to-confirm-lib.ts`, `nowo-slide-to-confirm-element.ts`).
- **FR-UI-002**: **[FR-7]** Standalone IIFE (`slide-to-confirm.ts`) and Stimulus controller (`controllers/slide_to_confirm_controller.ts`) share the same init logic; `logger.ts` is the shared client logger.
- **FR-CSP-001**: **[FR-9]** The standalone IIFE MUST NOT inject CSS at runtime (`document.createElement('style')`). Hosts load `slide-to-confirm.css` via `<link>` and `slide-to-confirm.js` via `<script>`; Stimulus hosts import the CSS from sources in their Vite entry.
- **FR-BUILD-001**: `pnpm run build` (Vite) produces `Resources/public/slide-to-confirm.js` and copies `Resources/assets/css/slide-to-confirm.css` to `Resources/public/slide-to-confirm.css`; `css.d.ts` declares CSS modules for TypeScript.
- **FR-TEST-TS-001**: Co-located Vitest files (`*.test.ts`) cover the lib, the custom element, the Stimulus controller and the logger.

### i18n and runtime

- **FR-I18N-001**: `NowoSlideToConfirmBundle.{de,en,es,fr,it,nl,pt}.yaml` translate the default texts.
- **FR-WORKER-001**: **[FR-8]** FrankenPHP worker-safe with kernel reuse (`FRANKENPHP_RESET_KERNEL` unset/false): no per-request state in shared services; PHPStan classic + worker-no-kernel-reset rulesets green.

## Success criteria

- **SC-001**: [`code-inventory.md`](code-inventory.md) maps **44/44** files under `src/` (audited 2026-10-20; `git ls-files src | wc -l`).
- **SC-002**: FrankenPHP worker audit (`docs/FRANKENPHP-WORKER-AUDIT.md`) documents compatibility with kernel reuse; PHPStan classic + worker-no-kernel-reset rulesets pass with no ignores.
- **SC-003**: Built `src/Resources/public/slide-to-confirm.js` contains no `createElement("style")` / style-append inject; USAGE documents separate CSS + JS includes.
- **SC-004**: `composer qa` (CS, PHPStan, PHPUnit) and `pnpm test` (Vitest) pass.

## Key entities

- `SlideToConfirmType` / `SwipeToSubmitType` form types and their options (`profile`, `text`, `confirmed_text`, `hint`, `variant`, `threshold`, `submit_on_confirm`, `reset_on_release`, CSS class options).
- Profiles (`default`, `payment`, `danger`, `legal`, `publish`, `gate`).
- `SlideConfirmed` constraint.

## Non-goals

- Payment processing, SCA, e-signatures, CSRF (host form), and authorization — the swipe is UX, not a security control.
- `demo/` trees are illustrative and not part of the Packagist API.

## Assumptions

- Hosts render the field through one of the shipped form themes and serve `Resources/public/` (or bundle the TS sources with Vite / Stimulus).

## Validation commands

```bash
make qa              # composer cs-check + phpstan + phpunit (Docker)
pnpm test            # Vitest
pnpm run build       # rebuild public assets
make release-check
```
