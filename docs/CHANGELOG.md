# Changelog

All notable changes to this project are documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## Table of contents

- [[Unreleased]](#unreleased)
- [[1.0.0] - 2026-08-23](#100---2026-08-23)

## [Unreleased]

### Added

- Usage docs: complete FormType + controller + Twig example, Stimulus Vite snippet, frontend events, and a dedicated [USE-CASES.md](USE-CASES.md) with copy-paste examples for all eight profiles.
- Demo UI catalogues and locale switcher for all seven required locales (`en`, `es`, `it`, `fr`, `pt`, `de`, `nl`).

### Fixed

- Required fields now translate `form.error.not_confirmed` from `NowoSlideToConfirmBundle` (it was looked up in the `validators` domain).
- The widget falls back to the `messages` domain when a `text` / `confirmed_text` / `hint` key is missing from the bundle catalogue (demo cancel/batch/emergency labels).

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

[Unreleased]: https://github.com/nowo-tech/SlideToConfirmBundle/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/nowo-tech/SlideToConfirmBundle/releases/tag/v1.0.0
