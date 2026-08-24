# Usage

`SlideToConfirmType` (and its alias `SwipeToSubmitType`) replace a clickable submit button with a **slide-to-confirm** control. Completing the swipe checks a hidden checkbox (the submitted value) and, by default, calls `form.requestSubmit()`.

The slider is **UX friction**, not an authorization control. Keep CSRF, authentication, and server-side authorization on the host form.

Copy-paste FormType + controller examples for payments, deletes, legal consent, and the other built-in profiles: **[USE-CASES.md](USE-CASES.md)**.

## Table of contents

- [Including the frontend assets](#including-the-frontend-assets)
  - [Standalone script (no Stimulus)](#standalone-script-no-stimulus)
  - [Stimulus + Vite](#stimulus--vite)
- [Complete example](#complete-example)
- [SwipeToSubmitType alias](#swipetosubmittype-alias)
- [Named profiles](#named-profiles)
- [Per-field options](#per-field-options)
- [Use cases](#use-cases)
- [Frontend events](#frontend-events)
- [Overriding templates and translations](#overriding-templates-and-translations)
- [Accessibility](#accessibility)
- [Server-side behaviour](#server-side-behaviour)

## Including the frontend assets

### Standalone script (no Stimulus)

Publish assets and include CSS + JS with the named package (REQ-ASSETS-004):

```twig
<link rel="stylesheet" href="{{ asset(nowo_slide_to_confirm_asset_path('slide-to-confirm.css'), nowo_slide_to_confirm_asset_package()) }}">
<script src="{{ asset(nowo_slide_to_confirm_asset_path('slide-to-confirm.js'), nowo_slide_to_confirm_asset_package()) }}" defer></script>
```

Equivalent without helpers:

```twig
<link rel="stylesheet" href="{{ asset('slide-to-confirm.css', 'nowo_slide_to_confirm') }}">
<script src="{{ asset('slide-to-confirm.js', 'nowo_slide_to_confirm') }}" defer></script>
```

Run `php bin/console assets:install` after install/upgrade. The files are published under `public/bundles/nowoslidetoconfirm/`. The script auto-inits `<nowo-slide-to-confirm>` hosts and watches for dynamically added nodes (Turbo / live forms included).

### Stimulus + Vite

Register the controller from the bundle sources (path relative to `vendor/nowo-tech/slide-to-confirm-bundle`):

```ts
import { Application } from '@hotwired/stimulus';
import SlideToConfirmController from 'vendor/nowo-tech/slide-to-confirm-bundle/src/Resources/assets/controllers/slide_to_confirm_controller.ts';
import 'vendor/nowo-tech/slide-to-confirm-bundle/src/Resources/assets/css/slide-to-confirm.css';

const application = Application.start();
application.register('slide-to-confirm', SlideToConfirmController);
```

The Symfony 8 demo aliases that directory as `@bundle` (Pentatrion Vite + **pnpm**). See `demo/symfony8/assets/app.ts` and `demo/symfony8/vite.config.ts`.

Do **not** also include the standalone IIFE if Stimulus already owns the widget — you would bind twice.

## Complete example

End-to-end: FormType, controller, Twig. More scenarios: [USE-CASES.md](USE-CASES.md).

```php
<?php

declare(strict_types=1);

namespace App\Form;

use Nowo\SlideToConfirmBundle\Form\Type\SlideToConfirmType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class SendMoneyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('iban', TextType::class, [
                'label' => 'Beneficiary IBAN',
            ])
            ->add('amount', MoneyType::class, [
                'currency' => 'EUR',
            ])
            ->add('confirm', SlideToConfirmType::class, [
                'profile' => 'payment',
            ]);
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\SendMoneyType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TransferController extends AbstractController
{
    #[Route('/transfer', name: 'app_transfer', methods: ['GET', 'POST'])]
    public function transfer(Request $request): Response
    {
        $form = $this->createForm(SendMoneyType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            // $data has iban + amount. confirm is mapped=false:
            // $form->get('confirm')->getData() === true

            $this->addFlash('success', 'Payment authorized.');

            return $this->redirectToRoute('app_transfer');
        }

        return $this->render('transfer/form.html.twig', [
            'form' => $form,
        ]);
    }
}
```

Render the wrapping form with a child loop (REQ-TWIG-003 / REQ-TWIG-005):

```twig
{# templates/transfer/form.html.twig #}
{% extends 'base.html.twig' %}

{% block stylesheets %}
    {{ parent() }}
    <link rel="stylesheet" href="{{ asset(nowo_slide_to_confirm_asset_path('slide-to-confirm.css'), nowo_slide_to_confirm_asset_package()) }}">
{% endblock %}

{% block body %}
    {{ form_start(form) }}
        {% for child in form %}
            {% if not child.rendered %}
                {{ form_row(child) }}
            {% endif %}
        {% endfor %}
    {{ form_end(form) }}
{% endblock %}

{% block javascripts %}
    {{ parent() }}
    <script src="{{ asset(nowo_slide_to_confirm_asset_path('slide-to-confirm.js'), nowo_slide_to_confirm_asset_package()) }}" defer></script>
{% endblock %}
```

Do not add a separate `<button type="submit">` unless you use the **gate** profile.

## SwipeToSubmitType alias

`SwipeToSubmitType` is the same widget with block prefix `nowo_swipe_to_submit`. Use it when the field *is* the form submit (batch approve, payroll commit):

```php
use Nowo\SlideToConfirmBundle\Form\Type\SwipeToSubmitType;

$builder->add('submit', SwipeToSubmitType::class, [
    'profile' => 'payment',
    'text'    => sprintf('Slide to approve %d payments', $count),
]);
```

## Named profiles

Built-in profiles (see [CONFIGURATION.md](CONFIGURATION.md)):

| Profile | Typical action | Variant | Submits form? |
| ------- | -------------- | ------- | ------------- |
| `default` | Generic confirm | default | yes |
| `payment` | Pay / send money | payment | yes |
| `danger` | Delete / wipe | danger | yes |
| `legal` | Accept terms | legal | yes |
| `publish` | Make public | success | yes |
| `gate` | Unlock another submit | default | **no** |

Omit `profile` to use `nowo_slide_to_confirm.default_profile` (usually `default`).

Custom profile used by several forms:

```yaml
nowo_slide_to_confirm:
    profiles:
        refund:
            text: app.slide_to_refund
            confirmed_text: app.refunded
            hint: app.hint.refund
            variant: payment
            threshold: 0.9
            submit_on_confirm: true
            reset_on_release: true
```

```php
$builder->add('confirm', SlideToConfirmType::class, [
    'profile' => 'refund',
]);
```

## Per-field options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `profile` | string\|null | bundle `default_profile` | Named profile key. |
| `text` | string\|null | profile | Track label (translation key or literal). |
| `confirmed_text` | string\|null | profile | Label after a successful slide. |
| `hint` | string\|null | profile | Helper text under the track. |
| `variant` | string\|enum\|null | profile | `default`, `danger`, `success`, `payment`, `legal`. |
| `threshold` | float\|null | profile | Ratio `0.5`–`1.0` that completes the slide. |
| `submit_on_confirm` | bool\|null | profile | Call `requestSubmit()` when complete. |
| `reset_on_release` | bool\|null | profile | Snap back if released too early. |
| `translation_domain` | string\|null | bundle config | Domain for `text` / `hint` keys. |
| `required` | bool | `true` | Adds `SlideConfirmed` (`IsTrue`) so an unchecked POST is invalid. |
| `mapped` | bool | `false` | Confirmation is usually not a model field. |
| `track_css_class` | string | `nowo-slide-to-confirm__track` | Track element class. |
| `thumb_css_class` | string | `nowo-slide-to-confirm__thumb` | Thumb button class. |
| `text_css_class` | string | `nowo-slide-to-confirm__text` | Label class. |
| `container_css_class` | string | `nowo-slide-to-confirm` | Host custom-element class. |

`variant` also accepts `Nowo\SlideToConfirmBundle\Form\SlideToConfirmVariant`. Field options override the named profile.

## Use cases

A slid submit is better than a click when the user must **commit a gesture** — it reduces accidental POSTs on destructive or costly actions.

| Case | Profile | Type | Full example |
| ---- | ------- | ---- | ------------ |
| Pay / send money | `payment` | `SlideToConfirmType` | [USE-CASES.md §1](USE-CASES.md#1-payment--send-money) |
| Delete account / wipe | `danger` | `SlideToConfirmType` | [USE-CASES.md §2](USE-CASES.md#2-delete-account-or-wipe-data) |
| Publish / go live | `publish` | `SlideToConfirmType` | [USE-CASES.md §3](USE-CASES.md#3-publish--go-live) |
| Legal consent | `legal` | `SlideToConfirmType` | [USE-CASES.md §4](USE-CASES.md#4-legal-consent) |
| Cancel subscription | `danger` | `SlideToConfirmType` | [USE-CASES.md §5](USE-CASES.md#5-cancel-a-subscription) |
| Approve batch / payroll | `payment` | `SwipeToSubmitType` | [USE-CASES.md §6](USE-CASES.md#6-approve-a-batch--payroll) |
| Emergency logout | `danger` | `SlideToConfirmType` | [USE-CASES.md §7](USE-CASES.md#7-emergency-lock--logout-all-sessions) |
| Unlock then submit | `gate` | `SlideToConfirmType` + `SubmitType` | [USE-CASES.md §8](USE-CASES.md#8-gate-mode-unlock-a-regular-submit) |

Quick snippets (same as the demo `UseCaseFormType`):

```php
use Nowo\SlideToConfirmBundle\Form\Type\SlideToConfirmType;
use Nowo\SlideToConfirmBundle\Form\Type\SwipeToSubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

// 1. Payment
$builder
    ->add('iban', TextType::class)
    ->add('amount', MoneyType::class, ['currency' => 'EUR'])
    ->add('confirm', SlideToConfirmType::class, ['profile' => 'payment']);

// 2. Delete
$builder
    ->add('acknowledge', CheckboxType::class, [
        'label'  => 'I understand this cannot be undone',
        'mapped' => false,
    ])
    ->add('confirm', SlideToConfirmType::class, ['profile' => 'danger']);

// 3. Publish
$builder->add('confirm', SlideToConfirmType::class, ['profile' => 'publish']);

// 4. Legal
$builder
    ->add('contractVersion', HiddenType::class, ['data' => $version])
    ->add('confirm', SlideToConfirmType::class, ['profile' => 'legal']);

// 5. Cancel plan
$builder->add('confirm', SlideToConfirmType::class, [
    'profile'        => 'danger',
    'text'           => 'Slide to cancel your plan',
    'confirmed_text' => 'Cancelled',
]);

// 6. Batch
$builder->add('confirm', SwipeToSubmitType::class, [
    'profile' => 'payment',
    'text'    => sprintf('Slide to approve %d payments', $count),
]);

// 7. Emergency
$builder->add('confirm', SlideToConfirmType::class, [
    'profile' => 'danger',
    'text'    => 'Slide to sign out everywhere',
]);

// 8. Gate (no auto-submit)
$builder
    ->add('unlock', SlideToConfirmType::class, ['profile' => 'gate'])
    ->add('save', SubmitType::class, [
        'attr' => ['disabled' => 'disabled', 'data-gated-submit' => '1'],
    ]);
```

Try them in the demo: `make up-symfony8` → http://localhost:8055/?case=payment (`delete`, `publish`, `legal`, `cancel`, `batch`, `emergency`, `gate`).

## Frontend events

The host custom element dispatches a bubbling event when the slide completes:

| Event | When |
| ----- | ---- |
| `nowo-slide-to-confirm:confirmed` | Threshold reached (pointer or keyboard). Checkbox is already checked. |

```js
document.addEventListener('nowo-slide-to-confirm:confirmed', (event) => {
  const host = event.target;
  if (!(host instanceof HTMLElement)) {
    return;
  }
  const form = host.closest('form');
  const submit = form?.querySelector('[data-gated-submit]');
  if (submit instanceof HTMLButtonElement) {
    submit.disabled = false;
  }
});
```

Use this for the **gate** profile. With `submit_on_confirm: true` (every profile except `gate`), `requestSubmit()` already runs after the event.

## Overriding templates and translations

Domain: **`NowoSlideToConfirmBundle`**. Required locales: `en`, `es`, `it`, `fr`, `pt`, `de`, `nl`.

Override labels in `translations/NowoSlideToConfirmBundle.en.yaml` in the host app, or pass `text` / `confirmed_text` / `hint` per field. Keys missing from the bundle domain are resolved from `messages` (so demo- or app-specific keys work without changing `translation_domain`).

Override the widget at `templates/bundles/NowoSlideToConfirmBundle/Form/_slide_to_confirm_widget.html.twig` (REQ-TWIG-001 freeze rule — prefer CSS tokens and profiles when you still want vendor widget fixes). See [THEMING.md](THEMING.md).

## Accessibility

- The thumb is a `role="slider"` button with `aria-valuenow`.
- Keyboard: `ArrowRight` / `ArrowLeft` (swapped in RTL), `Home` (reset), `End` (confirm), `Enter` / `Space` when the threshold is reached.
- The checkbox remains in the tab order as a visually hidden control for assistive tech that ignores the custom slider; keep `required` so native constraint validation can also apply.

## Server-side behaviour

- Parent type: `CheckboxType`. Submitted value is boolean (`true` when the slide completed or the checkbox was posted as checked).
- Default `mapped: false` — the flag is **not** in `$form->getData()`. Read `$form->get('confirm')->getData()`.
- A required field adds `SlideConfirmed` (an `IsTrue` with message `form.error.not_confirmed`, translated from `NowoSlideToConfirmBundle`). A crafted POST that omits the checkbox is **invalid**.
- A crafted POST that sends `confirm=1` **bypasses the gesture**. Treat the slide as confirmation UX, then apply real authorization on the action.
