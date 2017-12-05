<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Tisseo\EndivBundle\Entity\Line;
use Tisseo\EndivBundle\Entity\LineVersion;
use Tisseo\EndivBundle\Entity\PhysicalMode;
use Tisseo\EndivBundle\Services\LineVersionManager;

class OfferByLineType extends AbstractType
{
    /**
     * @var LineVersionManager
     */
    private $lvm;

    /**
     * OfferByLineType constructor.
     *
     * @param \Tisseo\EndivBundle\Services\LineVersionManager $lvm
     */
    public function __construct(LineVersionManager $lvm)
    {
        $this->lvm = $lvm;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $currentDate = new \Datetime('now');
        $currentDate->setDate($currentDate->format('Y'), $currentDate->format('m'), 1);
        $currentDate->setTime(8, 0, 0);
        $builder->add(
            'month',
            'datetime',
            array(
                'label' => 'tisseo.boa.monitoring.offer_by_line.label.month',
                'required' => true,
                'attr' => [
                    'class' => 'ajax_dep_element',
                ]
            )
        );

        $builder->get('month')->addModelTransformer(
            new CallbackTransformer(
                function ($value) use ($currentDate) {
                    if (!$value) {
                        return $currentDate;
                    }
                },
                function ($value) {
                    return $value;
                }
            )
        );

        $builder->add(
            'colors',
            'hidden',
            [
                'required' => false
            ]
        );

        $builder->add(
            'reset',
            'checkbox',
            [
                'label' => 'tisseo.boa.monitoring.offer_by_line.label.reset',
                'required' => false,
                'data' => false
            ]
        );

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            if (isset($data['month']) && !$data['month'] instanceof \DateTime) {
                $data['month'] = \DateTime::createFromFormat(
                    'Ymd-H',
                    $data['month']['date']['year'].$data['month']['date']['month'].$data['month']['date']['day'].'-'.$data['month']['time']['hour']);
            } else {
                $date = new \DateTime('now');
                $date->setTime(8, 0, 0);
                $data['month'] = $date;
            }


            $lvOptions = $this->getOptions(
                $this->lvm->findLineVersionSortedByLineNumber($data['month'], [PhysicalMode::PHYSICAL_MODE_TAD])
            );

            $form->add(
                'offer',
                'choice',
                array(
                    'label' => 'tisseo.boa.monitoring.offer_by_line.label.line_version',
                    'choices' => $lvOptions,
                    'required' => true,
                    'attr' => [
                        'class' => 'ajax_dep_element',
                    ],
                )
            );
        });
    }

    private function getOptions($lineVersions)
    {
        if (is_array($lineVersions)) {
            foreach ($lineVersions as $lv) {
                $options[$lv->getId()] = $lv->getLine()->getNumber().' - '.$lv->getVersion();
            }
        }

        return (isset($options)) ? $options : [];
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => null
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'boa_offer_by_line_type';
    }
}
