<?php
/**
 * Created by PhpStorm.
 * User: clesauln
 * Date: 14/04/2015
 * Time: 15:02
 */

namespace Tisseo\BoaBundle\Form\Type;


use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;


class LineVersionType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options){
        $builder->add('line_version_id', 'entity',  array('class' => 'TisseoEndivBundle:LineVersion',
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('lv')
                          ->groupBy('lv.id');
            }));


        $builder->setAction($options['action']);
    }
    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        // TODO: Implement getName() method.
        return "LineVersion";
    }
}