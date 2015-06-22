<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Tisseo\EndivBundle\Entity\StopAccessibility;
// use Tisseo\BoaBundle\Form\Type\AccessibilityTypeType;

class StopAccessibilityType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('accessibilityType', new AccessibilityTypeType(),
            array(
                'data_class' => 'Tisseo\EndivBundle\Entity\AccessibilityType'
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
                'data_class' => 'Tisseo\EndivBundle\Entity\StopAccessibility'
            )
        );
    }


    /**
     * @return string
     */
    public function getName()
    {
        return 'boa_stop_accessibility';
    }
}
