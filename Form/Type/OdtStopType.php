<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Tisseo\EndivBundle\Entity\OdtStop;
use Tisseo\CoreBundle\Form\DataTransformer\EntityToIntTransformer;

class OdtStopType extends AbstractType
{
    private $odtAreaTransformer = null;

    private function buildTransformers($em)
    {
        $this->odtAreaTransformer = new EntityToIntTransformer($em);
        $this->odtAreaTransformer->setEntityClass("Tisseo\\EndivBundle\\Entity\\OdtArea");
        $this->odtAreaTransformer->setEntityRepository("TisseoEndivBundle:OdtArea");
        $this->odtAreaTransformer->setEntityType("OdtArea");

        $this->stopTransformer = new EntityToIntTransformer($em);
        $this->stopTransformer->setEntityClass("Tisseo\\EndivBundle\\Entity\\Stop");
        $this->stopTransformer->setEntityRepository("TisseoEndivBundle:Stop");
        $this->stopTransformer->setEntityType("Stop");
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->buildTransformers($options['em']);

        $builder
            ->add(
                $builder->create(
                    'odtArea',
                    'hidden'
                )->addModelTransformer($this->odtAreaTransformer)
            )
            ->add(
                $builder->create(
                    'stop',
                    'hidden'
                )->addModelTransformer($this->stopTransformer)
            )
            ->add(
                'startDate',
                'tisseo_datepicker',
                array(
                    'label' => 'tisseo.boa.odt_stop.label.start_date',
                    'required' => true,
                    'attr' => array(
                        'data-to-date' => true
                    )
                )
            )
            ->add(
                'endDate',
                'tisseo_datepicker',
                array(
                    'label' => 'tisseo.boa.odt_stop.label.end_date',
                    'required' => false,
                    'attr' => array(
                        'data-to-date' => true
                    )
                )
            )
            ->add(
                'pickup',
                'checkbox',
                array(
                    'required' => false,
                    'data' => true
                )
            )
            ->add(
                'dropOff',
                'checkbox',
                array(
                    'required' => false,
                    'data' => true
                )
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
                'data_class' => 'Tisseo\EndivBundle\Entity\OdtStop'
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
        return 'boa_odt_stop';
    }
}
