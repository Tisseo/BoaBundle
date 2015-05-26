<?php
/**
 * Created by PhpStorm.
 * User: clesauln
 * Date: 06/05/2015
 * Time: 10:15
 */

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use Tisseo\EndivBundle\Entity\Trip;

class TripType extends AbstractType 
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text',
            array('label' => 'trip.labels.name')
        )
        ->add('dayCalendar', 'calendar_selector',
            array('label' => 'trip.labels.day_calendar')
        )
        ->add('periodCalendar', 'calendar_selector',
            array('label' => 'trip.labels.period_calendar')
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

    public function getName(){

        return 'boa_trip';
    }

}