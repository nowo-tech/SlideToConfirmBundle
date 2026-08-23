<?php

declare(strict_types=1);

namespace Nowo\SlideToConfirmBundle\Form;

/**
 * Visual / semantic variant for the slide-to-confirm track.
 */
enum SlideToConfirmVariant: string
{
    case Default = 'default';
    case Danger  = 'danger';
    case Success = 'success';
    case Payment = 'payment';
    case Legal   = 'legal';
}
