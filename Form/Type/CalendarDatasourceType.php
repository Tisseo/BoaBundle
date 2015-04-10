<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CalendarDatasourceType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'datasource',
            'entity',
            array(
                'class' => 'TisseoEndivBundle:Datasource',
                'property' => 'name',
                'label' => 'datasource.labels.name'
            )
        );
        $builder->add(
            'code',
            'text',
            array(
                'label' => 'datasource.labels.code'
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
                'data_class' => 'Tisseo\EndivBundle\Entity\CalendarDatasource'
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'boa_calendar_datasource';
    }
}
