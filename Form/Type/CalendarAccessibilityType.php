<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CalendarAccessibilityType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $calendar = $builder->getData();

        $builder->add('name', 'text', array('label' => 'calendar.labels.name'));

        $builder->add('computedStartDate', 'date',
            array(
                'read_only' => true,
                'label' => 'calendar.labels.computedStartDate',
                'widget' => 'single_text',
            )
        );
        $builder->add('computedEndDate', 'date',
            array(
                'read_only' => true,
                'label' => 'calendar.labels.computedEndDate',
                'widget' => 'single_text',
            )
        );
        $builder->add('calendarDatasources', 'collection',
            array(
                'read_only' => true,
                'type' => new CalendarDatasourceType(),
                'allow_add' => true,
                'by_reference' => false
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
        return 'boa_calendar_accessibility';
    }
}
