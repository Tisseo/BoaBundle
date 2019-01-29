<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityRepository;

class StopPhantomType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /*
        datas are mapped later in sub-forms => need event on pre set data to get the current id
        */
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $stop = $event->getData();

            $form->add('shortName', 'entity',
                    array(
                        'mapped' => false,
                        'label' => 'stop.labels.shortName',
                        'class' => 'TisseoEndivBundle:StopHistory',
                        'property' => 'shortName',
                        'query_builder' => function (EntityRepository $er) use ($stop) {
                            return $er->createQueryBuilder('s')
                                ->where('IDENTITY(s.stop) = :id')
                                ->andWhere('s.startDate <= CURRENT_DATE()')
                                ->andWhere('s.endDate IS NULL or s.endDate >= CURRENT_DATE()')
                                ->setParameter('id', $stop);
                        }
                    )
                );
        });

        $builder->add('id', 'text');

        $builder->add('stopDatasources', 'collection',
            array(
                'label' => 'stop.labels.datasource',
                'type' => new StopDatasourceType(),
                'by_reference' => false,
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
                'data_class' => 'Tisseo\EndivBundle\Entity\Stop'
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'boa_stop_phantom';
    }
}
