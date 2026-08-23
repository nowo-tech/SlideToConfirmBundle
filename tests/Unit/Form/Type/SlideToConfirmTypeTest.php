<?php

declare(strict_types=1);

namespace Nowo\SlideToConfirmBundle\Tests\Unit\Form\Type;

use InvalidArgumentException;
use Nowo\SlideToConfirmBundle\DependencyInjection\Configuration;
use Nowo\SlideToConfirmBundle\Form\SlideToConfirmVariant;
use Nowo\SlideToConfirmBundle\Form\Type\SlideToConfirmType;
use Nowo\SlideToConfirmBundle\Form\Type\SwipeToSubmitType;
use Nowo\SlideToConfirmBundle\Profile\SlideToConfirmProfileRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Validation;

final class SlideToConfirmTypeTest extends TestCase
{
    /**
     * @param array<string, mixed> $options
     *
     * @return FormInterface<mixed>
     */
    private function createField(array $options = [], bool $debug = false): FormInterface
    {
        $registry = new SlideToConfirmProfileRegistry('default', Configuration::builtinProfiles());
        $factory  = Forms::createFormFactoryBuilder()
            ->addExtensions([
                new PreloadedExtension([
                    new SlideToConfirmType($registry, 'NowoSlideToConfirmBundle', $debug),
                    new SwipeToSubmitType($registry, 'NowoSlideToConfirmBundle', $debug),
                ], []),
                new ValidatorExtension(Validation::createValidator()),
            ])
            ->getFormFactory();

        return $factory->create(SlideToConfirmType::class, null, $options);
    }

    public function testParentIsCheckboxAndDefaultBlockPrefix(): void
    {
        $registry = new SlideToConfirmProfileRegistry('default', Configuration::builtinProfiles());
        $type     = new SlideToConfirmType($registry, 'NowoSlideToConfirmBundle');

        self::assertSame(CheckboxType::class, $type->getParent());
        self::assertSame('nowo_slide_to_confirm', $type->getBlockPrefix());
        self::assertSame('nowo_swipe_to_submit', (new SwipeToSubmitType($registry, 'NowoSlideToConfirmBundle'))->getBlockPrefix());
    }

    public function testBuildViewUsesDefaultProfile(): void
    {
        $view = $this->createField()->createView();

        self::assertSame('default', $view->vars['slide_profile']);
        self::assertSame('form.slide_to_confirm', $view->vars['slide_text']);
        self::assertSame('form.confirmed', $view->vars['slide_confirmed_text']);
        self::assertSame(SlideToConfirmVariant::Default->value, $view->vars['slide_variant']);
        self::assertSame(0.85, $view->vars['slide_threshold']);
        self::assertTrue($view->vars['slide_submit_on_confirm']);
        self::assertTrue($view->vars['slide_reset_on_release']);
        self::assertSame('NowoSlideToConfirmBundle', $view->vars['slide_translation_domain']);
        self::assertFalse($view->vars['slide_debug']);
    }

    public function testBuildViewResolvesNamedProfileAndOverrides(): void
    {
        $view = $this->createField([
            'profile'            => 'danger',
            'text'               => 'Wipe everything',
            'confirmed_text'     => 'Gone',
            'hint'               => 'custom.hint',
            'variant'            => SlideToConfirmVariant::Danger,
            'threshold'          => 0.97,
            'submit_on_confirm'  => false,
            'reset_on_release'   => false,
            'translation_domain' => 'messages',
        ], true)->createView();

        self::assertSame('danger', $view->vars['slide_profile']);
        self::assertSame('Wipe everything', $view->vars['slide_text']);
        self::assertSame('Gone', $view->vars['slide_confirmed_text']);
        self::assertSame('custom.hint', $view->vars['slide_hint']);
        self::assertSame(SlideToConfirmVariant::Danger->value, $view->vars['slide_variant']);
        self::assertSame(0.97, $view->vars['slide_threshold']);
        self::assertFalse($view->vars['slide_submit_on_confirm']);
        self::assertFalse($view->vars['slide_reset_on_release']);
        self::assertSame('messages', $view->vars['slide_translation_domain']);
        self::assertTrue($view->vars['slide_debug']);
    }

    public function testVariantStringIsNormalized(): void
    {
        $view = $this->createField(['variant' => 'payment'])->createView();
        self::assertSame(SlideToConfirmVariant::Payment->value, $view->vars['slide_variant']);
    }

    public function testRejectsUnknownVariant(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->createField(['variant' => 'rainbow']);
    }

    public function testRejectsThresholdOutOfRange(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->createField(['threshold' => 0.1]);
    }

    public function testRejectsThresholdAboveOne(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->createField(['threshold' => 1.2]);
    }

    public function testUnknownProfileThrowsWhenBuildingTheView(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->createField(['profile' => 'missing'])->createView();
    }

    public function testRequiredAddsIsTrueConstraintOnce(): void
    {
        $form        = $this->createField(['constraints' => [new IsTrue()]]);
        $constraints = $form->getConfig()->getOption('constraints');
        $isTrueCount = 0;
        foreach ($constraints as $constraint) {
            if ($constraint instanceof IsTrue) {
                ++$isTrueCount;
            }
        }
        self::assertSame(1, $isTrueCount);
    }

    public function testOptionalFieldDoesNotRequireConfirmation(): void
    {
        $form = $this->createField(['required' => false]);
        self::assertSame([], $form->getConfig()->getOption('constraints'));
        $form->submit(null);
        self::assertTrue($form->isValid());
    }

    public function testSubmitTrueIsValid(): void
    {
        $form = $this->createField();
        $form->submit('1');
        self::assertTrue($form->isValid());
        self::assertTrue($form->getData());
    }

    public function testSubmitUncheckedIsInvalidWhenRequired(): void
    {
        $form = $this->createField();
        $form->submit(null);
        self::assertFalse($form->isValid());
    }
}
