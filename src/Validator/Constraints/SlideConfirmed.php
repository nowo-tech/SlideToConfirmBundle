<?php

declare(strict_types=1);

namespace Nowo\SlideToConfirmBundle\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraints\IsTrue;

/**
 * IsTrue that translates {@see $message} from the NowoSlideToConfirmBundle domain
 * instead of the default validators domain.
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class SlideConfirmed extends IsTrue
{
    public const TRANSLATION_DOMAIN = 'NowoSlideToConfirmBundle';

    public function __construct(?string $message = null, ?array $groups = null, mixed $payload = null)
    {
        parent::__construct(null, $message ?? 'form.error.not_confirmed', $groups, $payload);
    }

    public function validatedBy(): string
    {
        return SlideConfirmedValidator::class;
    }
}
