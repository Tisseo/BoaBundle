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

class RouteType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array('label' => 'Nom route'))
            ->add('way', 'choice', array(
                'choices' => array(
                    'zonal' => 'zonal', 'aller' => 'aller', 'retour' => 'retour'
                , 'boucle' => 'boucle'), 'label' => 'Sens'))
            ->add('direction', 'text', array('label' => 'Direction'))
            ->add('routeStops', 'collection', array(
                'type' => new RouteStopType(),
                'allow_add' => true,
                'by_reference' => false
            ));



        $builder->setAction($options['action']);
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