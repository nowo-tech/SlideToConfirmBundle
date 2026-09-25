# Release process

This document describes how to cut a new release of SlideToConfirmBundle.

Packagist uses the **git tag** (this project does not store a version in `composer.json`).

## Pre-release (every release)

1. Run full QA: `make release-check` (open PRs, CS, PHPStan, coverage, assets tests, demos).
2. Update [CHANGELOG.md](CHANGELOG.md): move `[Unreleased]` into a new `[X.Y.Z] - YYYY-MM-DD` section and add the version link at the bottom.
3. Update [UPGRADING.md](UPGRADING.md) when the change is user-facing (BC, config, assets, Twig).
4. Do **not** bump a version key in `composer.json` (none is stored).

## Pre-release (v1.1.1)

- [x] CHANGELOG: [1.1.1] with date and FrankenPHP worker audit notes; [Unreleased] empty.
- [x] UPGRADING: From 1.1.0 to 1.1.1 (docs/audit only).
- [x] FRANKENPHP-WORKER-AUDIT.md + FR-8 in specs; PHPStan classic + worker-no-kernel-reset.
- [ ] Run `make release-check` from the bundle root when Docker is available.
- [x] Commit all release-related file changes (docs, CHANGELOG, UPGRADING, specs, phpstan).

## Tag and GitHub Release

1. Commit the changelog and related files.
2. Create an annotated tag: `git tag -a v1.1.1 -m "Release v1.1.1"`.
3. Push the branch and the tag. `.github/workflows/release.yml` creates the GitHub Release from the tag and CHANGELOG.

**From the bundle repo root:**

```bash
git add -A
git commit -m "Release v1.1.1: FrankenPHP worker safe with reset_kernel false."
git tag -a v1.1.1 -m "Release v1.1.1 — FrankenPHP worker (FRANKENPHP_RESET_KERNEL unset/false)"
git push origin main
git push origin v1.1.1
```

After the release commit and tag, run `make check-no-cursor-coauthor` again **before** `git push` (REQ-GIT-001). An earlier `release-check` does not cover the release commit itself.

## Post-release

1. Keep an empty `## [Unreleased]` section at the top of CHANGELOG.md for the next cycle.
2. Submit / update the package on [Packagist](https://packagist.org/packages/nowo-tech/slide-to-confirm-bundle) if this is the first tag (`nowo-tech/slide-to-confirm-bundle`).
