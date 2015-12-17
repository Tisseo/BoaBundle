<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CityCreateType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            'text',
            array(
                'label' => 'tisseo.boa.city.label.name',
                'required' => true,
            )
        );
        $builder->add(
            'insee',
            'text',
            array(
                'label' => 'tisseo.boa.city.label.insee',
                'required' => true,
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
                'data_class' => 'Tisseo\EndivBundle\Entity\City'
            )
        );
    }


    /**
     * @return string
     */
    public function getName()
    {
        return 'boa_city_create';
    }
}
