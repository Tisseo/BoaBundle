<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\CoreBundle\Controller\CoreController;
use Tisseo\EndivBundle\Entity\PhysicalMode;
use Tisseo\BoaBundle\Form\Type\PhysicalModeType;

class PhysicalModeController extends CoreController
{
    /**
     * List
     *
     * Listing all PhysicalModes
     */
    public function listAction()
    {
        $this->isGranted(
            array(
                'BUSINESS_MANAGE_CONFIGURATION',
                'BUSINESS_VIEW_CONFIGURATION'
            )
        );

        return $this->render(
            'TisseoBoaBundle:PhysicalMode:list.html.twig',
            array(
                'navTitle' => 'tisseo.boa.menu.configuration',
                'pageTitle' => 'tisseo.boa.physical_mode.title.list',
                'physicalModes' => $this->get('tisseo_endiv.physical_mode_manager')->findAll()
            )
        );
    }

    /**
     * Edit
     * @param integer $physicalModeId
     *
     * Creating/editing PhysicalMode
     */
    public function editAction(Request $request, $physicalModeId)
    {
        $this->isGranted('BUSINESS_MANAGE_CONFIGURATION');

        $physicalModeManager = $this->get('tisseo_endiv.physical_mode_manager');
        $physicalMode = $physicalModeManager->find($physicalModeId);

        if (empty($physicalMode))
            $physicalMode = new PhysicalMode();

        $form = $this->createForm(
            new PhysicalModeType(),
            $physicalMode,
            array(
                'action' => $this->generateUrl(
                    'tisseo_boa_physical_mode_edit',
                    array('physicalModeId' => $physicalModeId)
                )
            )
        );

        $form->handleRequest($request);
        if ($form->isValid())
        {
            try
            {
                $physicalModeManager->save($form->getData());
                $this->addFlash('success', ($physicalModeId ? 'tisseo.flash.success.edited' : 'tisseo.flash.success.created'));
            }
            catch (\Exception $e)
            {
                $this->addFlashException($e->getMessage());
            }

            return $this->redirectToRoute('tisseo_boa_physical_mode_list');
        }

        return $this->render(
            'TisseoBoaBundle:PhysicalMode:form.html.twig',
            array(
                'form' => $form->createView(),
                'title' => ($physicalModeId ? 'tisseo.boa.physical_mode.title.edit' : 'tisseo.boa.physical_mode.title.create')
            )
        );
    }
}
