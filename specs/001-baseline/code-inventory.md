# Code inventory — SlideToConfirmBundle

Production PHP (`src/`):

- `NowoSlideToConfirmBundle.php` — bundle + TwigPathsPass
- `DependencyInjection/Configuration.php` — `default_profile` + `profiles`
- `DependencyInjection/SlideToConfirmExtension.php` — load + prepend theme/assets
- `DependencyInjection/Compiler/TwigPathsPass.php`
- `Form/SlideToConfirmVariant.php`
- `Form/Type/SlideToConfirmType.php` — CheckboxType parent
- `Form/Type/SwipeToSubmitType.php` — alias
- `Profile/SlideToConfirmProfileRegistry.php`
- `Twig/NowoSlideToConfirmTwigExtension.php`
- `Resources/config/services.yaml`
- `Resources/views/Form/*` — widget + multi-framework themes
- `Resources/translations/NowoSlideToConfirmBundle.{en,es,it,fr,pt,de,nl}.yaml`

Production TypeScript (`src/Resources/assets/`):

- `src/logger.ts`
- `src/slide-to-confirm-lib.ts`
- `src/nowo-slide-to-confirm-element.ts`
- `src/slide-to-confirm.ts` — IIFE entry
- `controllers/slide_to_confirm_controller.ts`
- `css/slide-to-confirm.css`
