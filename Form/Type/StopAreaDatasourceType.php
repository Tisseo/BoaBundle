<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Tisseo\EndivBundle\Entity\StopAreaDatasource;

class StopAreaDatasourceType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('datasource', 'entity',
            array(
                'label' => 'stop_area_datasource.labels.datasource',
                'required' => true,
                'class' => 'TisseoEndivBundle:Datasource',
                'property' => 'name'
            )
        );

        $builder->add('code', 'text',
                array(
                    'label' => 'stop_area_datasource.labels.code',
                    'required' => false
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
                'data_class' => 'Tisseo\EndivBundle\Entity\StopAreaDatasource'
            )
        );
    }


    /**
     * @return string
     */
    public function getName()
    {
        return 'boa_stop_area_datasource';
    }
}