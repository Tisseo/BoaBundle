<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Tisseo\EndivBundle\Entity\StopHistory;

class StopHistoryCloseType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'startDate',
                'date',
                array(
                    'label' => 'tisseo.boa.stop_history.label.start_date',
                    'read_only' => true,
                    'widget' => 'single_text',
                    'format' => 'dd/MM/yyyy'
                )
            )
            ->add(
                'endDate',
                'tisseo_datepicker',
                array(
                    'label' => 'tisseo.boa.stop_history.label.end_date',
                    'attr' => array(
                        'data-to-date' => true
                    )
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
                'data_class' => 'Tisseo\EndivBundle\Entity\StopHistory',
                'validation_groups' => array('StopHistory', 'close')
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'boa_stop_history_close';
    }
}
