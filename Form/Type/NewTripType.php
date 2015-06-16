<?php
namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use Tisseo\EndivBundle\Entity\Trip;

class NewTripType extends AbstractType
{
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
        ->add(
            'pattern',
            'trip_selector',
            array(
                'label' => 'trip.labels.pattern',
                'required' => false
            )
        )
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
                'label' => 'Datasource',
                'class' => 'TisseoEndivBundle:Datasource',
                'property' => 'name'
            )
        )
        ->add(
            'code',
            'text',
            array(
                'mapped' => false,
                'required' => true,
                'label' => 'Code'
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

    public function getName(){

        return 'boa_trip';
    }

}
