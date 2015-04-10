<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Tisseo\EndivBundle\Entity\StopHistory;
use CrEOF\Spatial\PHP\Types\Geometry\Point;

class StopHistoryType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
         $builder->add('shortName', 'text',
				array(
					'label' => 'stop_history.labels.shortName',
					'required' => true
				)
		);
        $builder->add('longName', 'text',
				array(
					'label' => 'stop_history.labels.longName',
					'required' => false
				)
		);

       $builder->add('startDate', 'date', 
				array(
                    'label' => 'stop_history.labels.startDate',
                    'widget' => 'single_text',
                    'format' => 'dd/MM/yyyy',
					'required' => true
                )
		);
        $builder->add('endDate', 'date',  
				array(
                    'label' => 'stop_history.labels.endDate',
                    'widget' => 'single_text',
                    'format' => 'dd/MM/yyyy',
					'required' => false
                )
		);
/*
        $builder->add('theGeom', 'text',  
				array(
					'data-class' =>new Point(),
					'required' => false
                )
		);
		
	private $id;
    private $theGeom;
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
                'data_class' => 'Tisseo\EndivBundle\Entity\StopHistory'
            )
        );	
	}

			
    /**
     * @return string
     */
    public function getName()
    {
        return 'boa_stop_history';
    }
}
