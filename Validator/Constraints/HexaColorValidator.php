<?php

namespace Tisseo\BoaBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * HexaColorValidator
 */
class HexaColorValidator extends ConstraintValidator
{
    /**
     * Checks if the passed value is valid.
     *
     * @param mixed      $value      The value that should be validated
     * @param Constraint $constraint The constrain for the validation
     *
     * @return bool Whether or not the value is valid
     */
    public function validate($value, Constraint $constraint)
    {
        if (!preg_match('/^#(([0-9a-fA-F]){2}){3}$/', $value, $matches)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%string%', $value)
                ->addViolation();
        }
    }
}
