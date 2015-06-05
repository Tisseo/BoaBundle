<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\EndivBundle\Entity\AccessibilityMode;
use Tisseo\BoaBundle\Form\Type\AccessibilityModeType;

class AccessibilityModeController extends AbstractController
{
    public function listAction()
    {
        $this->isGranted(
            array(
                'BUSINESS_MANAGE_CONFIGURATION',
                'BUSINESS_VIEW_CONFIGURATION'
            )
        );


		$AccessibilityModeManager = $this->get('tisseo_endiv.accessibility_mode_manager');
        return $this->render(
            'TisseoBoaBundle:AccessibilityMode:list.html.twig',
            array(
                'pageTitle' => 'menu.accessibility_mode',
                'accessibility_modes' => $AccessibilityModeManager->findAll()
            )
        );
    }
	
    public function editAction(Request $request, $AccessibilityModeId)
    {
        $this->isGranted('BUSINESS_MANAGE_CONFIGURATION');

		$AccessibilityModeManager = $this->get('tisseo_endiv.accessibility_mode_manager');
		$form = $this->buildForm($AccessibilityModeId, $AccessibilityModeManager);
        $render = $this->processForm($request, $form);
		if (!$render) {
            return $this->render(
                'TisseoBoaBundle:AccessibilityMode:form.html.twig',
                array(
                    'form' => $form->createView(),
                    'title' => ($AccessibilityModeId ? 'accessibility_mode.edit' : 'accessibility_mode.create')
                )
            );
        }
        return ($render);
    }

    private function buildForm($AccessibilityModeId, $AccessibilityModeManager)
    {
        $accessibility_mode = $AccessibilityModeManager->find($AccessibilityModeId);
        if (empty($accessibility_mode)) {
            $accessibility_mode = new AccessibilityMode();
        }		

        $form = $this->createForm( new AccessibilityModeType(), $accessibility_mode,
            array(
                'action' => $this->generateUrl('tisseo_boa_accessibility_mode_edit',
											array('AccessibilityModeId' => $AccessibilityModeId)
                )
            )
        );
		
		return ($form);
    }	

    private function processForm(Request $request, $form)
    {
        $form->handleRequest($request);
        $AccessibilityModeManager = $this->get('tisseo_endiv.accessibility_mode_manager');
        if ($form->isValid()) {
            $AccessibilityModeManager->save($form->getData());
            $this->get('session')->getFlashBag()->add('success',
                $this->get('translator')->trans(
                    'accessibility_mode.created',
                    array(),
                    'default'
                )
            );
            return $this->redirect(
                $this->generateUrl('tisseo_boa_accessibility_mode_list')
            );
        }
        return (null);
    }

}
