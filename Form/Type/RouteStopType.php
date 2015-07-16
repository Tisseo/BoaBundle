<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Tisseo\CoreBundle\Form\DataTransformer\EntityToIntTransformer;

class RouteStopType extends AbstractType
{
    private $waypointTransformer = null;
    private $routeTransformer = null;

    private function buildTransformers($em)
    {
        $this->waypointTransformer = new EntityToIntTransformer($em);
        $this->waypointTransformer->setEntityClass("Tisseo\\EndivBundle\\Entity\\Waypoint");
        $this->waypointTransformer->setEntityRepository("TisseoEndivBundle:Waypoint");
        $this->waypointTransformer->setEntityType("Waypoint");

        $this->routeTransformer = new EntityToIntTransformer($em);
        $this->routeTransformer->setEntityClass("Tisseo\\EndivBundle\\Entity\\Route");
        $this->routeTransformer->setEntityRepository("TisseoEndivBundle:Route");
        $this->routeTransformer->setEntityType("Route");
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
                'rank',
                'number',
                array(
                    'required' => true,
                    'read_only' => true,
                    'attr' => array(
                        'class' => 'input-sm'
                    )
                )
            )
            ->add(
                $builder->create(
                    'waypoint',
                    'hidden'
                )->addModelTransformer($this->waypointTransformer)
            )
            ->add(
                $builder->create(
                    'route',
                    'hidden'
                )->addModelTransformer($this->routeTransformer)
            )
            ->add(
                'pickup',
                'checkbox',
                array(
                    'required' => false,
                    'data' => true
                )
            )
            ->add(
                'dropOff',
                'checkbox',
                array(
                    'required' => false,
                    'data' => true
                )
            )
            ->add(
                'scheduledStop',
                'checkbox',
                array(
                    'required' => false,
                    'data' => true
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
                'data_class' => 'Tisseo\EndivBundle\Entity\RouteStop'
            )
        );

        $resolver->setRequired(array(
            'em'
        ));

        $resolver->setAllowedTypes(array(
            'em' => 'Doctrine\Common\Persistence\ObjectManager',
        ));
    }

    public function getName()
    {
        return 'boa_route_stop';
    }
}
