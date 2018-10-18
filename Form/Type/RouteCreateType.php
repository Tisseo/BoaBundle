<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Tisseo\CoreBundle\Form\DataTransformer\EntityToIntTransformer;
use Tisseo\EndivBundle\Entity\Route;

class RouteCreateType extends AbstractType
{
    private $lineVersionTransformer = null;

    private function buildTransformers($em)
    {
        $this->lineVersionTransformer = new EntityToIntTransformer($em);
        $this->lineVersionTransformer->setEntityClass('Tisseo\\EndivBundle\\Entity\\LineVersion');
        $this->lineVersionTransformer->setEntityRepository('TisseoEndivBundle:LineVersion');
        $this->lineVersionTransformer->setEntityType('lineVersion');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->buildTransformers($options['em']);

        $builder
            ->add(
                'name',
                'text',
                array(
                    'label' => 'tisseo.boa.route.label.name'
                )
            )
            ->add(
                'direction',
                'text',
                array(
                    'label' => 'tisseo.boa.route.label.direction',
                    'attr' => array(
                        'class' => 'direction-input'
                    )
                )
            )
            ->add(
                'way',
                'choice',
                array(
                    'label' => 'tisseo.boa.route.label.way',
                    'choices' => array(
                        Route::WAY_FORWARD => 'tisseo.boa.route.label.ways.forward',
                        Route::WAY_BACKWARD => 'tisseo.boa.route.label.ways.backward',
                        Route::WAY_LOOP => 'tisseo.boa.route.label.ways.loop',
                        Route::WAY_AREA => 'tisseo.boa.route.label.ways.area'
                    ),
                    'required' => false,
                )
            )
            ->add(
                'comment',
                'tisseo_boa_form_comment'
            )
            ->add(
                'routeDatasources',
                'collection',
                array(
                    'type' => new RouteDatasourceType(),
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
