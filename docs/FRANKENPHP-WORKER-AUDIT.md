# FrankenPHP worker mode audit (`FRANKENPHP_RESET_KERNEL` unset / false)

| Field | Value |
|-------|-------|
| Package | `nowo-tech/slide-to-confirm-bundle` (`symfony-bundle`) |
| Audited revision | release **1.1.1** / `main` |
| Audit date | 2026-09-25 |
| Method | Manual review of every PHP file under `src/` (form types, profile registry, Twig extension, validator, DI extension, configuration, compiler pass, `Resources/config/services.yaml`) + PHPStan FrankenPHP classic + worker-no-kernel-reset rulesets |
| **Verdict** | ✅ **Compatible** with FrankenPHP worker when the kernel is **not** reset between requests (`FRANKENPHP_RESET_KERNEL` unset or `0`). Every shared service holds only `readonly` constructor config; request data is never stored. |

## Execution model assumed

FrankenPHP worker mode boots the Symfony kernel once per worker and serves many requests with the same container. Symfony Runtime default is **kernel reused** (`FRANKENPHP_RESET_KERNEL` unset/false). Setting `FRANKENPHP_RESET_KERNEL=1` clones the application after each request (escape hatch; lower throughput). This audit targets the **strict** default:

- **A — kernel not reset, `services_resetter` still runs:** services tagged `kernel.reset` (or implementing `ResetInterface`) are reset between requests.
- **B — kernel not reset, no service reset relied upon:** nothing in this bundle needs `kernel.reset`; shared services only hold injected deps / compiled config.

A bundle that is safe under **B** is safe under **A**, under `FRANKENPHP_RESET_KERNEL=1`, and under classic mode / PHP-FPM.

## Summary

| Area | Status | Notes |
|------|--------|-------|
| Mutable state in shared services | ✅ | All services only hold `readonly` constructor config; no memoization or accumulating arrays |
| Static properties / `static` locals | ✅ | None; `Configuration::builtinProfiles()` is a pure static method used at compile time |
| `ResetInterface` / `kernel.reset` coverage | ✅ N/A | Nothing to reset |
| Request / user / locale captured in services | ✅ | None captured; per-form data comes from form options at `buildView()` time |
| Superglobals, `$_ENV`, `putenv`, `ini_set`, `setlocale`, timezone | ✅ | None used; config is compiled into container parameters |
| Doctrine / EntityManager | ✅ N/A | No persistence |
| Output, headers, `exit`, shutdown functions | ✅ | None |
| Resources (files, sockets, cURL) held open | ✅ | None |
| Memory growth across requests | ✅ | No caches or accumulating arrays |
| Blocking I/O and timeouts | ✅ N/A | No I/O at runtime |
| Third-party static state | ✅ | Only Symfony Form / Validator / Twig, used through their normal APIs |
| PHPStan FrankenPHP rulesets | ✅ | `ruleset-classic.neon` + `ruleset-worker-no-kernel-reset.neon` included in `phpstan.neon` |

## Services reviewed

| Service | Shared | Mutable state | Scenario A | Scenario B |
|---------|--------|---------------|------------|------------|
| `Nowo\SlideToConfirmBundle\Profile\SlideToConfirmProfileRegistry` | yes | none (`readonly` default profile + profiles array) | ✅ | ✅ |
| `Nowo\SlideToConfirmBundle\Form\Type\SlideToConfirmType` | yes (`form.type`) | none (`readonly` registry, translation domain, debug flag) | ✅ | ✅ |
| `Nowo\SlideToConfirmBundle\Form\Type\SwipeToSubmitType` | yes (`form.type`) | none; only overrides `getBlockPrefix()` | ✅ | ✅ |
| `Nowo\SlideToConfirmBundle\NowoSlideToConfirmBundle` | yes (kernel bundle) | `$extension` set once in `__construct` (boot-once, not request state) | ✅ | ✅ |
| `Nowo\SlideToConfirmBundle\Twig\NowoSlideToConfirmTwigExtension` | yes (`twig.extension`) | none; pure functions over constants | ✅ | ✅ |
| `Nowo\SlideToConfirmBundle\Validator\Constraints\SlideConfirmedValidator` | not registered by the bundle; created by Symfony's validator factory | only the standard `ConstraintValidator::$context`, set by Symfony before every validation | ✅ | ✅ |

The `SlideConfirmed` constraint and the `SlideToConfirmVariant` enum are created per form build and never stored in a service. The `variant`, `threshold` and `constraints` normalizers in `src/Form/Type/SlideToConfirmType.php` are `static` closures that only work on the options they receive, so a new `SlideConfirmed` instance is added per form and nothing builds up in the shared type.

## Findings

No findings. The bundle's runtime code is limited to building form views (`src/Form/Type/SlideToConfirmType.php`), validating a boolean (`src/Validator/Constraints/SlideConfirmedValidator.php`) and returning asset names (`src/Twig/NowoSlideToConfirmTwigExtension.php`). None of it writes to a property, a static or a global.

Info: the demo production Caddyfile runs in worker mode (`demo/symfony8/docker/frankenphp/Caddyfile`, `worker { ... }`); `Caddyfile.dev` is classic mode on purpose, so template edits show up without restarting.

## Usage recommendations in worker mode

- No special configuration or reset hook is needed.
- Profiles are read from container parameters at compile time. Changing `nowo_slide_to_confirm.profiles` requires a cache clear and a worker restart, as with any compiled config.
- Host subclasses of `SlideToConfirmType` (the class is intentionally not `final`) must stay stateless: do not store the current form, user or request in properties, or implement `ResetInterface` if you must.
- The widget is a client-side confirmation only. On the server it is a checkbox validated by `SlideConfirmed`; keep your usual CSRF and authorization checks.

## Re-audit triggers

Re-run this audit when a change adds: mutable properties to the form types, registry or Twig extension; a runtime profile cache or per-user profile resolution; a new service that reads the request, session or user; or any use of `$_SERVER` / `$_ENV` at runtime.
