<?php
/**
 * Created by PhpStorm.
 * User: clesauln
 * Date: 13/04/2015
 * Time: 14:25
 */
namespace Tisseo\BoaBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Tisseo\BoaBundle\Form\Type\LineVersionType;

class RouteType extends AbstractType {

   public function buildForm( FormBuilderInterface $builder, array $options) {
       $builder->add('name', 'text',  array('label' => 'Nom route'))
               ->add('way', 'text',  array('label' => 'Sens'))
               ->add('direction', 'text',  array('label' => 'Direction'))

           ->add('creer', 'submit');

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
        return 'Route';
    }
}