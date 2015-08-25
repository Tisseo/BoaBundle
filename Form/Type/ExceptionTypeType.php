<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ExceptionTypeType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'label',
                'text',
                array(
                    'label' => 'tisseo.boa.exception_type.label.label'
                )
            )
            ->add(
                'exceptionText',
                'text',
                array(
                    'label' => 'tisseo.boa.exception_type.label.exception_text'
                )
            )
            ->add(
                'gridCalendarPattern',
                'text',
                array(
                    'label' => 'tisseo.boa.exception_type.label.grid_calendar_pattern',
                    'max_length' => 7
                )
            )
            ->add(
                'tripCalendarPattern',
                'text',
                array(
                    'label' => 'tisseo.boa.exception_type.label.trip_calendar_pattern',
                    'max_length' => 7
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
                'data_class' => 'Tisseo\EndivBundle\Entity\ExceptionType'
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'boa_exception_type';
    }
}
