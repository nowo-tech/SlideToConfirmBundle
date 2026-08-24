<?php

declare(strict_types=1);

namespace Nowo\SlideToConfirmBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\IsTrueValidator;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Same acceptance rules as {@see IsTrueValidator},
 * with messages resolved from {@see SlideConfirmed::TRANSLATION_DOMAIN}.
 */
final class SlideConfirmedValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof SlideConfirmed) {
            throw new UnexpectedTypeException($constraint, SlideConfirmed::class);
        }

        if ($value === null || $value === true || $value === 1 || $value === '1') {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $this->formatValue($value))
            ->setTranslationDomain(SlideConfirmed::TRANSLATION_DOMAIN)
            ->setCode(IsTrue::NOT_TRUE_ERROR)
            ->addViolation();
    }
}
