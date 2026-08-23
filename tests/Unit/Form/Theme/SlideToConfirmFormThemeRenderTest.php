<?php

declare(strict_types=1);

namespace Nowo\SlideToConfirmBundle\Tests\Unit\Form\Theme;

use Nowo\SlideToConfirmBundle\DependencyInjection\Configuration;
use Nowo\SlideToConfirmBundle\Form\Type\SlideToConfirmType;
use Nowo\SlideToConfirmBundle\Form\Type\SwipeToSubmitType;
use Nowo\SlideToConfirmBundle\Profile\SlideToConfirmProfileRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Validator\Validation;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\RuntimeLoader\FactoryRuntimeLoader;

use function dirname;

/**
 * Renders form_row the same way the Symfony 8 demo does (Bootstrap 5 layout + bundle theme).
 */
final class SlideToConfirmFormThemeRenderTest extends TestCase
{
    public function testFormRowWithBootstrap5LayoutDoesNotThrow(): void
    {
        $html = $this->renderPaymentRows([
            'form_div_layout.html.twig',
            'bootstrap_5_layout.html.twig',
            'slide_to_confirm_theme_bootstrap5.html.twig',
        ]);

        self::assertStringContainsString('name="payment[iban]"', $html);
        self::assertStringContainsString('<nowo-slide-to-confirm', $html);
        self::assertStringContainsString('name="payment[confirm]"', $html);
        self::assertStringContainsString('type="checkbox"', $html);
    }

    public function testFormRowWithDivLayoutDoesNotThrow(): void
    {
        $html = $this->renderPaymentRows([
            'form_div_layout.html.twig',
            'slide_to_confirm_theme.html.twig',
        ]);

        self::assertStringContainsString('<nowo-slide-to-confirm', $html);
        self::assertStringContainsString('name="payment[confirm]"', $html);
    }

    /**
     * @param list<string> $themes
     */
    private function renderPaymentRows(array $themes): string
    {
        $bundleRoot = dirname(__DIR__, 4);
        $loader     = new FilesystemLoader([
            $bundleRoot . '/vendor/symfony/twig-bridge/Resources/views/Form',
            $bundleRoot . '/src/Resources/views/Form',
        ]);
        $loader->addPath($bundleRoot . '/src/Resources/views', 'NowoSlideToConfirmBundle');

        $twig     = new Environment($loader);
        $engine   = new TwigRendererEngine($themes, $twig);
        $renderer = new FormRenderer($engine);
        $twig->addRuntimeLoader(new FactoryRuntimeLoader([
            FormRenderer::class => static fn (): FormRenderer => $renderer,
        ]));
        $twig->addExtension(new FormExtension());
        $twig->addExtension(new TranslationExtension(new IdentityTranslator()));

        $registry = new SlideToConfirmProfileRegistry('default', Configuration::builtinProfiles());
        $factory  = Forms::createFormFactoryBuilder()
            ->addExtensions([
                new PreloadedExtension([
                    new SlideToConfirmType($registry, 'NowoSlideToConfirmBundle'),
                    new SwipeToSubmitType($registry, 'NowoSlideToConfirmBundle'),
                ], []),
                new ValidatorExtension(Validation::createValidator()),
            ])
            ->getFormFactory();

        $form = $factory->createNamedBuilder('payment', FormType::class)
            ->add('iban', TextType::class)
            ->add('confirm', SlideToConfirmType::class, ['profile' => 'payment'])
            ->getForm();

        $view = $form->createView();

        return $renderer->searchAndRenderBlock($view['iban'], 'row')
            . $renderer->searchAndRenderBlock($view['confirm'], 'row');
    }
}
