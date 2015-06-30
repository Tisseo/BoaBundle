<?php
namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityRepository;

class TripCreateType extends AbstractType
{
    private $user;
    private $datasource;

    public function __construct($user, $datasource)
    {
        $this->user = $user;
        $this->datasource = $datasource;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            'text',
            array(
                'label' => 'trip.labels.name'
            )
        )
        ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $trip = $event->getData();

            $form->add(
                'pattern',
                'entity',
                array(
                    'label' => 'trip.labels.pattern',
                    'required' => true,
                    'class' => 'TisseoEndivBundle:Trip',
                    'property' => 'name',
                    'query_builder' => function(EntityRepository $er) use ($trip) {
                        return $er->createQueryBuilder('t')
                            ->where("IDENTITY(t.route) = :routeId")
                            ->andWhere("t.isPattern = true")
                            ->setParameter('routeId', $trip->getRoute()->getId());
                    }
                )
            );
        })
        ->add(
            'dayCalendar',
            'calendar_selector',
            array(
                'label' => 'trip.labels.day_calendar',
                'required' => false
            )
        )
        ->add(
            'periodCalendar',
            'calendar_selector',
            array(
                'label' => 'trip.labels.period_calendar',
                'required' => false
            )
        )
        ->add(
            'datasource',
            'entity',
            array(
                'mapped' => false,
                'required' => true,
                'label' => 'datasource.labels.title',
                'class' => 'TisseoEndivBundle:Datasource',
                'property' => 'name',
                'data' => $this->datasource
            )
        )
        ->add(
            'code',
            'text',
            array(
                'label' => 'datasource.labels.code',
                'mapped' => false,
                'data' => $this->user
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
                'data_class' => 'Tisseo\EndivBundle\Entity\Trip'
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'boa_trip_create';
    }

}
