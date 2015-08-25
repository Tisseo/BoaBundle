<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AgencyType extends AbstractType
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
                    'label' => 'tisseo.boa.agency.label.name'
                )
            )
            ->add(
                'url',
                'text',
                array(
                    'label' => 'tisseo.boa.agency.label.url'
                )
            )
            ->add(
                'timezone',
                'text',
                array(
                    'label' => 'tisseo.boa.agency.label.timezone'
                )
            )
            ->add(
                'lang',
                'text',
                array(
                    'label' => 'tisseo.boa.agency.label.lang'
                )
            )
            ->add(
                'phone',
                'text',
                array(
                    'label' => 'tisseo.boa.agency.label.phone',
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
                'data_class' => 'Tisseo\EndivBundle\Entity\Agency'
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'boa_agency';
    }
}
