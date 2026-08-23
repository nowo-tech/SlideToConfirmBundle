<?php

declare(strict_types=1);

namespace Nowo\SlideToConfirmBundle\Twig;

use Nowo\SlideToConfirmBundle\DependencyInjection\Configuration;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Exposes safe relative asset filenames and the named Symfony asset package (REQ-ASSETS-004).
 *
 * Prefer: asset(nowo_slide_to_confirm_asset_path('slide-to-confirm.js'), nowo_slide_to_confirm_asset_package())
 * or:     asset('slide-to-confirm.js', 'nowo_slide_to_confirm')
 */
final class NowoSlideToConfirmTwigExtension extends AbstractExtension
{
    /**
     * Directory name under public/bundles/ where assets:install publishes this bundle.
     */
    public const ASSET_DIR = 'nowoslidetoconfirm';

    /**
     * Safe character set for asset path segments (alphanumeric, dot, hyphen, underscore, slash for subpaths).
     */
    private const SAFE_FILENAME_PATTERN = '#^[a-zA-Z0-9._/-]+$#';

    /**
     * Default JS filename used when the requested path is empty or unsafe.
     */
    private const DEFAULT_ASSET = 'slide-to-confirm.js';

    /**
     * @return array<int, TwigFunction>
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('nowo_slide_to_confirm_asset_path', $this->assetPath(...)),
            new TwigFunction('nowo_slide_to_confirm_asset_package', $this->assetPackage(...)),
        ];
    }

    /**
     * Returns a relative filename under the named asset package (REQ-ASSETS-004).
     *
     * @param string $filename Filename relative to the package base (e.g. "slide-to-confirm.js")
     */
    public function assetPath(string $filename): string
    {
        $filename = ltrim($filename, '/');
        if ($filename === '' || str_contains($filename, '..') || preg_match(self::SAFE_FILENAME_PATTERN, $filename) !== 1) {
            return self::DEFAULT_ASSET;
        }

        return $filename;
    }

    /**
     * Named Symfony asset package (same as Configuration::ALIAS).
     */
    public function assetPackage(): string
    {
        return Configuration::ALIAS;
    }
}
