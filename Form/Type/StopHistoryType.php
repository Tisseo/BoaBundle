<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Tisseo\EndivBundle\Entity\StopHistory;
use CrEOF\Spatial\PHP\Types\Geometry\Point;

class StopHistoryType extends AbstractType
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
                    'label' => 'stop_history.labels.short_name',
                    'required' => true
                )
            )
            ->add(
                'longName',
                'text',
                array(
                    'label' => 'stop_history.labels.long_name',
                    'required' => false
                )
            )
            ->add(
                'startDate',
                'tisseo_datepicker',
                array(
                    'label' => 'stop_history.labels.start_date',
                    'attr' => array(
                        'data-to-date' => true
                    )
                )
            )
            ->add(
                'x',
                'text',
                array(
                    'label' => 'stop_history.labels.x',
                    'mapped' => false,
                    'required' => true
                )
            )
            ->add(
                'y',
                'text',
                array(
                    'label' => 'stop_history.labels.y',
                    'mapped' => false,
                    'required' => true
                )
            )
            ->add(
                'srid',
                'text',
                array(
                    'label' => 'stop_history.labels.srid',
                    'mapped' => false,
                    'data' => '3943',
                    'read_only' => true,
                    'required' => true
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
                'validation_groups' => array('StopHistory', 'edit')
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'boa_stop_history';
    }
}
