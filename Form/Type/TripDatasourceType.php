<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TripDatasourceType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'datasource',
                'entity',
                array(
                    'label' => 'datasource.labels.datasource',
                    'class' => 'TisseoEndivBundle:Datasource',
                    'property' => 'name',
                    'required' => true
                )
            )
            ->add(
                'code',
                'text',
                array(
                    'label' => 'datasource.labels.code',
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
                'data_class' => 'Tisseo\EndivBundle\Entity\TripDatasource'
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'boa_trip_datasource';
    }
}
