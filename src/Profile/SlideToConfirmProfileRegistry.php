<?php

declare(strict_types=1);

namespace Nowo\SlideToConfirmBundle\Profile;

use InvalidArgumentException;

use function sprintf;

/**
 * Resolves named slide-to-confirm profiles from bundle configuration.
 *
 * @phpstan-type Profile array{
 *     text: string,
 *     confirmed_text: string,
 *     hint: string,
 *     variant: string,
 *     threshold: float,
 *     submit_on_confirm: bool,
 *     reset_on_release: bool
 * }
 */
final class SlideToConfirmProfileRegistry
{
    /**
     * @param array<string, Profile> $profiles
     */
    public function __construct(
        private readonly string $defaultProfile,
        private readonly array $profiles,
    ) {
    }

    public function getDefaultProfileName(): string
    {
        return $this->defaultProfile;
    }

    /**
     * @return array<string, Profile>
     */
    public function all(): array
    {
        return $this->profiles;
    }

    public function has(string $name): bool
    {
        return isset($this->profiles[$name]);
    }

    /**
     * @return Profile
     */
    public function get(?string $name = null): array
    {
        $key = $name ?? $this->defaultProfile;
        if (!isset($this->profiles[$key])) {
            throw new InvalidArgumentException(sprintf('Unknown slide-to-confirm profile "%s".', $key));
        }

        return $this->profiles[$key];
    }
}
