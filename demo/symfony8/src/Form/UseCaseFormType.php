<?php

declare(strict_types=1);

namespace App\Form;

use Nowo\SlideToConfirmBundle\Form\Type\SlideToConfirmType;
use Nowo\SlideToConfirmBundle\Form\Type\SwipeToSubmitType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UseCaseFormType extends AbstractType
{
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
        $builder
            ->add('iban', TextType::class, [
                'label'    => 'demo.payment.iban',
                'required' => true,
            ])
            ->add('amount', MoneyType::class, [
                'label'    => 'demo.payment.amount',
                'currency' => 'EUR',
                'divisor'  => 1,
            ])
            ->add('confirm', SlideToConfirmType::class, [
                'profile' => 'payment',
            ]);
    }

    /**
     * @param FormBuilderInterface<mixed> $builder
     */
    private function buildDelete(FormBuilderInterface $builder): void
    {
        $builder
            ->add('acknowledge', CheckboxType::class, [
                'label'    => 'demo.delete.acknowledge',
                'mapped'   => false,
                'required' => true,
            ])
            ->add('confirm', SlideToConfirmType::class, [
                'profile' => 'danger',
            ]);
    }

    /**
     * @param FormBuilderInterface<mixed> $builder
     */
    private function buildPublish(FormBuilderInterface $builder): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'demo.publish.title',
            ])
            ->add('confirm', SlideToConfirmType::class, [
                'profile' => 'publish',
            ]);
    }

    /**
     * @param FormBuilderInterface<mixed> $builder
     */
    private function buildLegal(FormBuilderInterface $builder): void
    {
        $builder
            ->add('confirm', SlideToConfirmType::class, [
                'profile' => 'legal',
            ]);
    }

    /**
     * @param FormBuilderInterface<mixed> $builder
     */
    private function buildCancel(FormBuilderInterface $builder): void
    {
        $builder->add('confirm', SlideToConfirmType::class, [
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
        $builder->add('confirm', SwipeToSubmitType::class, [
            'profile' => 'payment',
            'text'    => 'demo.batch.slide',
        ]);
    }

    /**
     * @param FormBuilderInterface<mixed> $builder
     */
    private function buildEmergency(FormBuilderInterface $builder): void
    {
        $builder->add('confirm', SlideToConfirmType::class, [
            'profile' => 'danger',
            'text'    => 'demo.emergency.slide',
        ]);
    }

    /**
     * @param FormBuilderInterface<mixed> $builder
     */
    private function buildGate(FormBuilderInterface $builder): void
    {
        $builder
            ->add('unlock', SlideToConfirmType::class, [
                'profile' => 'gate',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'demo.gate.submit',
                'attr'  => [
                    'disabled'               => 'disabled',
                    'data-demo-gated-submit' => '1',
                ],
            ]);
    }
}
