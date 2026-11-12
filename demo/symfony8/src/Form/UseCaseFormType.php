<?php

declare(strict_types=1);

namespace App\Form;

use Nowo\FormKitBundle\Attribute\FormKitConfig;
use Nowo\FormKitBundle\Form\FormOptionsTrait;
use Nowo\SlideToConfirmBundle\Form\Type\SwipeToSubmitType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * REQ-DEMO-015: a FormKit form (profile `demo`); the bundle's SlideToConfirmType is added through FormKit's
 * addSlideToConfirm() (SwipeToSubmitType has no helper, so it goes through addWithDefaults()).
 *
 * @extends AbstractType<mixed>
 */
#[FormKitConfig('demo')]
final class UseCaseFormType extends AbstractType
{
    use FormOptionsTrait;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $case = $options['use_case'];

        match ($case) {
            'payment'   => $this->buildPayment($builder),
            'delete'    => $this->buildDelete($builder),
            'publish'   => $this->buildPublish($builder),
            'legal'     => $this->buildLegal($builder),
            'cancel'    => $this->buildCancel($builder),
            'batch'     => $this->buildBatch($builder),
            'emergency' => $this->buildEmergency($builder),
            default     => $this->buildGate($builder),
        };
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'use_case' => 'payment',
        ]);
        $resolver->setAllowedTypes('use_case', 'string');
        $resolver->setAllowedValues('use_case', [
            'payment',
            'delete',
            'publish',
            'legal',
            'cancel',
            'batch',
            'emergency',
            'gate',
        ]);
    }

    /**
     * @param FormBuilderInterface<mixed> $builder
     */
    private function buildPayment(FormBuilderInterface $builder): void
    {
        $this->addText($builder, 'iban', [
            'label'       => 'demo.payment.iban',
            'required'    => true,
            'placeholder' => false,
            'help'        => false,
        ]);
        $this->addWithDefaults($builder, 'amount', MoneyType::class, [
            'label'    => 'demo.payment.amount',
            'currency' => 'EUR',
            'divisor'  => 1,
            'help'     => false,
        ]);
        $this->addConfirm($builder, 'confirm', ['profile' => 'payment']);
    }

    /**
     * @param FormBuilderInterface<mixed> $builder
     */
    private function buildDelete(FormBuilderInterface $builder): void
    {
        $this->addCheckbox($builder, 'acknowledge', [
            'label'    => 'demo.delete.acknowledge',
            'mapped'   => false,
            'required' => true,
            'help'     => false,
        ]);
        $this->addConfirm($builder, 'confirm', ['profile' => 'danger']);
    }

    /**
     * @param FormBuilderInterface<mixed> $builder
     */
    private function buildPublish(FormBuilderInterface $builder): void
    {
        $this->addText($builder, 'title', [
            'label'       => 'demo.publish.title',
            'placeholder' => false,
            'help'        => false,
        ]);
        $this->addConfirm($builder, 'confirm', ['profile' => 'publish']);
    }

    /**
     * @param FormBuilderInterface<mixed> $builder
     */
    private function buildLegal(FormBuilderInterface $builder): void
    {
        $this->addConfirm($builder, 'confirm', ['profile' => 'legal']);
    }

    /**
     * @param FormBuilderInterface<mixed> $builder
     */
    private function buildCancel(FormBuilderInterface $builder): void
    {
        $this->addConfirm($builder, 'confirm', [
            'profile'        => 'danger',
            'text'           => 'demo.cancel.slide',
            'confirmed_text' => 'demo.cancel.done',
        ]);
    }

    /**
     * @param FormBuilderInterface<mixed> $builder
     */
    private function buildBatch(FormBuilderInterface $builder): void
    {
        $this->addWithDefaults($builder, 'confirm', SwipeToSubmitType::class, [
            'label'   => 'Confirm',
            'help'    => false,
            'profile' => 'payment',
            'text'    => 'demo.batch.slide',
        ]);
    }

    /**
     * @param FormBuilderInterface<mixed> $builder
     */
    private function buildEmergency(FormBuilderInterface $builder): void
    {
        $this->addConfirm($builder, 'confirm', [
            'profile' => 'danger',
            'text'    => 'demo.emergency.slide',
        ]);
    }

    /**
     * @param FormBuilderInterface<mixed> $builder
     */
    private function buildGate(FormBuilderInterface $builder): void
    {
        $this->addConfirm($builder, 'unlock', ['profile' => 'gate', 'label' => 'Unlock']);
        $this->addWithDefaults($builder, 'save', SubmitType::class, [
            'label' => 'demo.gate.submit',
            'attr'  => [
                'class'                  => 'nowo-ui-btn nowo-ui-btn--primary',
                'disabled'               => 'disabled',
                'data-demo-gated-submit' => '1',
            ],
        ]);
    }

    /**
     * @param FormBuilderInterface<mixed> $builder
     * @param array<string, mixed> $options
     */
    private function addConfirm(FormBuilderInterface $builder, string $name, array $options): void
    {
        $this->addSlideToConfirm($builder, $name, [
            'label'       => 'Confirm',
            'help'        => false,
            'placeholder' => false,
            ...$options,
        ]);
    }
}
