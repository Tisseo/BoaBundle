<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\EndivBundle\Entity\AccessibilityMode;
use Tisseo\BoaBundle\Form\Type\AccessibilityModeType;

class AccessibilityModeController extends AbstractController
{
    /**
     * BuildForm
     * @param AccessibilityMode $accessibilityMode
     *
     * Building a new form for AccessibilityMode object.
     */
    private function buildForm($accessibilityMode)
    {
        if (empty($accessibilityMode))
            $accessibilityMode = new AccessibilityMode();

        $form = $this->createForm(
            new AccessibilityModeType(),
            $accessibilityMode,
            array(
                'action' => $this->generateUrl(
                    'tisseo_boa_accessibility_mode_edit',
                    array('accessibilityModeId' => $accessibilityModeId)
                )
            )
        );

        return ($form);
    }

    /**
     * ProcessForm
     * @param Form $form
     * @param AccessibilityModeManager $accessibilityModeManager
     *
     * Processing form validation.
     */
    private function processForm($form, $accessibilityModeManager)
    {
        if ($form->isValid()) {
            $accessibilityModeManager->save($form->getData());
            $this->get('session')->getFlashBag()->add(
                'success',
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

    /**
     * List
     *
     * Listing all accessibility modes.
     */
    public function listAction()
    {
        $this->isGranted(
            array(
                'BUSINESS_MANAGE_CONFIGURATION',
                'BUSINESS_VIEW_CONFIGURATION'
            )
        );

        $accessibilityModeManager = $this->get('tisseo_endiv.accessibility_mode_manager');
        return $this->render(
            'TisseoBoaBundle:AccessibilityMode:list.html.twig',
            array(
                'pageTitle' => 'menu.accessibility_mode',
                'accessibilityModes' => $accessibilityModeManager->findAll()
            )
        );
    }

    /**
     * Edit
     * @param integer $accessibilityModeId
     *
     * Editing or creating a new AccessibilityMode.
     */
    public function editAction($accessibilityModeId)
    {
        $this->isGranted('BUSINESS_MANAGE_CONFIGURATION');
        $request = $this->getRequest();

        $accessibilityModeManager = $this->get('tisseo_endiv.accessibility_mode_manager');
        $accessibilityMode = $accessibilityModeManager->find($accessibilityModeId);

        $form = $this->buildForm($accessibilityMode);
        $form->handleRequest($request);
        $render = $this->processForm($form, $accessibilityModeManager);

        if (!$render) {
            return $this->render(
                'TisseoBoaBundle:AccessibilityMode:form.html.twig',
                array(
                    'form' => $form->createView(),
                    'title' => ($accessibilityModeId ? 'accessibility_mode.edit' : 'accessibility_mode.create')
                )
            );
        }
        return ($render);
    }
}
