<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\CoreBundle\Controller\CoreController;
use Tisseo\EndivBundle\Entity\Color;
use Tisseo\BoaBundle\Form\Type\ColorType;

class ColorController extends CoreController
{
    /**
     * List
     *
     * Listing all Colors
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted(array(
            'BUSINESS_MANAGE_CONFIGURATION',
            'BUSINESS_VIEW_CONFIGURATION'
        ));

        return $this->render(
            'TisseoBoaBundle:Color:list.html.twig',
            array(
                'navTitle' => 'tisseo.boa.menu.configuration',
                'pageTitle' => 'tisseo.boa.color.title.list',
                'colors' => $this->get('tisseo_endiv.color_manager')->findAll()
            )
        );
    }

    /**
     * Edit
     *
     * @param int $colorId
     *
     * Creating/editing Color
     */
    public function editAction(Request $request, $colorId)
    {
        $this->denyAccessUnlessGranted('BUSINESS_MANAGE_CONFIGURATION');

        $colorManager = $this->get('tisseo_endiv.color_manager');
        $color = $colorManager->find($colorId);

        if (empty($color)) {
            $color = new Color();
        }

        $form = $this->createForm(
            new ColorType(),
            $color,
            array(
                'action' => $this->generateUrl(
                    'tisseo_boa_color_edit',
                    array('colorId' => $colorId)
                )
            )
        );

        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                $colorManager->save($form->getData());
                $this->addFlash('success', ($colorId ? 'tisseo.flash.success.edited' : 'tisseo.flash.success.created'));
            } catch (\Exception $e) {
                $this->addFlashException($e->getMessage());
            }

            return $this->redirectToRoute('tisseo_boa_color_list');
        }

        return $this->render(
            'TisseoBoaBundle:Color:form.html.twig',
            array(
                'form' => $form->createView(),
                'title' => ($colorId ? 'tisseo.boa.color.title.edit' : 'tisseo.boa.color.title.create')
            )
        );
    }
}
