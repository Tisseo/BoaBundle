<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\EndivBundle\Entity\OdtArea;
use Tisseo\EndivBundle\Entity\OdtStop;
use Tisseo\EndivBundle\Entity\Stop;
use Tisseo\CoreBundle\Controller\CoreController;
use Tisseo\BoaBundle\Form\Type\OdtAreaType;
use Tisseo\BoaBundle\Form\Type\OdtStopType;

class OdtAreaController extends CoreController
{
    /**
     * List
     *
     * Listing all odtAreas
     */
    public function listAction()
    {
        $this->isGranted(
            array(
                'BUSINESS_MANAGE_STOPS',
                'BUSINESS_VIEW_STOPS'
            )
        );

        /**$log = fopen('/tmp/test-jean.log','a+');
        foreach ($this->get('tisseo_endiv.odt_area_manager')->findAll() as $odtArea)
            fwrite($log, "\n".json_encode($odtArea->getOdtStops()->first()->getId()));

        fclose($log);*/
        return $this->render(
            'TisseoBoaBundle:OdtArea:list.html.twig',
            array(
                'navTitle' => 'tisseo.boa.menu.stop.manage',
                'pageTitle' => 'tisseo.boa.odt_area.title.list',
                'odtAreas' => $this->get('tisseo_endiv.odt_area_manager')->findAll(),
                'linesByOdtArea' => $this->get('tisseo_endiv.odt_area_manager')->getLinesByOdtArea(),
            )
        );
    }

    public function deleteAction($odtAreaId)
    {
        $this->isGranted(
            array(
                'BUSINESS_MANAGE_STOPS',
                'BUSINESS_VIEW_STOPS'
            )
        );

        try {
            $odtArea = $this->get('tisseo_endiv.odt_area_manager')->find($odtAreaId);
            if (!empty($odtArea))
                $this->get('tisseo_endiv.odt_area_manager')->delete($odtArea);
        } catch(\Exception $e) {
            $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
        }

        return $this->redirect(
            $this->generateUrl('tisseo_boa_odt_area_list')
        );
    }

    /**
     * Edit
     * @param integer $odtAreaId
     *
     * Creating/editing odtArea
     */
    public function editAction(Request $request, $odtAreaId)
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');

        $odtAreaManager = $this->get('tisseo_endiv.odt_area_manager');
        $odtArea = $odtAreaManager->find($odtAreaId);

        if (empty($odtArea))
        {
            $odtArea = new OdtArea();
            //$odtStops = null;
        }
        /*else
        {
            $odtStops = odtAreaManager
        }*/
        $form = $this->createForm(
            new OdtAreaType(),
            $odtArea,
            array(
                'action' => $this->generateUrl(
                    'tisseo_boa_odt_area_edit',
                    array(
                        'odtAreaId' => $odtAreaId,
                    )
                )
            )
        );

        $form->handleRequest($request);
        if ($form->isValid())
        {
            $odtArea = $form->getData();
            try
            {
                $odtAreaId = $odtAreaManager->create($odtArea);
                $odtAreaManager->save($odtArea);

                $this->addFlash('success', 'tisseo.flash.success.edited');
            }
            catch (\Exception $e)
            {
                $odtAreaId = null;
                $this->addFlashException($e->getMessage());
            }

            return $this->redirectToRoute(
                'tisseo_boa_odt_area_edit',
                array('odtAreaId' => $odtAreaId)
            );
        }

        return $this->render(
            'TisseoBoaBundle:OdtArea:edit.html.twig',
            array(
                'form' => $form->createView(),
                'pageTitle' => ($odtAreaId ? 'tisseo.boa.odt_area.title.edit' : 'tisseo.boa.odt_area.title.create'),
                'odtArea' => $odtArea
            )
        );
    }

    /**
     * Create
     * @param integer $odtAreaId
     *
     * Creating odtStop
     */
    public function createOdtStopAction(Request $request, $odtAreaId)
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');

        $odtStopManager = $this->get('tisseo_endiv.odt_stop_manager');
        $odtStop = new OdtStop();
        $odtStop->setOdtArea($odtAreaId);

        $form = $this->createForm(
            new OdtStopType(),
            $odtStop,
            array(
                'action' => $this->generateUrl(
                    'tisseo_boa_odt_area_edit',
                    array(
                        'odtAreaId' => $odtAreaId,
                    )
                )
            )
        );

        $form->handleRequest($request);
        if ($form->isValid())
        {
            $odtStop = $form->getData();
            try
            {
                $odtStopManager->save($odtStop);

                $this->addFlash('success', 'tisseo.flash.success.edited');
            }
            catch (\Exception $e)
            {
                $this->addFlashException($e->getMessage());
            }

            return $this->redirectToRoute(
                'tisseo_boa_odt_area_edit',
                array('odtAreaId' => $odtAreaId)
            );
        }

        return $this->render(
            'TisseoBoaBundle:OdtStop:create.html.twig',
            array(
                'form' => $form->createView(),
                'pageTitle' => ('tisseo.boa.odt_stop.title.create'),
                'odtStop' => $odtStop
            )
        );
    }

    /**
     * delete odtStop
     * @param integer $odtStopId
     *
     * Deleting odtStop
     */
    public function deleteOdtStopAction(Request $request, $odtStopId)
    {
        $this->isGranted(
            array(
                'BUSINESS_MANAGE_STOPS'
            )
        );
        $odtAreaId = explode("/",$odtStopId)[2];
        try {
            $odtStop = $this->get('tisseo_endiv.odt_stop_manager')->find($odtStopId);
            if (!empty($odtStop))
                $this->get('tisseo_endiv.odt_stop_manager')->delete($odtStop);
        } catch(\Exception $e) {
            $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
        }

        return $this->redirectToRoute(
                'tisseo_boa_odt_area_edit',
                array('odtAreaId' => $odtAreaId)
            );
    }
}
