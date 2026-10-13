<?php

declare(strict_types=1);

namespace Nowo\SlideToConfirmBundle\Tests\Unit\Validator\Constraints;

use Nowo\SlideToConfirmBundle\Validator\Constraints\SlideConfirmed;
use Nowo\SlideToConfirmBundle\Validator\Constraints\SlideConfirmedValidator;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @extends ConstraintValidatorTestCase<SlideConfirmedValidator>
 */
final class SlideConfirmedValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): SlideConfirmedValidator
    {
        return new SlideConfirmedValidator();
    }

    public function testTrueIsValid(): void
    {
        $this->validator->validate(true, new SlideConfirmed());
        $this->assertNoViolation();
    }

    public function testFalseRaisesBundleDomainViolation(): void
    {
        $constraint = new SlideConfirmed();
        $this->validator->validate(false, $constraint);

        $this->buildViolation('form.error.not_confirmed')
            ->setParameter('{{ value }}', 'false')
            ->setCode(IsTrue::NOT_TRUE_ERROR)
            ->assertRaised();
    }

    public function testValidatedByUsesDedicatedValidator(): void
    {
        self::assertSame(SlideConfirmedValidator::class, (new SlideConfirmed())->validatedBy());
        self::assertSame('NowoSlideToConfirmBundle', SlideConfirmed::TRANSLATION_DOMAIN);
    }

    public function testRejectsForeignConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->validator->validate(true, new NotBlank());
    }
}
