<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Tisseo\EndivBundle\Entity\StopArea;

class StopAreaType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$builder->add('shortName', 'text',
				array(
					'label' => 'stop_area.labels.shortName',
					'required' => false
				)
		);
        $builder->add('longName', 'text',
				array(
					'label' => 'stop_area.labels.longName',
					'required' => false
				)
		);
		
		$builder->add('city', 'entity',
			array(
				'label' => 'stop_area.labels.city',
				'class' => 'TisseoEndivBundle:City',
				'property' => 'name'
			)
		);		

        $builder->setAction($options['action']);
  /*
    private $transferDuration;
    private $theGeom;
*/		
  }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Tisseo\EndivBundle\Entity\StopArea'
            )
        );	
	}

			
    /**
     * @return string
     */
    public function getName()
    {
        return 'boa_stop_area';
    }
}