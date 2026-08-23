<?php

declare(strict_types=1);

namespace Nowo\SlideToConfirmBundle\Form\Type;

use Nowo\SlideToConfirmBundle\Form\SlideToConfirmVariant;
use Nowo\SlideToConfirmBundle\Profile\SlideToConfirmProfileRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use ValueError;

use function is_string;

/**
 * Checkbox-backed slide-to-confirm widget. Completing the swipe checks the field
 * and optionally submits the parent form (submit_on_confirm).
 *
 * Intentionally not `final`: {@see SwipeToSubmitType} is a semantic subclass that
 * only changes the form block prefix. Host apps should customise via form options,
 * Twig themes, and profiles rather than subclassing (REQ-PHP-001 extension point).
 *
 * @extends AbstractType<bool>
 */
class SlideToConfirmType extends AbstractType
{
    public function __construct(
        private readonly SlideToConfirmProfileRegistry $registry,
        private readonly string $translationDomain,
        private readonly bool $debug = false,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'mapped'              => false,
            'required'            => true,
            'label'               => false,
            'false_values'        => [null, '', '0', 0, false],
            'profile'             => null,
            'text'                => null,
            'confirmed_text'      => null,
            'hint'                => null,
            'variant'             => null,
            'threshold'           => null,
            'submit_on_confirm'   => null,
            'reset_on_release'    => null,
            'translation_domain'  => null,
            'track_css_class'     => 'nowo-slide-to-confirm__track',
            'thumb_css_class'     => 'nowo-slide-to-confirm__thumb',
            'text_css_class'      => 'nowo-slide-to-confirm__text',
            'container_css_class' => 'nowo-slide-to-confirm',
            'constraints'         => [],
            'error_bubbling'      => false,
        ]);

        $resolver->setAllowedTypes('profile', ['null', 'string']);
        $resolver->setAllowedTypes('text', ['null', 'string']);
        $resolver->setAllowedTypes('confirmed_text', ['null', 'string']);
        $resolver->setAllowedTypes('hint', ['null', 'string']);
        $resolver->setAllowedTypes('variant', ['null', 'string', SlideToConfirmVariant::class]);
        $resolver->setAllowedTypes('threshold', ['null', 'int', 'float']);
        $resolver->setAllowedTypes('submit_on_confirm', ['null', 'bool']);
        $resolver->setAllowedTypes('reset_on_release', ['null', 'bool']);
        $resolver->setAllowedTypes('translation_domain', ['null', 'string']);
        $resolver->setAllowedTypes('track_css_class', 'string');
        $resolver->setAllowedTypes('thumb_css_class', 'string');
        $resolver->setAllowedTypes('text_css_class', 'string');
        $resolver->setAllowedTypes('container_css_class', 'string');
        $resolver->setAllowedTypes('constraints', 'array');

        $resolver->setNormalizer('variant', static function (Options $options, mixed $value): ?SlideToConfirmVariant {
            if ($value === null) {
                return null;
            }
            if ($value instanceof SlideToConfirmVariant) {
                return $value;
            }

            try {
                return SlideToConfirmVariant::from((string) $value);
            } catch (ValueError $e) {
                throw new InvalidOptionsException('The "variant" option must be one of: default, danger, success, payment, legal.', 0, $e);
            }
        });

        $resolver->setNormalizer('threshold', static function (Options $options, mixed $value): ?float {
            if ($value === null) {
                return null;
            }

            $threshold = (float) $value;
            if ($threshold < 0.5 || $threshold > 1.0) {
                throw new InvalidOptionsException('The "threshold" option must be between 0.5 and 1.0.');
            }

            return $threshold;
        });

        $resolver->setNormalizer('constraints', static function (Options $options, array $value): array {
            if ($options['required'] !== true) {
                return $value;
            }

            foreach ($value as $constraint) {
                if ($constraint instanceof IsTrue) {
                    return $value;
                }
            }

            $value[] = new IsTrue(message: 'form.error.not_confirmed');

            return $value;
        });
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $profileName = is_string($options['profile']) ? $options['profile'] : null;
        $profile     = $this->registry->get($profileName);

        $variant = $options['variant'] instanceof SlideToConfirmVariant
            ? $options['variant']
            : SlideToConfirmVariant::from($profile['variant']);

        $view->vars['slide_profile']             = $profileName ?? $this->registry->getDefaultProfileName();
        $view->vars['slide_text']                = $options['text'] ?? $profile['text'];
        $view->vars['slide_confirmed_text']      = $options['confirmed_text'] ?? $profile['confirmed_text'];
        $view->vars['slide_hint']                = $options['hint'] ?? $profile['hint'];
        $view->vars['slide_variant']             = $variant->value;
        $view->vars['slide_threshold']           = $options['threshold'] ?? $profile['threshold'];
        $view->vars['slide_submit_on_confirm']   = $options['submit_on_confirm'] ?? $profile['submit_on_confirm'];
        $view->vars['slide_reset_on_release']    = $options['reset_on_release'] ?? $profile['reset_on_release'];
        $view->vars['slide_translation_domain']  = $options['translation_domain'] ?? $this->translationDomain;
        $view->vars['slide_track_css_class']     = $options['track_css_class'];
        $view->vars['slide_thumb_css_class']     = $options['thumb_css_class'];
        $view->vars['slide_text_css_class']      = $options['text_css_class'];
        $view->vars['slide_container_css_class'] = $options['container_css_class'];
        $view->vars['slide_debug']               = $this->debug;
    }

    public function getParent(): string
    {
        return CheckboxType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'nowo_slide_to_confirm';
    }
}
