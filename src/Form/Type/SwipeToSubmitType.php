<?php

declare(strict_types=1);

namespace Nowo\SlideToConfirmBundle\Form\Type;

/**
 * Semantic alias of SlideToConfirmType for swipe-to-submit form actions.
 *
 * Same widget, same options, same server-side confirmation checkbox.
 */
final class SwipeToSubmitType extends SlideToConfirmType
{
    public function getBlockPrefix(): string
    {
        return 'nowo_swipe_to_submit';
    }
}
