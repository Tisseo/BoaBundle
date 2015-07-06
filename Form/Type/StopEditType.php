<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\EntityRepository;
use Tisseo\EndivBundle\Entity\Stop;
use Tisseo\EndivBundle\Entity\StopHistory;

class StopEditType extends AbstractType
{
    private $stopHistory = null;

    public function __construct(StopHistory $stopHistory)
    {
        $this->stopHistory = $stopHistory;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'stopArea',
                'entity',
                array(
                    'label' => 'stop.labels.stop_area',
                    'class' => 'TisseoEndivBundle:StopArea',
                    'property' => 'nameLabel',
                    'empty_data' => '',
                    'empty_value' => ''
                )
            )
            ->add(
                'stopDatasources',
                'collection',
                array(
                    'label' => 'stop.labels.datasource',
                    'type' => new StopDatasourceType(),
                    'by_reference' => false,
                )
            )
            ->add(
                'shortName',
                'text',
                array(
                    'label' => 'stop.labels.short_name',
                    'data' => $this->stopHistory->getShortName(),
                    'mapped' => false,
                    'required' => false,
                    'read_only' => true,
                    'disabled' => true
                )
            )
            ->add(
                'longName',
                'text',
                array(
                    'label' => 'stop.labels.long_name',
                    'data' => $this->stopHistory->getLongName(),
                    'mapped' => false,
                    'required' => false,
                    'read_only' => true,
                    'disabled' => true
                )
            )
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
                $form = $event->getForm();
                $stop = $event->getData();
                
                $form
                    ->add(
                        'masterStop',
                        'entity',
                        array(
                            'label' => 'stop.labels.master_stop',
                            'required' => false,
                            'empty_value' => '',
                            'empty_data' => null,
                            'class' => 'TisseoEndivBundle:Stop',
                            'property' => 'StopLabel',
                            'query_builder' => function(EntityRepository $er)  use ($stop) {
                                return $er->createQueryBuilder('s')
                                    ->where("s.stopArea = :sa")
                                    ->andWhere("s.masterStop is null")
                                    ->setParameter('sa', $stop->getStopArea());
                            },
                            'disabled' => true // TODO: FOR NOW THIS IS DISABLED
                        )
                    )
                ;
            })
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
                'data_class' => 'Tisseo\EndivBundle\Entity\Stop'
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'boa_stop_edit';
    }
}
