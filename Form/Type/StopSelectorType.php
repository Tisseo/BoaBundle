<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Tisseo\BoaBundle\Form\DataTransformer\StopTransformer;

class StopSelectorType extends AbstractType
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

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new StopTransformer($this->om);
        $builder->addModelTransformer($transformer);
    }

/*	
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Tisseo\EndivBundle\Entity\Stop'
            )
        );	
	}
	

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'invalid_message' => 'The selected stop does not exist',
        ));
    }
*/
	
    public function getParent()
    {
        return 'text';
    }

    public function getName()
    {
        return 'stop_selector';
    }
}