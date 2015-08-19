<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

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
                    'label' => 'route.labels.name',
                )
            )
            ->add(
                'direction',
                'text',
                array(
                    'label' => 'route.labels.direction',
                )
            )
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $form = $event->getForm();
                $trip = $event->getData();
                if ($trip->getWay() == 'Zonal')
                {
                    $form->add(
                        'way',
                        'text',
                        array(
                            'label' => 'route.labels.way',
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
                            'label' => 'route.labels.way',
                            'choices' => array(
                                'Aller' => 'Aller',
                                'Retour' => 'Retour',
                                'Boucle' => 'Boucle'
                            )
                        )
                    );
                }
            })
            ->add(
                'routeDatasources',
                'collection',
                array(
                    'label' => 'route.labels.datasource',
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
