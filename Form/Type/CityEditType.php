<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Doctrine\ORM\EntityRepository;
use Tisseo\EndivBundle\Entity\City;

class CityEditType extends AbstractType
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
                    'label' => 'tisseo.boa.stop_point.label.stop_area',
                    'required' => true
                )
            )
            ->add(
                'insee',
                'text',
                array(
                    'label' => 'tisseo.boa.city.label.insee',
                    'required' => true,
                )
            )

            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $form = $event->getForm();
                $city = $event->getData();

                $form
                    ->add(
                        'mainStopArea',
                        'entity',
                        array(
                            'label' => 'tisseo.boa.city.label.main_stop_area',
                            'required' => false,
                            'empty_value' => '',
                            'empty_data' => null,
                            'class' => 'TisseoEndivBundle:StopArea',
                            'property' => 'longName',
                            'query_builder' => function (EntityRepository $er) use ($city) {
                                return $er->createQueryBuilder('s')
                                    ->where('s.city = :sa')
                                    ->addOrderBy('s.longName', 'ASC')
                                    ->setParameter('sa', $city->getId());
                            }
                        )
                    )
                ;
            })
            ->setAction($options['action']);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Tisseo\EndivBundle\Entity\City'
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'boa_city_edit';
    }
}
