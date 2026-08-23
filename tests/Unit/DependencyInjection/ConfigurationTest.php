<?php

declare(strict_types=1);

namespace Nowo\SlideToConfirmBundle\Tests\Unit\DependencyInjection;

use Nowo\SlideToConfirmBundle\DependencyInjection\Configuration;
use Nowo\SlideToConfirmBundle\Form\SlideToConfirmVariant;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationTest extends TestCase
{
    public function testProcessesDefaultConfig(): void
    {
        $processed = (new Processor())->processConfiguration(new Configuration(), [[]]);

        self::assertSame('default', $processed['default_profile']);
        self::assertSame('NowoSlideToConfirmBundle', $processed['translation_domain']);
        self::assertSame('form_div_layout.html.twig', $processed['form_theme']);
        self::assertFalse($processed['debug']);
        self::assertArrayHasKey('default', $processed['profiles']);
        self::assertArrayHasKey('danger', $processed['profiles']);
        self::assertArrayHasKey('payment', $processed['profiles']);
        self::assertArrayHasKey('legal', $processed['profiles']);
        self::assertArrayHasKey('publish', $processed['profiles']);
        self::assertArrayHasKey('gate', $processed['profiles']);
        self::assertSame(SlideToConfirmVariant::Danger->value, $processed['profiles']['danger']['variant']);
        self::assertFalse($processed['profiles']['gate']['submit_on_confirm']);
        self::assertSame(Configuration::builtinProfiles(), $processed['profiles']);
    }

    public function testMergesPartialProfileOverrides(): void
    {
        $processed = (new Processor())->processConfiguration(new Configuration(), [[
            'default_profile'    => 'payment',
            'form_theme'         => 'bootstrap_5_layout.html.twig',
            'translation_domain' => 'messages',
            'debug'              => true,
            'profiles'           => [
                'payment' => [
                    'threshold' => 0.99,
                    'text'      => 'Pay now',
                ],
            ],
        ]]);

        self::assertSame('payment', $processed['default_profile']);
        self::assertTrue($processed['debug']);
        self::assertSame('bootstrap_5_layout.html.twig', $processed['form_theme']);
        self::assertSame('messages', $processed['translation_domain']);
        self::assertSame(0.99, $processed['profiles']['payment']['threshold']);
        self::assertSame('Pay now', $processed['profiles']['payment']['text']);
        self::assertSame(SlideToConfirmVariant::Payment->value, $processed['profiles']['payment']['variant']);
        self::assertArrayHasKey('default', $processed['profiles']);
    }

    public function testRejectsUnknownDefaultProfile(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('default_profile must exist as a key under profiles.');

        (new Processor())->processConfiguration(new Configuration(), [[
            'default_profile' => 'does-not-exist',
        ]]);
    }

    public function testRejectsInvalidVariant(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        (new Processor())->processConfiguration(new Configuration(), [[
            'profiles' => [
                'default' => [
                    'variant' => 'neon',
                ],
            ],
        ]]);
    }
}
