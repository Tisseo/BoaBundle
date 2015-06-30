<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Tisseo\EndivBundle\Entity\StopArea;

class StopAreaType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'shortName',
                'text',
                array(
                    'label' => 'stop_area.labels.shortName',
                    'required' => false
                )
            )
            ->add(
                'longName',
                'text',
                array(
                    'label' => 'stop_area.labels.longName',
                    'required' => false
                )
            )
            ->add(
                'city',
                'city_selector',
                array(
                    'label' => 'stop_area.labels.city',
                    'required' => false
                )
            )
            ->add(
                'stopAreaDatasources',
                'collection',
                array(
                    'label' => 'stop_area.labels.datasource',
                    'type' => new StopAreaDatasourceType(),
                    'by_reference' => false,
                    'allow_add' => true
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
                'data_class' => 'Tisseo\EndivBundle\Entity\StopArea'
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'boa_stop_area';
    }
}
