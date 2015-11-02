<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class NonConcurrencyType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'priorityLine',
                'entity',
                array(
                    'label' => 'tisseo.boa.non_concurrency.label.priority_line',
                    'class' => 'TisseoEndivBundle:Line',
                    'property' => 'number',
                    'required' => true,
                    'placeholder' => ''
                )
            )
            ->add(
                'nonPriorityLine',
                'entity',
                array(
                    'label' => 'tisseo.boa.non_concurrency.label.non_priority_line',
                    'class' => 'TisseoEndivBundle:Line',
                    'property' => 'number',
                    'required' => true,
                    'placeholder' => ''
                )
            )
            ->add(
                'time',
                'integer',
                array(
                    'label' => 'tisseo.boa.non_concurrency.label.duration',
                    'required' => true
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
                'data_class' => 'Tisseo\EndivBundle\Entity\NonConcurrency'
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'boa_non_concurrency';
    }
}

