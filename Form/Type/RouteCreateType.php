<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Tisseo\EndivBundle\Entity\Route;

class RouteCreateType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text',
            array(
                'label' => 'route.labels.name'
            )
        )
        ->add('direction', 'text',
            array(
                'label' => 'route.labels.direction'
            )
        )
        ->add('way', 'choice',
            array(
                'label' => 'route.labels.way',
                'required'    => false,
                'empty_value'  => '',
                'empty_data'  => '',
                'choices'    => Route::getWayValues()
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
                'data_class' => 'Tisseo\EndivBundle\Entity\Route'
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'boa_route_create';
    }
}
