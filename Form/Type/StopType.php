<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Doctrine\ORM\EntityRepository;

use Tisseo\EndivBundle\Entity\Stop;

class StopType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('stopArea', 'entity',
            array(
                'label' => 'stop.labels.stopArea',
                'class' => 'TisseoEndivBundle:StopArea',
                'property' => 'name_label'
            )
        );

        $builder->add('stopDatasources', 'collection',
            array(
                'label' => 'stop.labels.datasource',
                'type' => new StopDatasourceType(),
                'by_reference' => false,
            )
        );

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event)
        {
            $form = $event->getForm();
            $stop = $event->getData();

            /* Check we're looking at the right data/form */
            if ($stop instanceof Stop) {
                $form->add('short_name', 'entity',
                    array(
                        'mapped' => false,
                        'label' => 'stop.labels.shortName',
                        'class' => 'TisseoEndivBundle:StopHistory',
                        'property' => 'shortName',
                        'query_builder' => function(EntityRepository $er)  use ( $stop ) {
                            return $er->createQueryBuilder('s')
                                ->where("IDENTITY(s.stop) = :id")
                                ->andWhere("s.startDate <= CURRENT_DATE()")
                                ->andWhere("s.endDate IS NULL or s.endDate >= CURRENT_DATE()")
                                ->setParameter('id', $stop);
                        }
                    )
                );

                $form->add('long_name', 'entity',
                    array(
                        'mapped' => false,
                        'label' => 'stop.labels.longName',
                        'class' => 'TisseoEndivBundle:StopHistory',
                        'property' => 'longName',
                        'query_builder' => function(EntityRepository $er)  use ( $stop ) {
                            return $er->createQueryBuilder('s')
                                ->where("IDENTITY(s.stop) = :id")
                                ->andWhere("s.startDate <= CURRENT_DATE()")
                                ->andWhere("s.endDate IS NULL or s.endDate >= CURRENT_DATE()")
                                ->setParameter('id', $stop);
                        }
                    )
                );

                $form->add('masterStop', 'entity',
                    array(
                        'label' =>  'stop.labels.masterStop',
                        'required' => false,
                        'empty_value' => '',
                        'empty_data' => null,
                        'class' => 'TisseoEndivBundle:Stop',
                        'property' => 'stopLabel',
                        'query_builder' => function(EntityRepository $er)  use ( $stop ) {
                            return $er->createQueryBuilder('s')
                                ->where("s.stopArea = :sa")
                                ->andWhere("s.masterStop is null")
                                ->setParameter('sa', $stop->getStopArea());
                        }
                    )
                );
            }
        });

        $builder->setAction($options['action']);
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
        return 'boa_stop';
    }
}

