<?php

namespace Tisseo\BOABundle\Form\Type;

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
        $builder->add('name', 'text',  array('label' => 'agency.labels.name'));
        $builder->add('url', 'text',  array('label' => 'agency.labels.url'));
        $builder->add('timezone', 'text',  array('label' => 'agency.labels.timezone'));
        $builder->add('lang', 'text',  array('label' => 'agency.labels.lang'));
        $builder->add('phone', 'text',  array('label' => 'agency.labels.phone','required' => false));
        $builder->setAction($options['action']);
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