<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Tisseo\CoreBundle\Form\DataTransformer\EntityToIntTransformer;
use Tisseo\BoaBundle\Form\Type\RouteDatasource;

class RouteCreateType extends AbstractType
{
    private $lineVersionTransformer = null;

    private function buildTransformers($em)
    {
        $this->lineVersionTransformer = new EntityToIntTransformer($em);
        $this->lineVersionTransformer->setEntityClass("Tisseo\\EndivBundle\\Entity\\LineVersion");
        $this->lineVersionTransformer->setEntityRepository("TisseoEndivBundle:LineVersion");
        $this->lineVersionTransformer->setEntityType("lineVersion");
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->buildTransformers($options['em']);

        $builder
            ->add(
                'name',
                'text',
                array(
                    'label' => 'route.labels.name'
                )
            )
            ->add(
                'direction',
                'text',
                array(
                    'label' => 'route.labels.direction',
                    'attr' => array(
                        'class' => 'direction-input'
                    )
                )
            )
            ->add(
                'way',
                'choice',
                array(
                    'label' => 'route.labels.way',
                    'choices' => array(
                        'Aller' => 'Aller',
                        'Retour' => 'Retour',
                        'Zonal' => 'Zonal',
                        'Boucle' => 'Boucle'
                    ),
                    'required' => false,
                    'attr' => array(
                        'class' => 'way-select'
                    ) 
                )
            )
            ->add(
                'routeDatasources',
                'collection',
                array(
                    'type' => new RouteDatasourceType(),
                    'label' => 'route.labels.route_datasource',
                    'by_reference' => false
                )
            )
            ->add(
                $builder->create(
                    'lineVersion',
                    'hidden'
                )->addModelTransformer($this->lineVersionTransformer)
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

        $resolver->setRequired(array(
            'em'
        ));

        $resolver->setAllowedTypes(array(
            'em' => 'Doctrine\Common\Persistence\ObjectManager',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'boa_route_create';
    }
}
