<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use Tisseo\EndivBundle\Entity\Stop;

class StopType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$stop= $builder->getData();
		
		$builder->add('id', 'text');
			
		$builder->add('short_name', 'entity',
			array(
				'mapped' => false,
				'label' => 'stop.labels.shortName',
				'class' => 'TisseoEndivBundle:StopHistory',
				'property' => 'shortName',
				'query_builder' => function(EntityRepository $er)  use ( $stop ) {
					return $er->createQueryBuilder('s')
						->where("IDENTITY(s.stop) = :id")
						->andWhere("s.startDate <= CURRENT_DATE()")
						->andWhere("s.endDate IS NULL or s.endDate >= CURRENT_DATE()")
						->setParameter('id', $stop);
				}
			)
		);

		$builder->add('long_name', 'entity',
			array(
				'mapped' => false,
				'label' => 'stop.labels.longName',
				'class' => 'TisseoEndivBundle:StopHistory',
				'property' => 'longName',
				'query_builder' => function(EntityRepository $er)  use ( $stop ) {
					return $er->createQueryBuilder('s')
						->where("IDENTITY(s.stop) = :id")
						->andWhere("s.startDate <= CURRENT_DATE()")
						->andWhere("s.endDate IS NULL or s.endDate >= CURRENT_DATE()")
						->setParameter('id', $stop);
				}
			)
		);

		$builder->add('stopArea', 'entity', 
			array(
				'label' => 'stop.labels.stopArea',
				'class' => 'TisseoEndivBundle:StopArea',
				'property' => 'name_label'
			)
		);		

		$builder->add('stopDatasources', 'collection', 
			array(
				'label' => 'stop.labels.datasource',
				'type' => new StopDatasourceType(),
				'by_reference' => false,
			)
		);

		$builder->add('phantoms', 'collection', 
			array(
				'label' => 'stop.labels.phantoms',
				'type' => new StopPhantomType(),
				'by_reference' => false,
			)
		);

/*		
		$builder->add('stopHistories', 'collection', 
			array(
				'type' => new StopHistoryType(),
				'allow_add' => true,
				'by_reference' => false,
			)
		);

		$builder->add('stopAccessibilities', 'collection', 
			array(
				'type' => new StopAccessibilityType(),
				'allow_add' => true,
				'by_reference' => false,
			)
		);
*/		
		
		$builder->add('masterStop', 'stop_selector',
			array(
				'label' =>  'stop.labels.masterStop',
				'required' => false
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
        return 'boa_stop';
    }
}

