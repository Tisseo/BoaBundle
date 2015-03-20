<?php

namespace Tisseo\BOABundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LogType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('datetime', 'datetime',  array('label' => 'log.labels.datetime'));
        $builder->add('table', 'text',  array('label' => 'log.labels.table'));
        $builder->add('action', 'text',  array('label' => 'log.labels.action'));
        $builder->add('previousData', 'text',  array('label' => 'log.labels.previousData'));
        $builder->add('insertedData', 'text',  array('label' => 'log.labels.insertedData'));
        $builder->add('user', 'text',  array('label' => 'log.labels.user'));
        $builder->setAction($options['action']);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Tisseo\EndivBundle\Entity\Log'
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'boa_log';
    }
}	