# Contributing

Thank you for considering contributing to SlideToConfirmBundle.



## Code of Conduct

This project follows the [Contributor Covenant Code of Conduct](../CODE_OF_CONDUCT.md). By participating, you are expected to uphold it. Please report unacceptable behavior to **hectorfranco@nowo.tech**.

## Table of contents

- [Development setup](#development-setup)
- [Running tests and QA](#running-tests-and-qa)
- [Code style](#code-style)
- [Pull requests](#pull-requests)
- [Documentation](#documentation)
- [Cursor Agent](#cursor-agent)

## Development setup

- **PHP** >= 8.1 and **Composer** (or use the provided Docker setup).
- **Frontend**: TypeScript and Vite via **pnpm** (`packageManager` in `package.json`). From the bundle root: `pnpm install`, then `pnpm typecheck`, `pnpm test`, or `pnpm run build`. Do not use npm/yarn. The published IIFE lives in `src/Resources/public`. Host apps can compile the bundle TS with **Pentatrion Vite** (`vite-plugin-symfony` + `pentatrion/vite-bundle`); that is what `demo/symfony8` does.
- **Docker** (optional): from the bundle root, run `make up` then `make install` to use the container for all PHP/Composer commands.

## Running tests and QA

From the bundle root:

```bash
# With Docker (recommended)
make install
make test
make test-coverage
make cs-check
make cs-fix
make qa

# Without Docker
composer install
composer test
composer test-coverage
composer cs-check
composer cs-fix
composer qa
```

## Code style

The project uses [PHP-CS-Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer) with the canonical Nowo bundle rules (PSR-12 + Symfony). Run `make cs-fix` (or `composer cs-fix`) before committing.

## Pull requests

1. Fork the repository and create a feature branch.
2. Ensure tests pass and code style is applied.
3. Submit a PR with a clear description; reference any related issues.

## Documentation

All docs are in English. Update the relevant `docs/*.md` and the root README when changing behaviour or options.

## Cursor Agent

Contributors using Cursor Agent on this repository should enable these user-level settings (REQ-IDE-006):

1. **Submit with Ctrl + Enter** (macOS: **⌘ + Enter**) — Cursor Settings → Agents / Chat. Enter inserts a newline; Ctrl/⌘+Enter submits.
2. **Queue follow-ups** in user `settings.json`:

```json
{
  "cursor.composer.queueMessageDefaultBehavior": "queue"
}
```

Do not use `"steer"` as the default: it interrupts a running agent turn.

## Git hooks (REQ-GIT-001)

Do **not** add `Co-authored-by: Cursor` or `cursoragent@cursor.com` trailers to commit messages.

```bash
make setup-hooks
make check-no-cursor-coauthor
```

`make setup-hooks` installs `.githooks/commit-msg` (or sets `core.hooksPath` to `.githooks`). Run it once per clone before your first commit.
If CI fails because trailers are already on the remote, see [GITHUB_CI.md](GITHUB_CI.md) (REQ-GIT-001) and run `make strip-cursor-coauthor-from-history` before `git push --force-with-lease`.
