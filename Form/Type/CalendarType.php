<?php

namespace Tisseo\BOABundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Tisseo\EndivBundle\Entity\Calendar;

class CalendarType extends AbstractType
{
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
		$builder->add('calendar_elements', 'collection',
				array(
                    'label' => False,
                    'type' => new CalendarElementType(),
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
					'cascade_validation' => true
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