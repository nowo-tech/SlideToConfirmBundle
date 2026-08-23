# Security

## Scope

This bundle ships a **Symfony FormType** (slide-to-confirm / swipe-to-submit) plus Twig themes and a browser widget. It does **not** expose HTTP controllers, Messenger handlers, file I/O, subprocesses, or Doctrine entities.

CSRF, authentication, and authorization of the wrapping form belong to the **host application** (REQ-SEC-005 — N/A for this package).

## Attack surface

| Input | Path |
| ----- | ---- |
| Form POST | Hidden checkbox value (`true` when the slide completed or a client posted the field checked) |
| Form options / YAML profiles | Labels, variant, threshold, submit flag |
| Twig | Widget labels/hints (translated strings) |
| Query string in the demo | `?case=` allow-listed use-case keys |
| Asset helper | Filename passed to `nowo_slide_to_confirm_asset_path()` |

## Threat model

| Threat | Risk | Mitigation |
| ------ | ---- | ---------- |
| Skip the gesture with a crafted POST | Expected | The swipe is UX friction, not auth. Host must CSRF + authenticate + authorize. Required fields add `IsTrue`. |
| XSS via labels | Low | Twig auto-escape / `e('html_attr')` on data attributes. Frontend uses `textContent`, never `innerHTML`. |
| Path traversal in asset helper | Low | Reject `..` and non-allowlisted characters; fall back to `slide-to-confirm.js`. |
| CSRF | N/A (bundle) | Host form CSRF token. |
| Secrets in logs | Low | Debug logger prints gesture events only; no form payloads. |
| Supply-chain | Low | `composer audit` in CI; no runtime network from the bundle. |

## Mitigations

- Threshold clamped to `[0.5, 1.0]` in PHP and in the TS parser.
- Unknown `variant` / `profile` fail closed (`InvalidOptionsException` / `InvalidArgumentException`).
- Custom element and Stimulus controller disconnect listeners on teardown.
- Demo `use_case` is an allow-list, not free text interpolated into PHP class names.

## Secrets and cryptography

None. No API keys, signing, or encryption.

## Logging

Optional frontend `debug` logs gesture start/confirm/reset. Do not enable debug in production if logs are shipped to a shared sink; they still contain no secrets.

## Dependencies and updates

Run `composer audit` (CI) and Dependabot weekly for Composer, GitHub Actions, and npm.

## Permissions / exposure

No routes. The widget is only rendered where the host places the FormType.

## AI security audit (REQ-SEC-004)

| Field | Value |
| ----- | ----- |
| Risk level | **Low** |
| AI audit date | 2026-08-23 |
| Method | Cursor agent static review of `src/`, Twig themes, TS widget, Flex recipe, demo, `docs/SECURITY.md`, `.github/SECURITY.md` |
| Grade | **Pass (good)** |
| Open residuals | Host must treat a checked checkbox as a normal form field (not proof of a human gesture). No Critical/High findings. |

## Release security checklist (12.4.1)

Before tagging a release, confirm:

| Item | Notes |
|------|--------|
| **SECURITY.md** | This document is current. |
| **`.gitignore` and `.env`** | `.env` ignored; no committed secrets. |
| **No secrets in repo** | No API keys or tokens in tracked files. |
| **Recipe / Flex** | Default recipe does not ship production secrets. |
| **Input / output** | Form options validated; Twig escaped. |
| **Dependencies** | `composer audit` run. |
| **Logging** | Frontend debug logs do not print secrets. |
| **CSRF** | N/A — no session mutations in the bundle; host form CSRF applies. |
| **REQ-SEC-004** | Latest AI audit grade is Pass (good); see above. |
