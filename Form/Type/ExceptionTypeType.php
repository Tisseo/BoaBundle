<?php

namespace Tisseo\BOABundle\Form\Type;

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
        $builder->add('label', 'text',  array('label' => 'exception_type.labels.label'));
        $builder->add('exceptionText', 'text',  array('label' => 'exception_type.labels.exceptionText'));
        $builder->add('gridCalendarPattern', 'text',  array('label' => 'exception_type.labels.gridCalendarPattern', 'max_length' => 7));
        $builder->add('tripCalendarPattern', 'text',  array('label' => 'exception_type.labels.tripCalendarPattern', 'max_length' => 7));
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
