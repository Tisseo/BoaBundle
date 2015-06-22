<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use Tisseo\EndivBundle\Entity\Stop;

class StopAreaTransferType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $stop= $builder->getData();

        $builder->add('transferDuration', 'text',
            array(
                'label' => 'stop_area.labels.tranfer_duration'
            )
        );

        $builder->add('transfer', 'collection',
            array(
                'mapped' => false,
                'type' => new TransferType(),
                'by_reference' => false,
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
                'data_class' => 'Tisseo\EndivBundle\Entity\StopArea'
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'boa_stop_area_transfer';
    }
}

