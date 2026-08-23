<?php

declare(strict_types=1);

namespace Nowo\SlideToConfirmBundle\DependencyInjection;

use Nowo\SlideToConfirmBundle\Form\SlideToConfirmVariant;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration for SlideToConfirmBundle (nowo_slide_to_confirm).
 *
 * Canonical multi-config surface: default_profile + profiles (REQ-CFG-001).
 */
final class Configuration implements ConfigurationInterface
{
    /** @var string Configuration key / extension alias. */
    public const ALIAS = 'nowo_slide_to_confirm';

    /**
     * Built-in named profiles covering the documented submit use cases.
     *
     * @return array<string, array{
     *     text: string,
     *     confirmed_text: string,
     *     hint: string,
     *     variant: string,
     *     threshold: float,
     *     submit_on_confirm: bool,
     *     reset_on_release: bool
     * }>
     */
    public static function builtinProfiles(): array
    {
        return [
            'default' => [
                'text'              => 'form.slide_to_confirm',
                'confirmed_text'    => 'form.confirmed',
                'hint'              => 'form.hint.default',
                'variant'           => SlideToConfirmVariant::Default->value,
                'threshold'         => 0.85,
                'submit_on_confirm' => true,
                'reset_on_release'  => true,
            ],
            'danger' => [
                'text'              => 'form.slide_to_delete',
                'confirmed_text'    => 'form.deleted',
                'hint'              => 'form.hint.danger',
                'variant'           => SlideToConfirmVariant::Danger->value,
                'threshold'         => 0.92,
                'submit_on_confirm' => true,
                'reset_on_release'  => true,
            ],
            'payment' => [
                'text'              => 'form.slide_to_pay',
                'confirmed_text'    => 'form.paid',
                'hint'              => 'form.hint.payment',
                'variant'           => SlideToConfirmVariant::Payment->value,
                'threshold'         => 0.9,
                'submit_on_confirm' => true,
                'reset_on_release'  => true,
            ],
            'legal' => [
                'text'              => 'form.slide_to_agree',
                'confirmed_text'    => 'form.agreed',
                'hint'              => 'form.hint.legal',
                'variant'           => SlideToConfirmVariant::Legal->value,
                'threshold'         => 0.95,
                'submit_on_confirm' => true,
                'reset_on_release'  => true,
            ],
            'publish' => [
                'text'              => 'form.slide_to_publish',
                'confirmed_text'    => 'form.published',
                'hint'              => 'form.hint.publish',
                'variant'           => SlideToConfirmVariant::Success->value,
                'threshold'         => 0.85,
                'submit_on_confirm' => true,
                'reset_on_release'  => true,
            ],
            'gate' => [
                'text'              => 'form.slide_to_unlock',
                'confirmed_text'    => 'form.unlocked',
                'hint'              => 'form.hint.gate',
                'variant'           => SlideToConfirmVariant::Default->value,
                'threshold'         => 0.85,
                'submit_on_confirm' => false,
                'reset_on_release'  => false,
            ],
        ];
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(self::ALIAS);
        $root        = $treeBuilder->getRootNode();

        $root
            ->children()
                ->scalarNode('default_profile')
                    ->info('Name of the profile used when the form option profile is omitted.')
                    ->defaultValue('default')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('translation_domain')
                    ->info('Default translation domain for slider texts (bundle uses NowoSlideToConfirmBundle).')
                    ->defaultValue('NowoSlideToConfirmBundle')
                ->end()
                ->scalarNode('form_theme')
                    ->info('Base form layout template. Must match a Symfony form theme (e.g. bootstrap_5_layout.html.twig).')
                    ->defaultValue('form_div_layout.html.twig')
                ->end()
                ->booleanNode('debug')
                    ->info('When true, the frontend logs debug messages to the console.')
                    ->defaultValue(false)
                ->end()
            ->end();

        $this->addProfilesNode($root);

        $root
            ->validate()
                ->ifTrue(static fn (array $v): bool => !isset($v['profiles'][$v['default_profile']]))
                ->thenInvalid('default_profile must exist as a key under profiles.')
            ->end();

        return $treeBuilder;
    }

    private function addProfilesNode(ArrayNodeDefinition $root): void
    {
        $root
            ->children()
                ->arrayNode('profiles')
                    ->info('Named complete settings blocks for slide-to-confirm variants (REQ-CFG-001).')
                    ->useAttributeAsKey('name')
                    ->normalizeKeys(false)
                    ->defaultValue(self::builtinProfiles())
                    ->beforeNormalization()
                        ->ifArray()
                        ->then(static function (array $profiles): array {
                            return array_replace_recursive(self::builtinProfiles(), $profiles);
                        })
                    ->end()
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('text')
                                ->defaultValue('form.slide_to_confirm')
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('confirmed_text')
                                ->defaultValue('form.confirmed')
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('hint')
                                ->defaultValue('form.hint.default')
                            ->end()
                            ->scalarNode('variant')
                                ->defaultValue(SlideToConfirmVariant::Default->value)
                                ->validate()
                                    ->ifNotInArray(array_map(
                                        static fn (SlideToConfirmVariant $v): string => $v->value,
                                        SlideToConfirmVariant::cases(),
                                    ))
                                    ->thenInvalid('variant must be one of: default, danger, success, payment, legal.')
                                ->end()
                            ->end()
                            ->floatNode('threshold')
                                ->min(0.5)
                                ->max(1.0)
                                ->defaultValue(0.85)
                            ->end()
                            ->booleanNode('submit_on_confirm')
                                ->defaultTrue()
                            ->end()
                            ->booleanNode('reset_on_release')
                                ->defaultTrue()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
