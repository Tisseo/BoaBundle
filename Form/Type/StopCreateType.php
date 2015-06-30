<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class StopCreateType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'stopArea',
                'entity',
                array(
                    'label' => 'stop.labels.stop_area',
                    'class' => 'TisseoEndivBundle:StopArea',
                    'property' => 'nameLabel',
                    'required' => true
                )
            )
            ->add(
                'stopHistories',
                'collection',
                array(
                    'type' => new StopHistoryType(),
                    'label' => 'stop.labels.stop_history',
                    'by_reference' => false
                )
            )
            ->add(
                'stopDatasources',
                'collection',
                array(
                    'type' => new StopDatasourceType(),
                    'label' => 'stop.labels.stop_datasource',
                    'by_reference' => false
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
                'data_class' => 'Tisseo\EndivBundle\Entity\Stop'
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'boa_stop_create';
    }
}

