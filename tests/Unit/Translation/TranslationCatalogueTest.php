<?php

declare(strict_types=1);

namespace Nowo\SlideToConfirmBundle\Tests\Unit\Translation;

use Nowo\SlideToConfirmBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

use function dirname;
use function is_array;
use function is_string;

final class TranslationCatalogueTest extends TestCase
{
    private const LOCALES = ['en', 'es', 'it', 'fr', 'pt', 'de', 'nl'];

    private const DOMAIN = 'NowoSlideToConfirmBundle';

    public function testRequiredLocalesShareTheSameKeysAndNonEmptyValues(): void
    {
        $dir      = dirname(__DIR__, 3) . '/src/Resources/translations';
        $leavesEn = $this->leafMap($dir . '/' . self::DOMAIN . '.en.yaml');

        self::assertNotEmpty($leavesEn);

        foreach (self::LOCALES as $locale) {
            $path   = $dir . '/' . self::DOMAIN . '.' . $locale . '.yaml';
            $leaves = $this->leafMap($path);
            self::assertSame(
                array_keys($leavesEn),
                array_keys($leaves),
                'Leaf keys for ' . $locale . ' must match en',
            );
            foreach ($leaves as $key => $value) {
                self::assertNotSame('', $value, $locale . ' has an empty value for ' . $key);
            }
        }
    }

    public function testBuiltinProfileKeysAndConstraintMessageExistInEnglishCatalogue(): void
    {
        $dir    = dirname(__DIR__, 3) . '/src/Resources/translations';
        $leaves = $this->leafMap($dir . '/' . self::DOMAIN . '.en.yaml');

        self::assertArrayHasKey('form.error.not_confirmed', $leaves);

        foreach (Configuration::builtinProfiles() as $name => $profile) {
            foreach (['text', 'confirmed_text', 'hint'] as $field) {
                $key = $profile[$field];
                self::assertArrayHasKey($key, $leaves, 'Profile "' . $name . '" ' . $field . ' key missing: ' . $key);
            }
        }
    }

    /**
     * @return array<string, string>
     */
    private function leafMap(string $path): array
    {
        self::assertFileExists($path);
        $parsed = Yaml::parseFile($path);
        self::assertIsArray($parsed);

        $leaves = [];
        $this->collectLeaves($parsed, '', $leaves);
        ksort($leaves);

        return $leaves;
    }

    /**
     * @param array<mixed> $data
     * @param array<string, string> $leaves
     */
    private function collectLeaves(array $data, string $prefix, array &$leaves): void
    {
        foreach ($data as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix . '.' . $key;
            if (is_array($value)) {
                $this->collectLeaves($value, $path, $leaves);

                continue;
            }

            self::assertTrue(is_string($value), 'Translation value for ' . $path . ' must be a string');
            $leaves[$path] = $value;
        }
    }
}
