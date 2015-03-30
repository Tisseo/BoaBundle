<?php

namespace Tisseo\BOABundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Tisseo\EndivBundle\Entity\Calendar;

class CalendarType extends AbstractType
{
	private $CalendarElementManager;
		
    public function __construct($CalendarElementManager)
    {
        $this->calendarElementManager = $CalendarElementManager;
    }	

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

		$builder->add('name', 'text',  array('label' => 'calendar.labels.name'));
		$builder->add('calendarType', 'choice',  
			array(			
				'label' => 'calendar.labels.type',
				'choices'    => Calendar::getCalendarTypes()
			)
		);
		$builder->add('lineVersion','entity',
				array(
					'class' => 'TisseoEndivBundle:LineVersion',
					'property' => 'FormattedLineVersion',
					'label' => 'calendar.labels.lineVersion'
				)
		);
		
		$builder->add('computedStartDate', 'datetime',  array('label' => 'calendar.labels.computedStartDate'));
		$builder->add('computedEndDate', 'datetime',  array('label' => 'calendar.labels.computedEndDate'));

		$builder->setAction($options['action']);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Tisseo\EndivBundle\Entity\Calendar'
            )
        );	
	}

			
    /**
     * @return string
     */
    public function getName()
    {
        return 'boa_calendar';
    }
}