## AI contribution guidelines (Nowo Symfony bundle)

Use this when suggesting code, tests, documentation, or CI changes for this repository.

### Scope

- This is a **Symfony bundle** providing **“select all” choice** behavior for forms (`nowo-tech/*` on Packagist).
- Respect **PHP** and **Symfony** ranges in `composer.json`.
- Prefer **PHP 8 attributes**; do not introduce `doctrine/annotations` for new metadata.

### Code

- Follow **PSR-12** and project CS-Fixer / PHPStan expectations.
- Form extensions and Twig integration must remain **compatible** with documented Symfony versions; document breaking changes in `docs/UPGRADING.md` and `CHANGELOG.md`.
- Frontend assets (if any) follow project Vite/build conventions under `src/Resources`.

### Documentation

- User-facing documentation in **English** under `docs/`.
- README follows Nowo canonical structure.

### Tests

- Add PHPUnit and, where applicable, TS tests for new behavior; preserve coverage targets from CI and README.
