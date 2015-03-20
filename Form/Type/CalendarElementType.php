<?php

namespace Tisseo\BOABundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Tisseo\EndivBundle\Entity\CalendarElement;

class CalendarElementType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('startDate', 'date', 
				array(
                    'label' => 'calendar_element.labels.startDate',
                    'widget' => 'single_text',
                    'format' => 'dd/MM/yyyy'
                )
		);
        $builder->add('endDate', 'date',  
				array(
                    'label' => 'calendar_element.labels.endDate',
                    'widget' => 'single_text',
                    'format' => 'dd/MM/yyyy'
                )
		);
		$builder->add('operator', 'choice',  
			array(			
				'label' => 'calendar_element.labels.operator',
				'choices'    => CalendarElement::getOperatorValues()
			)
		);
        $builder->add('interval', 'text',
				array(
					'label' => 'calendar_element.labels.interval',
					'required' => false
				)
		);
		$builder->add('includedCalendar','entity',
				array(
					'required' => false,
					'class' => 'TisseoEndivBundle:Calendar',
					'property' => 'name',
					'label' => 'calendar_element.labels.includedCalendar'
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
                'data_class' => 'Tisseo\EndivBundle\Entity\CalendarElement'
            )
        );	
	}

			
    /**
     * @return string
     */
    public function getName()
    {
        return 'boa_calendar_elements';
    }
}