<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class StopDatasourceType extends AbstractType
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
                    'label' => 'tisseo.boa.datasource.label.datasource',
                    'class' => 'TisseoEndivBundle:Datasource',
                    'property' => 'name',
                    'required' => true
                )
            )
            ->add(
                'code',
                'text',
                array(
                    'label' => 'tisseo.boa.datasource.label.code'
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
                'data_class' => 'Tisseo\EndivBundle\Entity\StopDatasource'
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'boa_stop_datasource';
    }
}
