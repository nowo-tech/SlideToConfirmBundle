# Release process

This document describes how to cut a new release of SlideToConfirmBundle.

## Pre-release (v1.4.12)

- [x] CHANGELOG: [1.4.12] with date and changes; [Unreleased] empty.
- [x] UPGRADING: 1.4.11 → 1.4.12 section (named asset package).
- [ ] Run `make release-check` from the bundle root when Docker is available.
- [x] Commit all release-related file changes (docs, CHANGELOG, RELEASE, demos if lockfiles changed).

## Pre-release (every release)

1. Run full QA: `make release-check` (or `composer-sync`, `cs-fix`, `cs-check`, `test-coverage`, and optionally demo verification).
2. Update [CHANGELOG.md](CHANGELOG.md): move "Unreleased" changes under a new version and set the release date.
3. Bump version in `composer.json` if needed (and any other places that reference the version).

## Tag and release

1. Commit the changelog and version bumps.
2. Create an annotated tag: `git tag -a v1.4.12 -m "Release 1.4.12"`.
3. Push the tag: `git push origin v1.4.12`.
4. If the project uses GitHub Releases or CI, the tag push may trigger release notes and artifact uploads; complete any manual steps required by your workflow.

**From the bundle repo root:**
```bash
git add -A
git commit -m "chore(release): prepare 1.4.12"
git tag -a v1.4.12 -m "Release v1.4.12 - named asset package, FrankenPHP banner, demo PHP 8.5"
git push origin master
git push origin v1.4.12
```

## Post-release

1. In the repo, add a new "Unreleased" section at the top of CHANGELOG.md for the next development cycle.
2. Optionally announce the release (e.g. in project docs or packagist).

After creating the release commit and tag, run `make check-no-cursor-coauthor` again **before** `git push` (REQ-GIT-001). The release commit itself is not covered by an earlier `release-check` run.
