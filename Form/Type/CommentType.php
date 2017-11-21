<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CommentType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'label',
                'text',
                array(
                    'label' => 'tisseo.paon.comment.label.label',
                    'required' => false
                )
            )
            ->add(
                'commentText',
                'textarea',
                array(
                    'label' => 'tisseo.paon.comment.label.text',
                    'required' => false
                )
            )
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Tisseo\EndivBundle\Entity\Comment'
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tisseo_boa_form_comment';
    }
}
