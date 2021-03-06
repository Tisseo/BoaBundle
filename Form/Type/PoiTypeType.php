<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PoiTypeType extends AbstractType
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
                    'label' => 'tisseo.boa.poi_type.label.name',
                    'required' => true
                )
            )
            ->setAction($options['action'])
            ->add(
                'long_name',
                'text',
                array(
                    'label' => 'tisseo.boa.poi_type.label.long_name',
                    'required' => false
                )
            )
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Tisseo\EndivBundle\Entity\PoiType'
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'boa_poi_type';
    }
}
