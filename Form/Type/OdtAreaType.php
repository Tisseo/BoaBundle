<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OdtAreaType extends AbstractType
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
                    'label' => 'tisseo.boa.odt_area.label.name',
                    'required' => true
                )
            )
            ->add(
                'comment',
                'text',
                array(
                    'label' => 'tisseo.boa.odt_area.label.comments',
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
                'data_class' => 'Tisseo\EndivBundle\Entity\OdtArea'
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'boa_odt_area';
    }
}
