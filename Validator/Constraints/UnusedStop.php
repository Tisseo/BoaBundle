<?php

namespace Tisseo\BoaBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UnusedStop extends Constraint
{
    public $message = 'tisseo.boa.error.unused_stop';

    public function validatedBy()
    {
        return 'tisseo_boa.validator.unused_stop';
    }
}
