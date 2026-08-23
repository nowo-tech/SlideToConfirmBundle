# Spec-driven development (REQ-DOCS-013)

Product behaviour for **SlideToConfirmBundle** is specified in this document and in `specs/001-baseline/`.

## Product

A Symfony FormType (`SlideToConfirmType` / `SwipeToSubmitType`) that replaces a click submit with a slide-to-confirm control. Completing the slide checks a checkbox and optionally submits the form. Named profiles cover payment, danger, legal, publish, and gate (unlock) use cases.

## User stories

1. As a user sending money, I slide to pay so I do not tap Pay by accident.
2. As a user deleting an account, I must drag fully to the end.
3. As an editor, I slide to publish a draft.
4. As a customer, I slide to accept terms.
5. As an admin, I slide to approve a batch.
6. As a developer, I can use gate mode so a regular submit stays disabled until unlocked.

## REQ-* anchors

- Form widget only (host CSRF): REQ-SEC-005 N/A.
- Named profiles: REQ-CFG-001 (`default_profile` + `profiles`).
- Twig child loop in demos: REQ-TWIG-003 / REQ-TWIG-005.
- Assets package `nowo_slide_to_confirm`: REQ-ASSETS-004.
- Stimulus optional: REQ-UX-001.
- Translations domain `NowoSlideToConfirmBundle` and 7 locales: REQ-I18N-002 / REQ-I18N-003.

## Validation

```bash
make release-check
```

## Spec Kit

See [SPEC-KIT.md](SPEC-KIT.md). Baseline: `specs/001-baseline/spec.md`.
