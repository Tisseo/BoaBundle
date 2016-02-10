<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\CoreBundle\Controller\CoreController;
use Tisseo\EndivBundle\Entity\Depot;
use Tisseo\BoaBundle\Form\Type\DepotType;

class DepotController extends CoreController
{
    /**
     * List
     *
     * Listing all Depots
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
            'TisseoBoaBundle:Depot:list.html.twig',
            array(
                'navTitle' => 'tisseo.boa.menu.configuration',
                'pageTitle' => 'tisseo.boa.depot.title.list',
                'depots' => $this->get('tisseo_endiv.depot_manager')->findAll()
            )
        );
    }

    /**
     * Edit
     * @param integer $depotId
     *
     * Creating/editing Depot
     */
    public function editAction(Request $request, $depotId)
    {
        $this->isGranted('BUSINESS_MANAGE_CONFIGURATION');

        $depotManager = $this->get('tisseo_endiv.depot_manager');
        $depot = $depotManager->find($depotId);

        if (empty($depot))
            $depot = new Depot();

        $form = $this->createForm(
            new DepotType(),
            $depot,
            array(
                'action' => $this->generateUrl(
                    'tisseo_boa_depot_edit',
                    array('depotId' => $depotId)
                )
            )
        );

        $form->handleRequest($request);
        if ($form->isValid())
        {
            try
            {
                $depotManager->save($form->getData());
                $this->addFlash('success', ($depotId ? 'tisseo.flash.success.edited' : 'tisseo.flash.success.created'));
            }
            catch (\Exception $e)
            {
                $this->addFlashException($e->getMessage());
            }

            return $this->redirectToRoute('tisseo_boa_depot_list');
        }

        return $this->render(
            'TisseoBoaBundle:Depot:form.html.twig',
            array(
                'form' => $form->createView(),
                'title' => ($depotId ? 'tisseo.boa.depot.title.edit' : 'tisseo.boa.depot.title.create')
            )
        );
    }

    /**
     * Delete
     * @param integer $depotId
     *
     * Deleting Depot
     */
    public function deleteAction($depotId)
    {
        
        $this->isGranted('BUSINESS_MANAGE_CONFIGURATION');

        try {
            $depot = $this->get('tisseo_endiv.depot_manager')->find($depotId);
            if (!empty($depot))
                $this->get('tisseo_endiv.depot_manager')->delete($depot);
        } catch(\Exception $e) {
            $this->addFlashException($e->getMessage());
        }

        return $this->redirect(
            $this->generateUrl('tisseo_boa_depot_list')
        );
    }

}
