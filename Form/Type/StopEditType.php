<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\EntityRepository;
use Tisseo\EndivBundle\Entity\Stop;
use Tisseo\EndivBundle\Entity\StopHistory;

class StopEditType extends AbstractType
{
    private $stopHistory = null;

    public function __construct(StopHistory $stopHistory)
    {
        $this->stopHistory = $stopHistory;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'stopArea',
                'entity',
                array(
                    'label' => 'tisseo.boa.stop_point.label.stop_area',
                    'class' => 'TisseoEndivBundle:StopArea',
                    'property' => 'nameLabel',
                    'empty_data' => '',
                    'empty_value' => '',
                    'required' => false
                )
            )
            ->add(
                'stopDatasources',
                'collection',
                array(
                    'label' => 'tisseo.boa.stop_point.label.datasource',
                    'type' => new StopDatasourceType(),
                    'by_reference' => false,
                )
            )
            ->add(
                'shortName',
                'text',
                array(
                    'label' => 'tisseo.boa.stop_point.label.short_name',
                    'data' => $this->stopHistory->getShortName(),
                    'mapped' => false,
                    'required' => false,
                    'read_only' => true,
                    'disabled' => true
                )
            )
            ->add(
                'longName',
                'text',
                array(
                    'label' => 'tisseo.boa.stop_point.label.long_name',
                    'data' => $this->stopHistory->getLongName(),
                    'mapped' => false,
                    'required' => false,
                    'read_only' => true,
                    'disabled' => true
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
                'data_class' => 'Tisseo\EndivBundle\Entity\Stop'
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'boa_stop_edit';
    }
}
