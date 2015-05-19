<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ColorType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array('label' => 'color.labels.name'));
        $builder->add('html', 'text', array('label' => 'color.labels.html'));
        $builder->add('pantoneOc', 'text', array('label' => 'color.labels.pantoneOc'));
        $builder->add('hoxis', 'text',
            array(
                'label' => 'color.labels.hoxis',
                'required' => false
            )
        );
        $builder->add('cmykCyan', 'text', array('label' => 'color.labels.cmykCyan'));
        $builder->add('cmykMagenta', 'text', array('label' => 'color.labels.cmykMagenta'));
        $builder->add('cmykYellow', 'text', array('label' => 'color.labels.cmykYellow'));
        $builder->add('cmykBlack', 'text', array('label' => 'color.labels.cmykBlack'));
        $builder->add('rgbRed', 'text', array('label' => 'color.labels.rgbRed'));
        $builder->add('rgbGreen', 'text', array('label' => 'color.labels.rgbGreen'));
        $builder->add('rgbBlue', 'text', array('label' => 'color.labels.rgbBlue'));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Tisseo\EndivBundle\Entity\Color'
            )
        );	
	}

			
    /**
     * @return string
     */
    public function getName()
    {
        return 'boa_color';
    }
}
