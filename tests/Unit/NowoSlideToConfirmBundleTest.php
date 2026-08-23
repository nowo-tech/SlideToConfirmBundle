<?php

declare(strict_types=1);

namespace Nowo\SlideToConfirmBundle\Tests\Unit;

use Nowo\SlideToConfirmBundle\DependencyInjection\SlideToConfirmExtension;
use Nowo\SlideToConfirmBundle\NowoSlideToConfirmBundle;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class NowoSlideToConfirmBundleTest extends TestCase
{
    public function testBuildAddsTwigPathsPass(): void
    {
        $container = new ContainerBuilder();
        $loaderDef = new Definition(stdClass::class);
        $container->setDefinition('twig.loader.native_filesystem', $loaderDef);

        $bundle = new NowoSlideToConfirmBundle();
        $bundle->build($container);
        $container->compile();

        $calls = $loaderDef->getMethodCalls();
        self::assertNotEmpty($calls);
        $addPathCalls = array_filter($calls, static fn (array $c): bool => $c[0] === 'addPath' && ($c[1][1] ?? '') === 'NowoSlideToConfirmBundle');
        self::assertCount(1, $addPathCalls);
    }

    public function testGetContainerExtensionReturnsSlideToConfirmExtension(): void
    {
        $bundle    = new NowoSlideToConfirmBundle();
        $extension = $bundle->getContainerExtension();

        self::assertInstanceOf(SlideToConfirmExtension::class, $extension);
        self::assertSame($extension, $bundle->getContainerExtension());
    }
}
