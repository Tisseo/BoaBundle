<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Tisseo\EndivBundle\Entity\Calendar;
use Tisseo\BoaBundle\Form\Type\CalendarElementType;
use Tisseo\BoaBundle\Form\Type\RemoveElementType;

class CalendarType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                'text',
                array(
                    'label' => 'tisseo.boa.calendar.label.name'
                )
            )
            ->add(
                'calendarType',
                'choice',
                array(
                    'label' => 'tisseo.boa.calendar.label.type',
                    'choices' => Calendar::getCalendarTypes()
                )
            )
            ->add(
                'lineVersion',
                'entity',
                array(
                    'class' => 'TisseoEndivBundle:LineVersion',
                    'property' => 'FormattedLineVersion',
                    'label' => 'tisseo.boa.calendar.label.lineVersion',
                    'required' => false,
                )
            )
            ->add(
                'computedStartDate',
                'datetime',
                array(
                    'label' => 'tisseo.boa.calendar.label.computedStartDate'
                )
            )
            ->add(
                'computedEndDate',
                'datetime',
                array(
                    'label' => 'tisseo.boa.calendar.label.computedEndDate'
                )
            )
            //new calendar_elements container
            ->add(
                'calendarElement',
                'collection',
                array(
                    'type' => new CalendarElementType(),
                    'allow_add' => true,
                    'by_reference' => false,
                    'mapped' => false
                )
            )
            //calendar_elements to remove
            ->add(
                'removeElement',
                'collection',
                array(
                    'type' => new RemoveElementType(),
                    'allow_add' => true,
                    'by_reference' => false,
                    'mapped' => false
                )
            )
            ->setAction($options['action'])
        ;
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
