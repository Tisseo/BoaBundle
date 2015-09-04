<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;
use Tisseo\EndivBundle\Entity\Calendar;
use Tisseo\BoaBundle\Form\Type\CalendarElementType;

class CalendarType extends AbstractType
{
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        usort($view->children['lineVersion']->vars['choices'], function(ChoiceView $choice1, ChoiceView $choice2) {
            $lineVersion1 = $choice1->data;
            $lineVersion2 = $choice2->data;
            if ($lineVersion1->getLine()->getPriority() == $lineVersion2->getLine()->getPriority())
            {
                $numberComparison = strnatcmp($lineVersion1->getLine()->getNumber(), $lineVersion2->getLine()->getNumber());

                if ($numberComparison == 0)
                    return ($lineVersion1->getVersion() < $lineVersion2->getVersion() ? -1 : 1);
                else
                    return $numberComparison;
            }
            if ($lineVersion1->getLine()->getPriority() > $lineVersion2->getLine()->getPriority())
                return 1;
            if ($lineVersion1->getLine()->getPriority() < $lineVersion2->getLine()->getPriority())
                return -1;
        });
    }

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
                    'label' => 'tisseo.boa.calendar.label.name'
                )
            )
            ->add(
                'calendarType',
                'choice',
                array(
                    'label' => 'tisseo.boa.calendar.label.type',
                    'choices' => Calendar::$calendarTypes,
                    'attr' => array(
                        'class' => 'calendar-type'
                    )
                )
            )
            ->add(
                'lineVersion',
                'entity',
                array(
                    'label' => 'tisseo.boa.calendar.label.line_version',
                    'class' => 'TisseoEndivBundle:LineVersion',
                    'property' => 'numberAndVersion',
                    'empty_value' => '',
                    'required' => false
                )
            )
            ->setAction($options['action'])
            ->addEventListener(FormEvents::PRE_SET_DATA,
                function (FormEvent $event) use ($options) {
                    $form = $event->getForm();
                    $calendar = $event->getData();

                    if ($calendar->getId() !== null)
                    {
                        $form
                            ->add(
                                'computedStartDate',
                                'tisseo_datepicker',
                                array(
                                    'label' => 'tisseo.boa.calendar.label.computed_start_date',
                                    'read_only' => true,
                                    'required' => false
                                )
                            )
                            ->add(
                                'computedEndDate',
                                'tisseo_datepicker',
                                array(
                                    'label' => 'tisseo.boa.calendar.label.computed_end_date',
                                    'read_only' => true,
                                    'required' => false
                                )
                            )
                        ;
                    }
                }
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
                'data_class' => 'Tisseo\EndivBundle\Entity\Calendar'
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'boa_calendar';
    }
}
