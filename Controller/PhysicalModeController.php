<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\EndivBundle\Entity\PhysicalMode;
use Tisseo\BoaBundle\Form\Type\PhysicalModeType;

class PhysicalModeController extends AbstractController
{
    public function listAction()
    {
        $this->isGranted(
            array(
                'BUSINESS_MANAGE_CONFIGURATION',
                'BUSINESS_VIEW_CONFIGURATION'
            )
        );

        $physicalModeManager = $this->get('tisseo_endiv.physical_mode_manager');
        return $this->render(
            'TisseoBoaBundle:PhysicalMode:list.html.twig',
            array(
                'pageTitle' => 'menu.physical_mode',
                'physical_modes' => $physicalModeManager->findAll()
            )
        );
    }

    public function editAction(Request $request, $physicalModeId)
    {
        $this->isGranted('BUSINESS_MANAGE_CONFIGURATION');

        $physicalModeManager = $this->get('tisseo_endiv.physical_mode_manager');
        $form = $this->buildForm($physicalModeId, $physicalModeManager);
        $render = $this->processForm($request, $form);
        if (!$render) {
            return $this->render(
                'TisseoBoaBundle:PhysicalMode:form.html.twig',
                array(
                    'form' => $form->createView(),
                    'title' => ($physicalModeId ? 'physical_mode.edit' : 'physical_mode.create')
                )
            );
        }
        return ($render);
    }

    private function buildForm($physicalModeId, $physicalModeManager)
    {
        $physical_mode = $physicalModeManager->find($physicalModeId);
        if (empty($physical_mode)) {
            $physical_mode = new PhysicalMode();
        }

        $form = $this->createForm( new PhysicalModeType(), $physical_mode,
            array(
                'action' => $this->generateUrl('tisseo_boa_physical_mode_edit',
                                            array('physicalModeId' => $physicalModeId)
                )
            )
        );

        return ($form);
    }

    private function processForm(Request $request, $form)
    {
        $form->handleRequest($request);
        $physicalModeManager = $this->get('tisseo_endiv.physical_mode_manager');
        if ($form->isValid()) {
            $physicalModeManager->save($form->getData());
            $this->get('session')->getFlashBag()->add('success',
                $this->get('translator')->trans(
                    'physical_mode.created',
                    array(),
                    'default'
                )
            );
            return $this->redirect(
                $this->generateUrl('tisseo_boa_physical_mode_list')
            );
        }
        return (null);
    }

}
