<?php

namespace Tisseo\BoaBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

use Tisseo\EndivBundle\Entity\Stop;

class StopTransformer implements DataTransformerInterface
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
     * Transforms an object (stop) to a string (id).
     *
     * @param  Stop|null $stop
     * @return string
     */
    public function transform($stop)
    {
        if (null === $stop) {
            return "";
        }

        return $stop->getId();
    }

    /**
     * Transforms a string (id) to an object (stop).
     *
     * @param  string $id
     * @return Stop|null
     * @throws TransformationFailedException if object (stop) is not found.
     */
    public function reverseTransform($id)
    {
        if (!$id) {
            return null;
        }

        $stop = $this->om
            ->getRepository('TisseoEndivBundle:Stop')
            ->findOneBy(array('id' => $id));

        if (null === $stop) {
            return null;
/*
            throw new TransformationFailedException(sprintf(
                "Le calendrier avec l'id "%s" ne peut pas être trouvé!",
                $id
            ));
*/
        }

        return $stop;
    }
}

