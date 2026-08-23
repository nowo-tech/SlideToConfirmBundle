# Release process

This document describes how to cut a new release of SlideToConfirmBundle.

Packagist uses the **git tag** (this project does not store a version in `composer.json`).

## Pre-release (v1.0.0)

- [x] CHANGELOG: [1.0.0] with date and changes; [Unreleased] empty.
- [x] UPGRADING: 1.0.0 first-release section.
- [ ] Run `make release-check` from the bundle root when Docker is available.
- [x] Commit all release-related file changes (docs, CHANGELOG, RELEASE, demos if lockfiles changed).

## Pre-release (every release)

1. Run full QA: `make release-check` (open PRs, CS, PHPStan, coverage, assets tests, demos).
2. Update [CHANGELOG.md](CHANGELOG.md): move `[Unreleased]` into a new `[X.Y.Z] - YYYY-MM-DD` section and add the version link at the bottom.
3. Update [UPGRADING.md](UPGRADING.md) when the change is user-facing (BC, config, assets, Twig).
4. Do **not** bump a version key in `composer.json` (none is stored).

## Tag and GitHub Release

1. Commit the changelog and related files.
2. Create an annotated tag: `git tag -a v1.0.0 -m "Release v1.0.0"`.
3. Push the branch and the tag. `.github/workflows/release.yml` creates the GitHub Release from the tag and CHANGELOG.

**From the bundle repo root:**

```bash
git add -A
git commit -m "chore(release): prepare 1.0.0"
git tag -a v1.0.0 -m "Release v1.0.0 — first public release"
git push origin main
git push origin v1.0.0
```

After the release commit and tag, run `make check-no-cursor-coauthor` again **before** `git push` (REQ-GIT-001). An earlier `release-check` does not cover the release commit itself.

## Post-release

1. Keep an empty `## [Unreleased]` section at the top of CHANGELOG.md for the next cycle.
2. Submit / update the package on [Packagist](https://packagist.org/packages/nowo-tech/slide-to-confirm-bundle) if this is the first tag (`nowo-tech/slide-to-confirm-bundle`).
