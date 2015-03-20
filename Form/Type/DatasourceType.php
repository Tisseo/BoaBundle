<?php

namespace Tisseo\BOABundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DatasourceType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text',  array('label' => 'datasource.labels.name'));
        $builder->add('agency', 'entity',
            array(
                'class' => 'TisseoEndivBundle:Agency',
                'property' => 'name',
                'label' => 'datasource.labels.agency'
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
                'data_class' => 'Tisseo\EndivBundle\Entity\Datasource'
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'boa_datasource';
    }
}