<?php
/**
 * Created by PhpStorm.
 * User: clesauln
 * Date: 13/04/2015
 * Time: 14:25
 */
namespace Tisseo\BoaBundle\Form\Type;

//use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Tisseo\EndivBundle\Entity\Route;

class RouteType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text',
            array(
                'label' => 'route.labels.name',
            )
        )
        ->add('direction', 'text',
            array(
                'label' => 'route.labels.direction',
            )
        )
        ->add('way', 'text',
            array(
                'label' => 'route.labels.way'
            )
        );

        $builder->add('routeDatasources', 'collection', 
            array(
                'type' => new RouteDatasourceType(),
                'by_reference' => false,
                'allow_add' => true
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
        return 'boa_route';
    }
}