<?php

namespace Tisseo\BoaBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

use Tisseo\EndivBundle\Entity\City;

class CityTransformer implements DataTransformerInterface
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
     * Transforms an object (city) to a string (id).
     *
     * @param  City|null $city
     * @return string
     */
    public function transform($city)
    {
        if (null === $city) {
            return "";
        }

        return $city->getId();
    }

    /**
     * Transforms a string (id) to an object (city).
     *
     * @param  string $id
     * @return City|null
     * @throws TransformationFailedException if object (city) is not found.
     */
    public function reverseTransform($id)
    {
        if (!$id) {
			return null; 
		}

        $city = $this->om
            ->getRepository('TisseoEndivBundle:City')
            ->findOneBy(array('id' => $id));

        if (null === $city) {
			return null;
/*			
            throw new TransformationFailedException(sprintf(
                "Le calendrier avec l'id "%s" ne peut pas être trouvé!",
                $id
            ));
*/			
        }

        return $city;
    }
}

