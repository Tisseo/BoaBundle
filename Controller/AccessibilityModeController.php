<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\CoreBundle\Controller\CoreController;
use Tisseo\EndivBundle\Entity\AccessibilityMode;
use Tisseo\BoaBundle\Form\Type\AccessibilityModeType;

class AccessibilityModeController extends CoreController
{
    /**
     * List
     *
     * Listing all AccessibilityModes
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted(
            array(
                'BUSINESS_MANAGE_CONFIGURATION',
                'BUSINESS_VIEW_CONFIGURATION'
            )
        );

        return $this->render(
            'TisseoBoaBundle:AccessibilityMode:list.html.twig',
            array(
                'navTitle' => 'tisseo.boa.menu.configuration',
                'pageTitle' => 'tisseo.boa.accessibility_mode.title.list',
                'accessibilityModes' => $this->get('tisseo_endiv.accessibility_mode_manager')->findAll()
            )
        );
    }

    /**
     * Edit
     *
     * @param int $accessibilityModeId
     *
     * Creating/editing AccessibilityMode
     */
    public function editAction(Request $request, $accessibilityModeId)
    {
        $this->denyAccessUnlessGranted('BUSINESS_MANAGE_CONFIGURATION');

        $accessModeManager = $this->get('tisseo_endiv.accessibility_mode_manager');
        $accessibilityMode = $accessModeManager->find($accessibilityModeId);

        if (empty($accessibilityMode)) {
            $accessibilityMode = new AccessibilityMode();
        }

        $form = $this->createForm(
            new AccessibilityModeType(),
            $accessibilityMode,
            array(
                'action' => $this->generateUrl(
                    'tisseo_boa_accessibility_mode_edit',
                    array('accessibilityModeId' => $accessibilityMode->getId())
                )
            )
        );

        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                $accessModeManager->save($form->getData());
                $this->addFlash('success', ($accessibilityModeId ? 'tisseo.flash.success.edit' : 'tisseo.flash.success.create'));
            } catch (\Exception $e) {
                $this->addFlashException($e->getMessage());
            }

            return $this->redirectToRoute('tisseo_boa_accessibility_mode_list');
        }

        return $this->render(
            'TisseoBoaBundle:AccessibilityMode:form.html.twig',
            array(
                'form' => $form->createView(),
                'title' => ($accessibilityModeId ? 'tisseo.boa.accessibility_mode.title.edit' : 'tisseo.boa.accessibility_mode.title.create')
            )
        );
    }
}
