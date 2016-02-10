<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DepotType extends AbstractType
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
                    'label' => 'tisseo.boa.depot.label.short_name',
                    'required' => false
                )
            )
            ->add(
                'longName',
                'text',
                array(
                    'label' => 'tisseo.boa.depot.label.long_name',
                    'required' => false
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
                'data_class' => 'Tisseo\EndivBundle\Entity\Depot'
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'boa_depot';
    }
}
