<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Tisseo\EndivBundle\Entity\AccessibilityType;
use Tisseo\BoaBundle\Form\Type\CalendarAccessibilityType;

class AccessibilityTypeType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
/*
    private $calendar;
*/		
         $builder->add('startTime', 'time',
				array(
					'label' => 'accessibility_type.labels.startTime',
					'input'  => 'timestamp',
					'widget' => 'single_text',
					'required' => false
				)
		);
         $builder->add('endTime', 'time',
				array(
					'label' => 'accessibility_type.labels.endTime',
					'input'  => 'timestamp',
					'widget' => 'single_text',
					'required' => false
				)
		);
         $builder->add('isRecursive', 'checkbox',
				array(
					'label' => 'accessibility_type.labels.isRecursive',
					'required' => false
				)
		);		
		$builder->add('accessibilityMode', 'entity',  
			array(
				'required' => true,
				'label' => 'accessibility_type.labels.accessibilityMode',
				'class' => 'Tisseo\EndivBundle\Entity\AccessibilityMode',
				'property' => 'name'
			)
		);
		
		$builder->add('calendar', 'calendar_selector',
			array(
				'label' => 'Calendrier'
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
                'data_class' => 'Tisseo\EndivBundle\Entity\AccessibilityType'
            )
        );	
	}

			
    /**
     * @return string
     */
    public function getName()
    {
        return 'boa_accessibility_type';
    }
}
