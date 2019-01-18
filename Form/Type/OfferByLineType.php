<?php

namespace Tisseo\BoaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Tisseo\EndivBundle\Entity\PhysicalMode;
use Tisseo\EndivBundle\Services\LineManager;
use Tisseo\EndivBundle\Services\LineVersionManager;

class OfferByLineType extends AbstractType
{
    /**
     * @var LineVersionManager
     */
    private $lvm;

  /**
   * @var LineManager
   */
    private $lm;

    /**
     * OfferByLineType constructor.
     *
     * @param LineVersionManager $lvm
     * @param LineManager $lm
     */
    public function __construct(LineVersionManager $lvm, LineManager $lm)
    {
        $this->lvm = $lvm;
        $this->lm = $lm;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /*$currentDate = new \Datetime('now');
        $currentDate->setDate($currentDate->format('Y'), $currentDate->format('m'), 1);
        $currentDate->setTime(8, 0, 0);*/

        $lvOptions = $this->getLmOptions(
          $this->lm->findAllLinesByPriority()
        );

        $builder->add(
          'line',
          'choice',
          array(
            'label' => 'tisseo.boa.monitoring.offer_by_line.label.line',
            'choices' => $lvOptions,
            'required' => true,
            'attr' => [
              'class' => 'ajax_dep_element',
            ],
          )
        );
        /*$builder->add(
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
        );*/

        $builder->add(
            'routes',
            'hidden',
            [
                'required' => false
            ]
        );

        /*$builder->add(
            'reset',
            'checkbox',
            [
                'label' => 'tisseo.boa.monitoring.offer_by_line.label.reset',
                'required' => false,
                'data' => false
            ]
        );*/

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use($lvOptions) {
            $form = $event->getForm();
            $data = $event->getData();

            /*if (isset($data['month']) && !$data['month'] instanceof \DateTime) {
                $strDate = $data['month']['date']['year'].'-'.$data['month']['date']['month'].'-'.$data['month']['date']['day'].'-'.$data['month']['time']['hour'];
                $data['month'] = \DateTime::createFromFormat(
                    'Y-n-j-G',
                    $strDate
                );
            } else {
                $date = new \DateTime('now');
                $date->setTime(8, 0, 0);
                $data['month'] = $date;
            }*/

            if (!isset($data['line'])) {
              reset($lvOptions);
              $data['line'] = key($lvOptions);
            }
            $lvOptions = $this->getLvmOptions(
                //$this->lvm->findLineVersionSortedByLineNumber($data['month'], [PhysicalMode::PHYSICAL_MODE_TAD])
              $this->lvm->findBy([
                'line' => $data['line'],
              ],[
                'version' => 'ASC',
                'startDate' => 'ASC',
              ])
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

  /**
   * @param $lines
   *
   * @return array
   */
  private function getLmOptions($lines)
  {
    if (is_array($lines)) {
      /**
       * @var $line \Tisseo\EndivBundle\Entity\Line
       */
      foreach ($lines as $line) {
        $options[$line->getId()] = $line->getNumber();
      }
    }

    return (isset($options)) ? $options : [];
  }

    private function getLvmOptions($lineVersions)
    {
        if (is_array($lineVersions)) {
          /**
           * @var $lv \Tisseo\EndivBundle\Entity\LineVersion
           */
            foreach ($lineVersions as $lv) {
                $options[$lv->getId()] = $lv->getLine()->getNumber().' - '.$lv->getVersion().' - '.$lv->getStartDate()->format('d/m/Y');
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
