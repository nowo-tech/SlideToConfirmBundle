# Use cases

Use `SlideToConfirmType` (or the `SwipeToSubmitType` alias) whenever a **click is too cheap** for the action: payments, irreversible deletes, legal consent, go-live, batch approvals. The user must finish a gesture before the form posts.

The slider is **confirmation UX**, not authorization. Keep CSRF, authentication, and server-side checks on the host form. A crafted POST can still send `confirm=1`.

Live examples of every case below: Symfony 8 demo (`make up-symfony8`, http://localhost:8055, `?case=payment` and friends). Form code: `demo/symfony8/src/Form/UseCaseFormType.php`.

## Table of contents

- [How to pick a profile](#how-to-pick-a-profile)
- [Shared wiring (FormType + Twig + assets)](#shared-wiring-formtype--twig--assets)
- [1. Payment / send money](#1-payment--send-money)
- [2. Delete account or wipe data](#2-delete-account-or-wipe-data)
- [3. Publish / go live](#3-publish--go-live)
- [4. Legal consent](#4-legal-consent)
- [5. Cancel a subscription](#5-cancel-a-subscription)
- [6. Approve a batch / payroll](#6-approve-a-batch--payroll)
- [7. Emergency lock / logout all sessions](#7-emergency-lock--logout-all-sessions)
- [8. Gate mode (unlock a regular submit)](#8-gate-mode-unlock-a-regular-submit)
- [Custom app profile](#custom-app-profile)
- [What the controller receives](#what-the-controller-receives)

## How to pick a profile

| Situation | Profile | Variant | Auto-submit? | Why this profile |
| --------- | ------- | ------- | ------------ | ---------------- |
| Generic confirm | `default` | default | yes | Neutral copy and threshold `0.85` |
| Pay, send money, refund | `payment` | payment | yes | Amount-oriented copy; threshold `0.9` |
| Delete, wipe, cancel plan | `danger` | danger | yes | Destructive copy; threshold `0.92` |
| Accept terms / DPA | `legal` | legal | yes | Highest built-in threshold (`0.95`) |
| Publish, go live, enable flag | `publish` | success | yes | Positive copy |
| Unlock another submit button | `gate` | default | **no** | Only checks the checkbox; host enables the button |

Override `text`, `confirmed_text`, `hint`, `threshold`, or `variant` per field when the built-in copy is not enough. Define a named profile in YAML when several forms share the same wording ([Custom app profile](#custom-app-profile)).

## Shared wiring (FormType + Twig + assets)

All examples assume the bundle is registered, `form_theme` matches the app layout, and assets are loaded. Full steps: [INSTALLATION.md](INSTALLATION.md) and [USAGE.md](USAGE.md).

**Twig** — always render with a child loop (no raw `<form>` / `<button type="submit">` unless you use [gate](#8-gate-mode-unlock-a-regular-submit)):

```twig
{{ form_start(form) }}
    {% for child in form %}
        {% if not child.rendered %}
            {{ form_row(child) }}
        {% endif %}
    {% endfor %}
{{ form_end(form) }}
```

**Assets** (standalone IIFE, no Stimulus):

```twig
<link rel="stylesheet" href="{{ asset(nowo_slide_to_confirm_asset_path('slide-to-confirm.css'), nowo_slide_to_confirm_asset_package()) }}">
<script src="{{ asset(nowo_slide_to_confirm_asset_path('slide-to-confirm.js'), nowo_slide_to_confirm_asset_package()) }}" defer></script>
```

The confirmation field is `mapped => false` by default, so it does **not** appear in `$form->getData()`. Read it with `$form->get('confirm')->getData()` (boolean `true` after a completed slide). A required field is invalid if the checkbox was not posted.

---

## 1. Payment / send money

**When:** checkout, wallet send, SEPA / wire, payout. Show IBAN and amount **above** the slider so the user confirms the figure they see.

**Profile:** `payment`

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
                'label'    => 'Amount',
                'currency' => 'EUR',
            ])
            ->add('confirm', SlideToConfirmType::class, [
                'profile' => 'payment',
                // Optional: bake the amount into the track label
                // 'text' => sprintf('Slide to send %s', $formattedAmount),
            ]);
    }
}
```

```php
#[Route('/transfer', name: 'app_transfer', methods: ['GET', 'POST'])]
public function transfer(Request $request, TransferService $transfers): Response
{
    $form = $this->createForm(SendMoneyType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        /** @var array{iban: string, amount: string|float} $data */
        $data = $form->getData();
        $transfers->send($data['iban'], $data['amount']);

        $this->addFlash('success', 'Payment authorized.');

        return $this->redirectToRoute('app_transfer_done');
    }

    return $this->render('transfer/form.html.twig', [
        'form' => $form,
    ]);
}
```

Pair with 3-D Secure / SCA on the payment provider, CSRF, and idempotency keys. The slide does not replace those.

---

## 2. Delete account or wipe data

**When:** destroy an account, tenant, vault, or backups. Irreversible.

**Profile:** `danger` (threshold `0.92`). Keep a separate acknowledgement checkbox for the legal “I understand” step.

```php
<?php

declare(strict_types=1);

namespace App\Form;

use Nowo\SlideToConfirmBundle\Form\Type\SlideToConfirmType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\IsTrue;

final class DeleteAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('acknowledge', CheckboxType::class, [
                'label'       => 'I understand this cannot be undone',
                'mapped'      => false,
                'required'    => true,
                'constraints' => [new IsTrue()],
            ])
            ->add('confirm', SlideToConfirmType::class, [
                'profile' => 'danger',
            ]);
    }
}
```

```php
#[Route('/account/delete', name: 'app_account_delete', methods: ['GET', 'POST'])]
public function delete(Request $request, AccountWiper $wiper): Response
{
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

    $form = $this->createForm(DeleteAccountType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $wiper->wipe($this->getUser());

        return $this->redirectToRoute('app_goodbye');
    }

    return $this->render('account/delete.html.twig', [
        'form' => $form,
    ]);
}
```

For high-risk wipes, still require password re-entry or a typed confirmation phrase in the host app.

---

## 3. Publish / go live

**When:** make a draft public — marketing pages, blog posts, price lists, production feature flags.

**Profile:** `publish` (success variant)

```php
<?php

declare(strict_types=1);

namespace App\Form;

use Nowo\SlideToConfirmBundle\Form\Type\SlideToConfirmType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PublishArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Article title',
            ])
            ->add('confirm', SlideToConfirmType::class, [
                'profile' => 'publish',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
```

```php
if ($form->isSubmitted() && $form->isValid()) {
    $article = $form->getData();
    $article->publish();
    $this->entityManager->flush();

    return $this->redirectToRoute('app_article_show', ['id' => $article->getId()]);
}
```

---

## 4. Legal consent

**When:** accept terms, a DPA, or a contract version. Sliding is a clearer affirmative act than a missed checkbox. It is **not** a qualified electronic signature by itself.

**Profile:** `legal` (threshold `0.95`)

```php
<?php

declare(strict_types=1);

namespace App\Form;

use Nowo\SlideToConfirmBundle\Form\Type\SlideToConfirmType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AcceptTermsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('contractVersion', HiddenType::class, [
                'data'   => $options['contract_version'],
                'mapped' => false,
            ])
            ->add('confirm', SlideToConfirmType::class, [
                'profile' => 'legal',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('contract_version');
        $resolver->setAllowedTypes('contract_version', 'string');
    }
}
```

```php
$form = $this->createForm(AcceptTermsType::class, null, [
    'contract_version' => $currentTerms->getVersion(),
]);
$form->handleRequest($request);

if ($form->isSubmitted() && $form->isValid()) {
    $this->consentLog->record(
        user: $this->getUser(),
        version: (string) $form->get('contractVersion')->getData(),
        at: new \DateTimeImmutable(),
        ip: $request->getClientIp(),
    );

    return $this->redirectToRoute('app_dashboard');
}
```

Store contract version, timestamp, and user id on the server when the form is valid. Show the contract text **above** the slider in Twig.

---

## 5. Cancel a subscription

**When:** ending a paid plan is easy to mis-click on mobile. Show the renewal date next to the slider.

**Profile:** `danger`, with custom track labels (translation keys or literals).

```php
$builder->add('confirm', SlideToConfirmType::class, [
    'profile'        => 'danger',
    'text'           => 'Slide to cancel your plan',
    'confirmed_text' => 'Cancelled',
    'hint'           => 'Auto-renewal stops on 1 September 2026.',
]);
```

Prefer translation keys in the host `messages` domain so locales stay in YAML:

```php
$builder->add('confirm', SlideToConfirmType::class, [
    'profile'        => 'danger',
    'text'           => 'subscription.cancel.slide',
    'confirmed_text' => 'subscription.cancel.done',
    'hint'           => 'subscription.cancel.hint',
]);
```

```yaml
# translations/messages.en.yaml
subscription:
  cancel:
    slide: 'Slide to cancel your plan'
    done: 'Cancelled'
    hint: 'You keep access until the end of the current period.'
```

Keys missing from `NowoSlideToConfirmBundle` fall back to `messages`.

---

## 6. Approve a batch / payroll

**When:** one gesture confirms many records (payroll run, mass refund, CSV import commit).

**Type:** `SwipeToSubmitType` — same widget, block prefix `nowo_swipe_to_submit`, use when **this field is the submit**.

```php
use Nowo\SlideToConfirmBundle\Form\Type\SwipeToSubmitType;

$builder->add('confirm', SwipeToSubmitType::class, [
    'profile' => 'payment',
    'text'    => sprintf('Slide to approve %d payments', $count),
]);
```

```php
if ($form->isSubmitted() && $form->isValid()) {
    $this->payroll->commitBatch($batchId);

    return $this->redirectToRoute('app_payroll_done', ['id' => $batchId]);
}
```

Show a summary table of the batch **above** the slider. Re-load the count from the server on POST; do not trust a client-side count.

---

## 7. Emergency lock / logout all sessions

**When:** panic / stolen-device flows. After a valid POST, invalidate every session server-side.

**Profile:** `danger`

```php
$builder->add('confirm', SlideToConfirmType::class, [
    'profile' => 'danger',
    'text'    => 'Slide to sign out everywhere',
]);
```

```php
#[Route('/security/logout-all', name: 'app_logout_all', methods: ['GET', 'POST'])]
public function logoutAll(Request $request, SessionLogout $sessions): Response
{
    $form = $this->createFormBuilder()
        ->add('confirm', SlideToConfirmType::class, [
            'profile' => 'danger',
            'text'    => 'Slide to sign out everywhere',
        ])
        ->getForm();

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $sessions->invalidateAllFor($this->getUser());

        return $this->redirectToRoute('app_login');
    }

    return $this->render('security/logout_all.html.twig', [
        'form' => $form,
    ]);
}
```

---

## 8. Gate mode (unlock a regular submit)

**When:** you still want a visible submit button (Live Component, Turbo, or a multi-step wizard that expects `SubmitType`). Profile `gate` does **not** call `requestSubmit()`; it only checks the checkbox.

**Profile:** `gate` (`submit_on_confirm: false`, `reset_on_release: false`)

```php
use Nowo\SlideToConfirmBundle\Form\Type\SlideToConfirmType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

$builder
    ->add('title', TextType::class)
    ->add('unlock', SlideToConfirmType::class, [
        'profile' => 'gate',
    ])
    ->add('save', SubmitType::class, [
        'label' => 'Save',
        'attr'  => [
            'disabled'         => 'disabled',
            'data-gated-submit' => '1',
        ],
    ]);
```

Enable the button when the custom event fires:

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

The server still rejects the POST if `unlock` is not `true`. Do not rely on the disabled button alone.

---

## Custom app profile

When several forms share wording that is not one of the built-ins, add a named profile instead of repeating options:

```yaml
# config/packages/nowo_slide_to_confirm.yaml
nowo_slide_to_confirm:
    default_profile: default
    form_theme: 'bootstrap_5_layout.html.twig'
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

```yaml
# translations/NowoSlideToConfirmBundle.en.yaml  (or messages.en.yaml)
app:
  slide_to_refund: 'Slide to refund this charge'
  refunded: 'Refunded'
  hint:
    refund: 'The customer receives the original amount within 5–10 days.'
```

```php
$builder->add('confirm', SlideToConfirmType::class, [
    'profile' => 'refund',
]);
```

Partial `profiles` maps merge onto the built-in set. `default_profile` must exist as a key under `profiles`. See [CONFIGURATION.md](CONFIGURATION.md).

## What the controller receives

| Field option | Submitted value | In `$form->getData()`? |
| ------------ | --------------- | ---------------------- |
| `mapped: false` (default) | `true` when the slide completed | **No** — use `$form->get('confirm')->getData()` |
| `mapped: true` | boolean on the model property | Yes |
| `required: true` (default) | missing / `0` / `false` | Form is **invalid** (`SlideConfirmed` / `IsTrue`) |
| `required: false` | optional | Valid even without a slide |

Never authorize the action only because the checkbox is true. Check the current user, CSRF, and business rules after `isValid()`.
