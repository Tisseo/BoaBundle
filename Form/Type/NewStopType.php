<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use Tisseo\EndivBundle\Entity\Stop;

class NewStopType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$builder->add('stopArea', 'entity', 
			array(
				'label' => 'stop.labels.stopArea',
				'class' => 'TisseoEndivBundle:StopArea',
				'property' => 'name_label',
				'required' => true
			)
		);		
		
		$builder->add('stopHistories', 'collection', 
			array(
				'type' => new StopHistoryType(),
				'allow_add' => true,
				'by_reference' => false,
			)
		);

		$builder->add('stopDatasources', 'collection', 
			array(
				'allow_add' => true,
				'type' => new StopDatasourceType(),
				'by_reference' => false,
			)
		);


        $builder->setAction($options['action']);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Tisseo\EndivBundle\Entity\Stop'
            )
        );	
	}

    /**
     * @return string
     */
    public function getName()
    {
        return 'boa_new_stop';
    }
}

