<?php

declare(strict_types=1);

namespace Nowo\SlideToConfirmBundle\Tests\Unit\DependencyInjection;

use Nowo\SlideToConfirmBundle\DependencyInjection\Configuration;
use Nowo\SlideToConfirmBundle\DependencyInjection\SlideToConfirmExtension;
use Nowo\SlideToConfirmBundle\Form\Type\SlideToConfirmType;
use Nowo\SlideToConfirmBundle\Form\Type\SwipeToSubmitType;
use Nowo\SlideToConfirmBundle\Profile\SlideToConfirmProfileRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\FrameworkExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class SlideToConfirmExtensionTest extends TestCase
{
    public function testLoadSetsParametersAndRegistersServices(): void
    {
        $container = new ContainerBuilder();
        $extension = new SlideToConfirmExtension();

        $extension->load([[]], $container);

        self::assertSame('default', $container->getParameter('nowo_slide_to_confirm.default_profile'));
        self::assertSame('NowoSlideToConfirmBundle', $container->getParameter('nowo_slide_to_confirm.translation_domain'));
        self::assertSame('form_div_layout.html.twig', $container->getParameter('nowo_slide_to_confirm.form_theme'));
        self::assertFalse($container->getParameter('nowo_slide_to_confirm.debug'));
        self::assertIsArray($container->getParameter('nowo_slide_to_confirm.profiles'));
        self::assertTrue($container->hasDefinition(SlideToConfirmProfileRegistry::class));
        self::assertTrue($container->hasDefinition(SlideToConfirmType::class));
        self::assertTrue($container->hasDefinition(SwipeToSubmitType::class));
    }

    public function testLoadSetsFormThemeFromConfig(): void
    {
        $container = new ContainerBuilder();
        $extension = new SlideToConfirmExtension();

        $extension->load([['form_theme' => 'bootstrap_5_layout.html.twig']], $container);

        self::assertSame('bootstrap_5_layout.html.twig', $container->getParameter('nowo_slide_to_confirm.form_theme'));
    }

    public function testPrependAddsFormThemeToTwig(): void
    {
        $container = new ContainerBuilder();
        $container->prependExtensionConfig(Configuration::ALIAS, []);
        $extension = new SlideToConfirmExtension();

        $extension->prepend($container);

        $twigConfigs = $container->getExtensionConfig('twig');
        self::assertNotEmpty($twigConfigs);
        self::assertSame(
            ['@NowoSlideToConfirmBundle/Form/slide_to_confirm_theme.html.twig'],
            $twigConfigs[0]['form_themes'],
        );
    }

    public function testPrependAddsBootstrap5ThemeWhenConfigured(): void
    {
        $container = new ContainerBuilder();
        $container->prependExtensionConfig(Configuration::ALIAS, ['form_theme' => 'bootstrap_5_layout.html.twig']);
        $extension = new SlideToConfirmExtension();

        $extension->prepend($container);

        $twigConfigs = $container->getExtensionConfig('twig');
        self::assertSame(
            ['@NowoSlideToConfirmBundle/Form/slide_to_confirm_theme_bootstrap5.html.twig'],
            $twigConfigs[0]['form_themes'],
        );
    }

    public function testPrependFallsBackToDivThemeForUnknownFormTheme(): void
    {
        $container = new ContainerBuilder();
        $container->prependExtensionConfig(Configuration::ALIAS, ['form_theme' => 'unknown_layout.html.twig']);
        $extension = new SlideToConfirmExtension();

        $extension->prepend($container);

        $twigConfigs = $container->getExtensionConfig('twig');
        self::assertSame(
            ['@NowoSlideToConfirmBundle/Form/slide_to_confirm_theme.html.twig'],
            $twigConfigs[0]['form_themes'],
        );
    }

    public function testPrependRegistersNamedAssetPackageWhenFrameworkPresent(): void
    {
        $container = new ContainerBuilder();
        $container->registerExtension(new FrameworkExtension());
        $container->prependExtensionConfig(Configuration::ALIAS, []);
        $extension = new SlideToConfirmExtension();

        $extension->prepend($container);

        $frameworkConfigs = $container->getExtensionConfig('framework');
        self::assertNotEmpty($frameworkConfigs);
        self::assertSame(
            '/bundles/nowoslidetoconfirm',
            $frameworkConfigs[0]['assets']['packages']['nowo_slide_to_confirm']['base_path'] ?? null,
        );
    }

    public function testGetAlias(): void
    {
        $extension = new SlideToConfirmExtension();
        self::assertSame('nowo_slide_to_confirm', $extension->getAlias());
    }
}
