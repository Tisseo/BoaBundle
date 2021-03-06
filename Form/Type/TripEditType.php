<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TripEditType extends AbstractType
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
                    'label' => 'tisseo.boa.trip.label.name'
                )
            )
            ->add(
                'dayCalendar',
                'text',
                array(
                    'label' => 'tisseo.boa.trip.label.day_calendar',
                    'data_class' => 'Tisseo\EndivBundle\Entity\Calendar',
                    'read_only' => true,
                    'required' => false,
                    'disabled' => true
                )
            )
            ->add(
                'periodCalendar',
                'text',
                array(
                    'label' => 'tisseo.boa.trip.label.period_calendar',
                    'data_class' => 'Tisseo\EndivBundle\Entity\Calendar',
                    'read_only' => true,
                    'required' => false,
                    'disabled' => true
                )
            )
            ->add(
                'pattern',
                'text',
                array(
                    'label' => 'tisseo.boa.trip.label.pattern',
                    'data_class' => 'Tisseo\EndivBundle\Entity\Trip',
                    'read_only' => true,
                    'required' => false,
                    'disabled' => true
                )
            )
            ->add(
                'tripDatasources',
                'collection',
                array(
                    'type' => new TripDatasourceType()
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
                'data_class' => 'Tisseo\EndivBundle\Entity\Trip'
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'boa_trip_edit';
    }
}
