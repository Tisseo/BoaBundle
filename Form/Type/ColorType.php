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
        $builder
            ->add(
                'name',
                'text',
                array(
                    'label' => 'tisseo.boa.color.label.name'
                )
            )
            ->add(
                'html',
                'text',
                array(
                    'label' => 'tisseo.boa.color.label.html'
                )
            )
            ->add(
                'pantoneOc',
                'text',
                array(
                    'label' => 'tisseo.boa.color.label.pantone'
                )
            )
            ->add(
                'hoxis',
                'text',
                array(
                    'label' => 'tisseo.boa.color.label.hoxis',
                    'required' => false
                )
            )
            ->add(
                'cmykCyan',
                'text',
                array(
                    'label' => 'tisseo.boa.color.label.cmyk_cyan'
                )
            )
            ->add(
                'cmykMagenta',
                'text',
                array(
                    'label' => 'tisseo.boa.color.label.cmyk_magenta'
                )
            )
            ->add(
                'cmykYellow',
                'text',
                array(
                    'label' => 'tisseo.boa.color.label.cmyk_yellow'
                )
            )
            ->add(
                'cmykBlack',
                'text',
                array(
                    'label' => 'tisseo.boa.color.label.cmyk_black'
                )
            )
            ->add(
                'rgbRed',
                'text',
                array(
                    'label' => 'tisseo.boa.color.label.rgb_red'
                )
            )
            ->add(
                'rgbGreen',
                'text',
                array(
                    'label' => 'tisseo.boa.color.label.rgb_green'
                )
            )
            ->add(
                'rgbBlue',
                'text',
                array(
                    'label' => 'tisseo.boa.color.label.rgb_blue'
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
