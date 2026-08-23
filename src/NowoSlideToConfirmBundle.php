<?php

declare(strict_types=1);

namespace Nowo\SlideToConfirmBundle;

use Nowo\SlideToConfirmBundle\DependencyInjection\Compiler\TwigPathsPass;
use Nowo\SlideToConfirmBundle\DependencyInjection\SlideToConfirmExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Symfony bundle that provides SlideToConfirmType / SwipeToSubmitType form widgets.
 *
 * Sliding the thumb to the end of the track confirms the action and optionally submits the form.
 */
final class NowoSlideToConfirmBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new TwigPathsPass());
    }

    /**
     * Returns the container extension that loads the bundle configuration and services.
     *
     * @return ExtensionInterface The extension instance (cached after first call)
     */
    public function getContainerExtension(): ExtensionInterface
    {
        if ($this->extension instanceof ExtensionInterface) {
            return $this->extension;
        }

        $this->extension = new SlideToConfirmExtension();

        return $this->extension;
    }
}
