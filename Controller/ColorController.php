<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\EndivBundle\Entity\Color;
use Tisseo\BoaBundle\Form\Type\ColorType;

class ColorController extends AbstractController
{
    public function listAction()
    {
        $this->isGranted(
            array(
                'BUSINESS_MANAGE_CONFIGURATION',
                'BUSINESS_VIEW_CONFIGURATION'
            )
        );

        $ColorManager = $this->get('tisseo_endiv.color_manager');
        return $this->render(
            'TisseoBoaBundle:Color:list.html.twig',
            array(
                'pageTitle' => 'menu.color',
                'colors' => $ColorManager->findAll()
            )
        );
    }

    public function editAction(Request $request, $colorId)
    {
        $this->isGranted('BUSINESS_MANAGE_CONFIGURATION');

        $ColorManager = $this->get('tisseo_endiv.color_manager');
        $form = $this->buildForm($colorId, $ColorManager);
        $render = $this->processForm($request, $form);
        if (!$render) {
            return $this->render(
                'TisseoBoaBundle:Color:form.html.twig',
                array(
                    'form' => $form->createView(),
                    'title' => ($colorId ? 'color.edit' : 'color.create')
                )
            );
        }
        return ($render);
    }

    private function buildForm($colorId, $ColorManager)
    {
        $color = $ColorManager->find($colorId);
        if (empty($color)) {
            $color = new Color();
        }

        $form = $this->createForm(new ColorType(), $color,
            array(
                'action' => $this->generateUrl('tisseo_boa_color_edit',
                                            array('colorId' => $colorId)
                )
            )
        );

        return ($form);
    }

    private function processForm(Request $request, $form)
    {
        $form->handleRequest($request);
        $ColorManager = $this->get('tisseo_endiv.color_manager');
        if ($form->isValid()) {
            $ColorManager->save($form->getData());
            $this->get('session')->getFlashBag()->add('success',
                $this->get('translator')->trans(
                    'color.created',
                    array(),
                    'default'
                )
            );
            return $this->redirect(
                $this->generateUrl('tisseo_boa_color_list')
            );
        }
        return (null);
    }
}
