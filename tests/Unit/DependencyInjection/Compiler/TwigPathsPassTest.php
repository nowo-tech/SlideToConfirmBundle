<?php

declare(strict_types=1);

namespace Nowo\SlideToConfirmBundle\Tests\Unit\DependencyInjection\Compiler;

use Nowo\SlideToConfirmBundle\DependencyInjection\Compiler\TwigPathsPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

use function count;

final class TwigPathsPassTest extends TestCase
{
    public function testProcessAddsTwigPathToNativeFilesystemLoader(): void
    {
        $container = new ContainerBuilder();
        $loaderDef = new Definition();
        $container->setDefinition('twig.loader.native_filesystem', $loaderDef);

        $pass = new TwigPathsPass();
        $pass->process($container);

        $calls = $loaderDef->getMethodCalls();
        self::assertNotEmpty($calls);

        $found = false;
        foreach ($calls as [$method, $args]) {
            if ($method !== 'addPath') {
                continue;
            }
            if (!isset($args[0], $args[1])) {
                continue;
            }
            if ($args[1] !== 'NowoSlideToConfirmBundle') {
                continue;
            }
            self::assertStringEndsWith('/Resources/views', (string) $args[0]);
            $found = true;
            break;
        }

        self::assertTrue($found, 'Expected addPath call for NowoSlideToConfirmBundle namespace.');
    }

    public function testProcessUsesTwigLoaderNativeWhenAliasExists(): void
    {
        $container = new ContainerBuilder();
        $loaderDef = new Definition();
        $container->setDefinition('twig.loader.native_filesystem', $loaderDef);
        $container->setAlias('twig.loader.native', 'twig.loader.native_filesystem');

        $pass = new TwigPathsPass();
        $pass->process($container);

        $calls        = $loaderDef->getMethodCalls();
        $addPathCalls = array_filter($calls, static fn (array $c): bool => $c[0] === 'addPath' && ($c[1][1] ?? '') === 'NowoSlideToConfirmBundle');
        self::assertCount(1, $addPathCalls);
    }

    public function testProcessUsesTwigLoaderNativeDefinitionWhenNoAlias(): void
    {
        $container = new ContainerBuilder();
        $loaderDef = new Definition();
        $container->setDefinition('twig.loader.native', $loaderDef);

        $pass = new TwigPathsPass();
        $pass->process($container);

        $calls        = $loaderDef->getMethodCalls();
        $addPathCalls = array_filter($calls, static fn (array $c): bool => $c[0] === 'addPath' && ($c[1][1] ?? '') === 'NowoSlideToConfirmBundle');
        self::assertCount(1, $addPathCalls);
    }

    public function testProcessDoesNothingWhenTwigLoaderNotDefined(): void
    {
        $container   = new ContainerBuilder();
        $countBefore = count($container->getDefinitions());

        $pass = new TwigPathsPass();
        $pass->process($container);

        self::assertCount($countBefore, $container->getDefinitions(), 'Pass must not add definitions when Twig loader is missing.');
    }
}
