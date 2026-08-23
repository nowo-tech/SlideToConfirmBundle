<?php

declare(strict_types=1);

namespace Nowo\SlideToConfirmBundle\Tests\Unit\Profile;

use InvalidArgumentException;
use Nowo\SlideToConfirmBundle\DependencyInjection\Configuration;
use Nowo\SlideToConfirmBundle\Profile\SlideToConfirmProfileRegistry;
use PHPUnit\Framework\TestCase;

final class SlideToConfirmProfileRegistryTest extends TestCase
{
    public function testGetDefaultProfileAndAll(): void
    {
        $profiles = Configuration::builtinProfiles();
        $registry = new SlideToConfirmProfileRegistry('default', $profiles);

        self::assertSame('default', $registry->getDefaultProfileName());
        self::assertSame($profiles, $registry->all());
        self::assertTrue($registry->has('payment'));
        self::assertFalse($registry->has('missing'));
        self::assertSame('form.slide_to_pay', $registry->get('payment')['text']);
        self::assertSame('form.slide_to_confirm', $registry->get(null)['text']);
    }

    public function testGetThrowsForUnknownProfile(): void
    {
        $registry = new SlideToConfirmProfileRegistry('default', Configuration::builtinProfiles());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown slide-to-confirm profile "nope".');

        $registry->get('nope');
    }
}
