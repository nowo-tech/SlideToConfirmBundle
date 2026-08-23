# Baseline specification — SlideToConfirmBundle

## Summary

Symfony bundle providing `SlideToConfirmType` and `SwipeToSubmitType`: a checkbox-backed slide-to-confirm widget that optionally submits the parent form.

## Functional requirements

- FR-1: Completing the slide sets the checkbox to true.
- FR-2: When `submit_on_confirm` is true, the widget calls `HTMLFormElement.requestSubmit()`.
- FR-3: Required fields add `IsTrue` so an incomplete POST is invalid.
- FR-4: Named profiles `default`, `payment`, `danger`, `legal`, `publish`, `gate` are available.
- FR-5: `gate` does not auto-submit; host may unlock a separate submit button.
- FR-6: Keyboard (arrows, Home, End, Enter/Space) and RTL are supported.
- FR-7: Standalone IIFE and Stimulus controller share the same init logic.

## Out of scope

- Payment processing, SCA, e-signatures, CSRF (host form), and authorization.
