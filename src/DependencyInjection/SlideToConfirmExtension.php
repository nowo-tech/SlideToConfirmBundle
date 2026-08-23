<?php

declare(strict_types=1);

namespace Nowo\SlideToConfirmBundle\DependencyInjection;

use Nowo\SlideToConfirmBundle\Profile\SlideToConfirmProfileRegistry;
use Symfony\Component\Asset\Package;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Dependency injection extension for SlideToConfirmBundle.
 *
 * Loads configuration, registers the form type, prepends the Twig form theme,
 * and registers the named asset package (REQ-ASSETS-004).
 */
final class SlideToConfirmExtension extends Extension implements PrependExtensionInterface
{
    /** @var array<string, string> Map form_theme config value to bundle theme path. */
    private const FORM_THEME_MAP = [
        'form_div_layout.html.twig'               => '@NowoSlideToConfirmBundle/Form/slide_to_confirm_theme.html.twig',
        'form_table_layout.html.twig'             => '@NowoSlideToConfirmBundle/Form/slide_to_confirm_theme_table.html.twig',
        'bootstrap_5_layout.html.twig'            => '@NowoSlideToConfirmBundle/Form/slide_to_confirm_theme_bootstrap5.html.twig',
        'bootstrap_5_horizontal_layout.html.twig' => '@NowoSlideToConfirmBundle/Form/slide_to_confirm_theme_bootstrap5_horizontal.html.twig',
        'bootstrap_4_layout.html.twig'            => '@NowoSlideToConfirmBundle/Form/slide_to_confirm_theme_bootstrap4.html.twig',
        'bootstrap_4_horizontal_layout.html.twig' => '@NowoSlideToConfirmBundle/Form/slide_to_confirm_theme_bootstrap4_horizontal.html.twig',
        'bootstrap_3_layout.html.twig'            => '@NowoSlideToConfirmBundle/Form/slide_to_confirm_theme_bootstrap3.html.twig',
        'bootstrap_3_horizontal_layout.html.twig' => '@NowoSlideToConfirmBundle/Form/slide_to_confirm_theme_bootstrap3_horizontal.html.twig',
        'foundation_5_layout.html.twig'           => '@NowoSlideToConfirmBundle/Form/slide_to_confirm_theme_foundation5.html.twig',
        'foundation_6_layout.html.twig'           => '@NowoSlideToConfirmBundle/Form/slide_to_confirm_theme_foundation6.html.twig',
        'tailwind_2_layout.html.twig'             => '@NowoSlideToConfirmBundle/Form/slide_to_confirm_theme_tailwind2.html.twig',
    ];

    /**
     * Prepends the bundle form theme to Twig and registers the named asset package (REQ-ASSETS-004).
     */
    public function prepend(ContainerBuilder $container): void
    {
        $configs   = $container->getExtensionConfig(Configuration::ALIAS);
        $config    = $this->processConfiguration(new Configuration(), $configs);
        $formTheme = $config['form_theme'];
        $themePath = self::FORM_THEME_MAP[$formTheme] ?? self::FORM_THEME_MAP['form_div_layout.html.twig'];

        $container->prependExtensionConfig('twig', [
            'form_themes' => [$themePath],
        ]);

        if ($container->hasExtension('framework') && class_exists(Package::class)) {
            $container->prependExtensionConfig('framework', [
                'assets' => [
                    'packages' => [
                        Configuration::ALIAS => [
                            'base_path' => '/bundles/nowoslidetoconfirm',
                        ],
                    ],
                ],
            ]);
        }
    }

    /**
     * Loads the bundle configuration and service definitions.
     *
     * @param array<int, array<string, mixed>> $configs Array of config arrays (one per config file)
     * @param ContainerBuilder $container The container builder
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        $container->setParameter('nowo_slide_to_confirm.default_profile', $config['default_profile']);
        $container->setParameter('nowo_slide_to_confirm.profiles', $config['profiles']);
        $container->setParameter('nowo_slide_to_confirm.translation_domain', $config['translation_domain']);
        $container->setParameter('nowo_slide_to_confirm.form_theme', $config['form_theme']);
        $container->setParameter('nowo_slide_to_confirm.debug', $config['debug'] ?? false);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $container->getDefinition(SlideToConfirmProfileRegistry::class)
            ->setArgument('$defaultProfile', $config['default_profile'])
            ->setArgument('$profiles', $config['profiles']);
    }

    /**
     * Returns the extension alias (used in config keys).
     */
    public function getAlias(): string
    {
        return Configuration::ALIAS;
    }
}
