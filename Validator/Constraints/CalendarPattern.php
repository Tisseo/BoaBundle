<?php

namespace Tisseo\BOABundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CalendarPattern extends Constraint
{
    public $message = 'error.calendar_pattern';
}
