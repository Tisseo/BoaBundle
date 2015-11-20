<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Tisseo\EndivBundle\Entity\Route;

class RouteEditType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                'text',
                array(
                    'label' => 'tisseo.boa.route.label.name',
                )
            )
            ->add(
                'direction',
                'text',
                array(
                    'label' => 'tisseo.boa.route.label.direction',
                )
            )
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $form = $event->getForm();
                $trip = $event->getData();
                if ($trip->getWay() == Route::WAY_AREA)
                {
                    $form->add(
                        'way',
                        'text',
                        array(
                            'label' => 'tisseo.boa.route.label.way',
                            'read_only' => true
                        )
                    );
                }
                else
                {
                    $form->add(
                        'way',
                        'choice',
                        array(
                            'label' => 'tisseo.boa.route.label.way',
                            'choices' => array(
                                Route::WAY_FORWARD => 'tisseo.boa.route.label.ways.forward',
                                Route::WAY_BACKWARD => 'tisseo.boa.route.label.ways.backward',
                                Route::WAY_LOOP => 'tisseo.boa.route.label.ways.loop',
                                Route::WAY_AREA => 'tisseo.boa.route.label.ways.area'
                            )
                        )
                    );
                }
            })
            ->add(
                'routeDatasources',
                'collection',
                array(
                    'type' => new RouteDatasourceType(),
                    'by_reference' => false
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
                'data_class' => 'Tisseo\EndivBundle\Entity\Route'
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'boa_route_edit';
    }
}
