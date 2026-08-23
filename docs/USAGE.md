# Usage

`SlideToConfirmType` (and its alias `SwipeToSubmitType`) replace a clickable submit button with a **slide-to-confirm** control. Completing the swipe checks a hidden checkbox (the submitted value) and, by default, calls `form.requestSubmit()`.

The slider is **UX friction**, not an authorization control. Keep CSRF, authentication, and server-side authorization on the host form.


## Table of contents

- [Including the frontend assets](#including-the-frontend-assets)
- [Basic example](#basic-example)
- [SwipeToSubmitType alias](#swipetosubmittype-alias)
- [Named profiles](#named-profiles)
- [Per-field options](#per-field-options)
- [Use cases](#use-cases)
  - [1. Payment / send money](#1-payment--send-money)
  - [2. Delete account or wipe data](#2-delete-account-or-wipe-data)
  - [3. Publish / go live](#3-publish--go-live)
  - [4. Legal consent](#4-legal-consent)
  - [5. Cancel a subscription](#5-cancel-a-subscription)
  - [6. Approve a batch / payroll](#6-approve-a-batch--payroll)
  - [7. Emergency lock / logout all sessions](#7-emergency-lock--logout-all-sessions)
  - [8. Gate mode (unlock a regular submit)](#8-gate-mode-unlock-a-regular-submit)
- [Overriding templates and translations](#overriding-templates-and-translations)
- [Accessibility](#accessibility)
- [Server-side behaviour](#server-side-behaviour)

## Including the frontend assets

**1. Standalone script (no Stimulus required)** — publish assets and include CSS + JS with the named package (REQ-ASSETS-004):

```twig
<link rel="stylesheet" href="{{ asset(nowo_slide_to_confirm_asset_path('slide-to-confirm.css'), nowo_slide_to_confirm_asset_package()) }}">
<script src="{{ asset(nowo_slide_to_confirm_asset_path('slide-to-confirm.js'), nowo_slide_to_confirm_asset_package()) }}" defer></script>
```

Equivalent without helpers:

```twig
<link rel="stylesheet" href="{{ asset('slide-to-confirm.css', 'nowo_slide_to_confirm') }}">
<script src="{{ asset('slide-to-confirm.js', 'nowo_slide_to_confirm') }}" defer></script>
```

Run `php bin/console assets:install` after install/upgrade. The files are published under `public/bundles/nowoslidetoconfirm/`. The script auto-inits `<nowo-slide-to-confirm>` hosts and watches for dynamically added nodes.

**2. With Stimulus** — register the controller: `application.register('slide-to-confirm', SlideToConfirmController)`. Import the CSS from `src/Resources/assets/css/slide-to-confirm.css` in your Vite entry.

## Basic example

```php
use Nowo\SlideToConfirmBundle\Form\Type\SlideToConfirmType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;

$builder
    ->add('amount', MoneyType::class)
    ->add('confirm', SlideToConfirmType::class, [
        'profile' => 'payment',
    ]);
```

Render the wrapping form with a child loop (REQ-TWIG-003 / REQ-TWIG-005):

```twig
{{ form_start(form) }}
    {% for child in form %}
        {% if not child.rendered %}
            {{ form_row(child) }}
        {% endif %}
    {% endfor %}
{{ form_end(form) }}
```

Do not add a separate `<button type="submit">` unless you use the **gate** profile.

## SwipeToSubmitType alias

`SwipeToSubmitType` is the same widget with block prefix `nowo_swipe_to_submit`. Use it when the field *is* the form submit:

```php
use Nowo\SlideToConfirmBundle\Form\Type\SwipeToSubmitType;

$builder->add('submit', SwipeToSubmitType::class, [
    'text' => 'form.slide_to_confirm',
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
| `required` | bool | `true` | Adds an `IsTrue` constraint so an unchecked POST is invalid. |
| `mapped` | bool | `false` | Confirmation is usually not a model field. |

## Use cases

These are the situations where a slid submit is better than a click: the user must **commit a gesture**, which reduces accidental POSTs on destructive or costly actions.

### 1. Payment / send money

Banking, wallets, checkout, SEPA / wire transfers. Show the amount **above** the slider so the user confirms the figure they see.

```php
$builder
    ->add('iban', TextType::class)
    ->add('amount', MoneyType::class, ['currency' => 'EUR'])
    ->add('confirm', SlideToConfirmType::class, [
        'profile' => 'payment',
        'text' => 'Slide to send €' . $formattedAmount,
    ]);
```

Pair with: 3-D Secure / SCA on the payment provider, CSRF, and idempotency keys. The slide does not replace those.

### 2. Delete account or wipe data

Irreversible destruction (account, tenant, vault, backups). Use the `danger` profile and a higher threshold.

```php
$builder
    ->add('acknowledge', CheckboxType::class, [
        'label' => 'I understand this cannot be undone',
        'mapped' => false,
    ])
    ->add('confirm', SlideToConfirmType::class, [
        'profile' => 'danger',
    ]);
```

Still require password re-entry or a typed confirmation phrase in the host app when the risk is high.

### 3. Publish / go live

Make a draft public: marketing pages, blog posts, price lists, production feature flags.

```php
$builder->add('confirm', SlideToConfirmType::class, [
    'profile' => 'publish',
]);
```

### 4. Legal consent

Accept terms, DPA, or a contract. Sliding is a clearer affirmative act than an easy-to-miss checkbox, but it is **not** a qualified electronic signature by itself.

```php
$builder
    ->add('contractVersion', HiddenType::class, ['data' => $version])
    ->add('confirm', SlideToConfirmType::class, [
        'profile' => 'legal',
    ]);
```

Store the contract version, timestamp, and user id on the server when the form is valid.

### 5. Cancel a subscription

Ending a paid plan is easy to mis-click on mobile. Use `danger` and show the renewal date next to the slider.

```php
$builder->add('confirm', SlideToConfirmType::class, [
    'profile' => 'danger',
    'text' => 'Slide to cancel your plan',
    'confirmed_text' => 'Cancelled',
]);
```

### 6. Approve a batch / payroll

Admin actions that affect many records (payroll run, mass refund, CSV import commit). One slide confirms the whole batch.

```php
$builder->add('confirm', SwipeToSubmitType::class, [
    'profile' => 'payment',
    'text' => sprintf('Slide to approve %d payments', $count),
]);
```

### 7. Emergency lock / logout all sessions

Panic / stolen-device flows. High threshold, `danger` variant, then invalidate sessions server-side.

```php
$builder->add('confirm', SlideToConfirmType::class, [
    'profile' => 'danger',
    'text' => 'Slide to sign out everywhere',
]);
```

### 8. Gate mode (unlock a regular submit)

When you still want a visible submit button (or Live Component / Turbo that expects a button), use profile `gate`: the slide only checks the checkbox. Enable or reveal the button in your own JS by listening to `nowo-slide-to-confirm:confirmed`.

```php
$builder
    ->add('unlock', SlideToConfirmType::class, [
        'profile' => 'gate',
    ])
    ->add('save', SubmitType::class);
```

```js
document.addEventListener('nowo-slide-to-confirm:confirmed', () => {
  document.querySelector('[type="submit"]')?.removeAttribute('disabled');
});
```

The server still rejects the POST if `unlock` is not `true`.

## Overriding templates and translations

Domain: **`NowoSlideToConfirmBundle`**. Required locales: `en`, `es`, `it`, `fr`, `pt`, `de`, `nl`.

Override labels in `translations/NowoSlideToConfirmBundle.en.yaml` in the host app, or pass `text` / `confirmed_text` / `hint` per field.

Override the widget at `templates/bundles/NowoSlideToConfirmBundle/Form/_slide_to_confirm_widget.html.twig`.

## Accessibility

- The thumb is a `role="slider"` button with `aria-valuenow`.
- Keyboard: `ArrowRight` / `ArrowLeft` (swapped in RTL), `Home` (reset), `End` (confirm), `Enter` / `Space` when the threshold is reached.
- The checkbox remains in the tab order as a visually hidden control for assistive tech that ignores the custom slider; keep `required` so native constraint validation can also apply.

## Server-side behaviour

- Parent type: `CheckboxType`. Submitted value is boolean (`true` when the slide completed or the checkbox was posted as checked).
- A required field adds `IsTrue` (`form.error.not_confirmed`). A crafted POST that omits the checkbox is **invalid**.
- A crafted POST that sends `confirm=1` **bypasses the gesture**. Treat the slide as confirmation UX, then apply real authorization on the action.
