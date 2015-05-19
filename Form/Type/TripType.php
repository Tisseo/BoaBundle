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


class TripType extends AbstractType {


    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id','text',array('attr'=>array('class'=>"id")))
                ->add('name', 'text')
                ->add('isPattern', 'text');


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