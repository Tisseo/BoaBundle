<?php

namespace Tisseo\BoaBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

use Tisseo\EndivBundle\Entity\Trip;

class TripTransformer implements DataTransformerInterface
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
     * Transforms an object (trip) to a string (id).
     *
     * @param  Trip|null $trip
     * @return string
     */
    public function transform($trip)
    {
        if (null === $trip) {
            return "";
        }

        return $trip->getId();
    }

    /**
     * Transforms a string (id) to an object (trip).
     *
     * @param  string $id
     * @return Stop|null
     * @throws TransformationFailedException if object (trip) is not found.
     */
    public function reverseTransform($id)
    {
        if (!$id) {
            return null;
        }

        $trip = $this->om
            ->getRepository('TisseoEndivBundle:Trip')
            ->findOneBy(array('id' => $id));

        if (null === $trip) {
            return null;
        }

        return $trip;
    }
}

