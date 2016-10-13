<?php
namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityRepository;
use Tisseo\CoreBundle\Form\DataTransformer\EntityToIntTransformer;
use Tisseo\Form\Type\TripDatasource;

class TripCreateType extends AbstractType
{
    private $calendarTransformer = null;
    private $routeTransformer = null;

    private function buildTransformers($em)
    {
        $this->calendarTransformer = new EntityToIntTransformer($em);
        $this->calendarTransformer->setEntityClass("Tisseo\\EndivBundle\\Entity\\Calendar");
        $this->calendarTransformer->setEntityRepository("TisseoEndivBundle:Calendar");
        $this->calendarTransformer->setEntityType("calendar");

        $this->routeTransformer = new EntityToIntTransformer($em);
        $this->routeTransformer->setEntityClass("Tisseo\\EndivBundle\\Entity\\Route");
        $this->routeTransformer->setEntityRepository("TisseoEndivBundle:Route");
        $this->routeTransformer->setEntityType("route");
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->buildTransformers($options['em']);

        $builder
            ->add(
                'name',
                'text',
                array(
                    'label' => 'tisseo.boa.trip.label.name'
                )
            )
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $form = $event->getForm();
                $trip = $event->getData();

                $form->add(
                    'tripPattern',
                    'entity',
                    array(
                        'label' => 'tisseo.boa.trip.label.pattern',
                        'required' => true,
                        'class' => 'TisseoEndivBundle:Trip',
                        'property' => 'name',
                        'query_builder' => function(EntityRepository $er) use ($trip) {
                            return $er->createQueryBuilder('t')
                                ->where("IDENTITY(t.route) = :routeId")
                                ->andWhere("t.pattern = true")
                                ->setParameter('routeId', $trip->getRoute()->getId());
                        }
                    )
                );
            })
            ->add(
                $builder->create(
                    'dayCalendar',
                    'hidden'
                )->addModelTransformer($this->calendarTransformer)
            )
            ->add(
                $builder->create(
                    'periodCalendar',
                    'hidden'
                )->addModelTransformer($this->calendarTransformer)
            )
            ->add(
                $builder->create(
                    'route',
                    'hidden'
                )->addModelTransformer($this->routeTransformer)
            )
            ->add(
                'tripDatasources',
                'collection',
                array(
                    'type' => new TripDatasourceType()
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
                'data_class' => 'Tisseo\EndivBundle\Entity\Trip'
            )
        );

        $resolver->setRequired(array(
            'em'
        ));

        $resolver->setAllowedTypes(array(
            'em' => 'Doctrine\Common\Persistence\ObjectManager',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'boa_trip_create';
    }
}
