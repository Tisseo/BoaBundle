<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use Tisseo\EndivBundle\Entity\Stop;
use Tisseo\EndivBundle\Entity\StopHistory;

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

		$builder->add('x', 'text',
			array(
				'mapped' => false,
				'required' => true
			)
		);
		$builder->add('y', 'text',
			array(
				'mapped' => false,
				'required' => true
			)
		);
		
		$builder->add('srid', 'text', 
			array(
				'mapped' => false,
				'label' => 'SRID',
				'data' => '3943'
			)
		);
/*	
		$builder->add('srid', 'choice',  
			array(		
				'mapped' => false,
				'label' => 'SRID',
				'choices'    => StopHistory::getSridList(),
				'data' => 1
			)
		);
*/			

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

