<?php

namespace Tisseo\BoaBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

use Tisseo\EndivBundle\Entity\Calendar;

class CalendarTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * Transforms an object (calendar) to a string (id).
     *
     * @param  Calendar|null $calendar
     * @return string
     */
    public function transform($calendar)
    {
        if (null === $calendar) {
            return "";
        }

        return $calendar->getId();
    }

    /**
     * Transforms a string (id) to an object (calendar).
     *
     * @param  string $id
     * @return Calendar|null
     * @throws TransformationFailedException if object (calendar) is not found.
     */
    public function reverseTransform($id)
    {
        if (!$id)
            return null;

        $calendar = $this->om
            ->getRepository('TisseoEndivBundle:Calendar')
            ->findOneBy(array('id' => $id));

        return $calendar;
    }
}
