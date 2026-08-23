<?php

declare(strict_types=1);

namespace Nowo\SlideToConfirmBundle\Tests\Unit\Twig;

use Nowo\SlideToConfirmBundle\Twig\NowoSlideToConfirmTwigExtension;
use PHPUnit\Framework\TestCase;
use Twig\TwigFunction;

final class NowoSlideToConfirmTwigExtensionTest extends TestCase
{
    private NowoSlideToConfirmTwigExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new NowoSlideToConfirmTwigExtension();
    }

    public function testAssetPathReturnsRelativeFilenameForValidFilename(): void
    {
        self::assertSame('slide-to-confirm.js', $this->extension->assetPath('slide-to-confirm.js'));
        self::assertSame('css/theme.css', $this->extension->assetPath('css/theme.css'));
    }

    public function testGetFunctionsContainsExpectedTwigFunctions(): void
    {
        $functions = $this->extension->getFunctions();

        self::assertCount(2, $functions);
        self::assertInstanceOf(TwigFunction::class, $functions[0]);
        self::assertSame('nowo_slide_to_confirm_asset_path', $functions[0]->getName());
        self::assertSame('nowo_slide_to_confirm_asset_package', $functions[1]->getName());
    }

    public function testAssetPackageReturnsConfigurationAlias(): void
    {
        self::assertSame('nowo_slide_to_confirm', $this->extension->assetPackage());
    }

    public function testAssetPathReturnsDefaultForEmptyFilename(): void
    {
        self::assertSame('slide-to-confirm.js', $this->extension->assetPath(''));
    }

    public function testAssetPathReturnsDefaultForPathTraversal(): void
    {
        self::assertSame('slide-to-confirm.js', $this->extension->assetPath('../etc/passwd'));
        self::assertSame('slide-to-confirm.js', $this->extension->assetPath('foo/../../bar'));
    }

    public function testAssetPathReturnsDefaultForUnsafeCharacters(): void
    {
        self::assertSame('slide-to-confirm.js', $this->extension->assetPath('file;.js'));
    }

    public function testAssetPathTrimsLeadingSlash(): void
    {
        self::assertSame('slide-to-confirm.js', $this->extension->assetPath('/slide-to-confirm.js'));
    }
}
