# Code inventory — SlideToConfirmBundle

**Baseline spec**: [`spec.md`](spec.md)  
**Package**: `nowo-tech/slide-to-confirm-bundle`  
**Last audited**: 2026-10-20

Every file under `src/` is listed exactly once. Co-located Vitest files are included as `FR-TEST-TS-001`; `Resources/public/*` are build outputs of the named sources (`FR-BUILD-001`).

## Bundle & DI

| Source file | Requirement IDs |
| --- | --- |
| `DependencyInjection/Compiler/TwigPathsPass.php` | FR-BUNDLE-001 |
| `DependencyInjection/Configuration.php` | FR-CFG-001 |
| `DependencyInjection/SlideToConfirmExtension.php` | FR-CFG-001 |
| `NowoSlideToConfirmBundle.php` | FR-BUNDLE-001 |
| `Profile/SlideToConfirmProfileRegistry.php` | FR-CFG-001 |
| `Resources/config/services.yaml` | FR-CFG-001 |

## Form types

| Source file | Requirement IDs |
| --- | --- |
| `Form/SlideToConfirmVariant.php` | FR-FORM-001 |
| `Form/Type/SlideToConfirmType.php` | FR-FORM-001, FR-FORM-002 |
| `Form/Type/SwipeToSubmitType.php` | FR-FORM-001 |

## Validation

| Source file | Requirement IDs |
| --- | --- |
| `Validator/Constraints/SlideConfirmed.php` | FR-VAL-001 |
| `Validator/Constraints/SlideConfirmedValidator.php` | FR-VAL-001 |

## Twig

| Source file | Requirement IDs |
| --- | --- |
| `Twig/NowoSlideToConfirmTwigExtension.php` | FR-TWIG-001 |

## Twig form themes

| Source file | Requirement IDs |
| --- | --- |
| `Resources/views/Form/_slide_to_confirm_widget.html.twig` | FR-TWIG-002 |
| `Resources/views/Form/slide_to_confirm_theme.html.twig` | FR-TWIG-002 |
| `Resources/views/Form/slide_to_confirm_theme_bootstrap3.html.twig` | FR-TWIG-002 |
| `Resources/views/Form/slide_to_confirm_theme_bootstrap3_horizontal.html.twig` | FR-TWIG-002 |
| `Resources/views/Form/slide_to_confirm_theme_bootstrap4.html.twig` | FR-TWIG-002 |
| `Resources/views/Form/slide_to_confirm_theme_bootstrap4_horizontal.html.twig` | FR-TWIG-002 |
| `Resources/views/Form/slide_to_confirm_theme_bootstrap5.html.twig` | FR-TWIG-002 |
| `Resources/views/Form/slide_to_confirm_theme_bootstrap5_horizontal.html.twig` | FR-TWIG-002 |
| `Resources/views/Form/slide_to_confirm_theme_foundation5.html.twig` | FR-TWIG-002 |
| `Resources/views/Form/slide_to_confirm_theme_foundation6.html.twig` | FR-TWIG-002 |
| `Resources/views/Form/slide_to_confirm_theme_table.html.twig` | FR-TWIG-002 |
| `Resources/views/Form/slide_to_confirm_theme_tailwind2.html.twig` | FR-TWIG-002 |

## TypeScript / CSS sources

| Source file | Requirement IDs |
| --- | --- |
| `Resources/assets/controllers/slide_to_confirm_controller.ts` | FR-UI-002 |
| `Resources/assets/css.d.ts` | FR-BUILD-001 |
| `Resources/assets/css/slide-to-confirm.css` | FR-CSP-001 |
| `Resources/assets/src/logger.ts` | FR-UI-002 |
| `Resources/assets/src/nowo-slide-to-confirm-element.ts` | FR-UI-001 |
| `Resources/assets/src/slide-to-confirm-lib.ts` | FR-UI-001, FR-FORM-002 |
| `Resources/assets/src/slide-to-confirm.ts` | FR-UI-002, FR-CSP-001 |

## TypeScript tests (co-located Vitest)

| Source file | Requirement IDs |
| --- | --- |
| `Resources/assets/controllers/slide_to_confirm_controller.test.ts` | FR-TEST-TS-001 |
| `Resources/assets/src/logger.test.ts` | FR-TEST-TS-001 |
| `Resources/assets/src/nowo-slide-to-confirm-element.test.ts` | FR-TEST-TS-001 |
| `Resources/assets/src/slide-to-confirm-lib.test.ts` | FR-TEST-TS-001 |

## Build outputs

| Source file | Requirement IDs |
| --- | --- |
| `Resources/public/slide-to-confirm.css` | FR-BUILD-001 (copy of `css/slide-to-confirm.css`), FR-CSP-001 |
| `Resources/public/slide-to-confirm.js` | FR-BUILD-001 (output of `slide-to-confirm.ts`), FR-CSP-001 |

## Translations

| Source file | Requirement IDs |
| --- | --- |
| `Resources/translations/NowoSlideToConfirmBundle.de.yaml` | FR-I18N-001 |
| `Resources/translations/NowoSlideToConfirmBundle.en.yaml` | FR-I18N-001 |
| `Resources/translations/NowoSlideToConfirmBundle.es.yaml` | FR-I18N-001 |
| `Resources/translations/NowoSlideToConfirmBundle.fr.yaml` | FR-I18N-001 |
| `Resources/translations/NowoSlideToConfirmBundle.it.yaml` | FR-I18N-001 |
| `Resources/translations/NowoSlideToConfirmBundle.nl.yaml` | FR-I18N-001 |
| `Resources/translations/NowoSlideToConfirmBundle.pt.yaml` | FR-I18N-001 |

## Coverage summary

| Category | Files |
| --- | ---: |
| Bundle & DI | 6 |
| Form types | 3 |
| Validation | 2 |
| Twig | 1 |
| Twig form themes | 12 |
| TypeScript / CSS sources | 7 |
| TypeScript tests (co-located Vitest) | 4 |
| Build outputs | 2 |
| Translations | 7 |
| **Total production sources** | **44** |

Audit command: `git ls-files src | wc -l` → 44.
