# Baseline specification — SlideToConfirmBundle

## Summary

Symfony bundle providing `SlideToConfirmType` and `SwipeToSubmitType`: a checkbox-backed slide-to-confirm widget that optionally submits the parent form.

## User Scenarios

### US-01 — Slide to confirm checkout (P1)

As a shopper, I drag the slider to confirm a sensitive action; the hidden checkbox becomes checked and the parent form may submit.

### US-02 — Gate mode (P2)

As a host app, I use the `gate` profile to unlock a separate submit button without auto-submitting the form.

## Functional requirements

- FR-1: Completing the slide sets the checkbox to true.
- FR-2: When `submit_on_confirm` is true, the widget calls `HTMLFormElement.requestSubmit()`.
- FR-3: Required fields add `IsTrue` / `SlideConfirmed` so an incomplete POST is invalid.
- FR-4: Named profiles `default`, `payment`, `danger`, `legal`, `publish`, `gate` are available.
- FR-5: `gate` does not auto-submit; host may unlock a separate submit button.
- FR-6: Keyboard (arrows, Home, End, Enter/Space) and RTL are supported.
- FR-7: Standalone IIFE and Stimulus controller share the same init logic.
- FR-8: FrankenPHP worker-safe with kernel reuse (`FRANKENPHP_RESET_KERNEL` unset/false): no per-request state in shared services; PHPStan classic + worker-no-kernel-reset rulesets green.
- FR-9: Standalone IIFE MUST NOT inject CSS at runtime (`document.createElement('style')`). Hosts load `slide-to-confirm.css` via `<link>` and `slide-to-confirm.js` via `<script>` (CSP-safe with `style-src-elem` nonces). Stimulus hosts import the CSS from sources in their Vite entry.

## Success criteria

- FrankenPHP worker audit (`docs/FRANKENPHP-WORKER-AUDIT.md`) documents compatibility with kernel reuse (`FRANKENPHP_RESET_KERNEL` unset/false); PHPStan classic + worker-no-kernel-reset rulesets pass with no ignores.
- Built `src/Resources/public/slide-to-confirm.js` contains no `createElement("style")` / style-append inject; USAGE documents separate CSS + JS includes.

## Out of scope

- Payment processing, SCA, e-signatures, CSRF (host form), and authorization.
