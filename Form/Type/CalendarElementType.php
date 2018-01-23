<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Tisseo\CoreBundle\Form\DataTransformer\EntityToIntTransformer;
use Tisseo\EndivBundle\Entity\CalendarElement;

class CalendarElementType extends AbstractType
{
    private $calendarTransformer = null;

    private function buildTransformers($em)
    {
        $this->calendarTransformer = new EntityToIntTransformer($em);
        $this->calendarTransformer->setEntityClass('Tisseo\\EndivBundle\\Entity\\Calendar');
        $this->calendarTransformer->setEntityRepository('TisseoEndivBundle:Calendar');
        $this->calendarTransformer->setEntityType('Calendar');
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
                'rank',
                'integer',
                array(
                    'label' => 'tisseo.boa.calendar_element.label.rank',
                    'read_only' => true
                )
            )
            ->add(
                'startDate',
                'tisseo_datepicker',
                array(
                    'label' => 'tisseo.boa.calendar_element.label.startDate',
                    'required' => true,
                    'attr' => array(
                        'class' => 'input-date'
                    )
                )
            )
            ->add(
                'endDate',
                'tisseo_datepicker',
                array(
                    'label' => 'tisseo.boa.calendar_element.label.endDate',
                    'required' => true,
                    'attr' => array(
                        'class' => 'input-date'
                    )
                )
            )
            ->add(
                'operator',
                'choice',
                array(
                    'label' => 'tisseo.boa.calendar_element.label.operator',
                    'choices' => CalendarElement::$operators
                )
            )
            ->add(
                'interval',
                'text',
                array(
                    'label' => 'tisseo.boa.calendar_element.label.interval',
                    'data' => '1',
                    'required' => false
                )
            )
            ->add(
                $builder->create(
                    'calendar',
                    'hidden'
                )->addModelTransformer($this->calendarTransformer)
            )
            ->add(
                $builder->create(
                    'includedCalendar',
                    'hidden'
                )->addModelTransformer($this->calendarTransformer)
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
                'data_class' => 'Tisseo\EndivBundle\Entity\CalendarElement'
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
        return 'boa_calendar_element';
    }
}
