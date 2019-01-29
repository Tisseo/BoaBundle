<?php

namespace Tisseo\BoaBundle\Validator\Constraints;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * CalendarPatternValidator
 */
class UnusedStopValidator extends ConstraintValidator
{
    private $om = null;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

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
        $conn = $this->om->getConnection();
        $query = '
            SELECT DISTINCT lv.id
            FROM stop s
            LEFT JOIN odt_stop os ON os.stop_id = s.id
            JOIN route_stop rs ON s.id = rs.waypoint_id OR os.odt_area_id = rs.waypoint_id
            JOIN route r ON rs.route_id = r.id
            JOIN line_version lv ON r.line_version_id = lv.id
            WHERE(:val < COALESCE(lv.end_date, lv.planned_end_date)) and :stopId = s.id
        ';

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':val', $value->format('Y-m-d'));
        $stmt->bindValue(':stopId', $this->context->getObject()->getStop()->getId());

        $stmt->execute();
        $result = $stmt->fetchAll();

        if (count($result) > 0) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
