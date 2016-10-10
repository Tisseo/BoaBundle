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
        $this->denyAccessUnlessGranted(
            array(
                'BUSINESS_MANAGE_STOPS',
                'BUSINESS_VIEW_STOPS'
            )
        );

        /**$log = fopen('/tmp/test-jean.log','a+');
        foreach ($this->get('tisseo_endiv.manager.odt_area')->findAll() as $odtArea)
            fwrite($log, "\n".json_encode($odtArea->getOdtStops()->first()->getId()));

        fclose($log);*/
        return $this->render(
            'TisseoBoaBundle:OdtArea:list.html.twig',
            array(
                'navTitle' => 'tisseo.boa.menu.stop.manage',
                'pageTitle' => 'tisseo.boa.odt_area.title.list',
                'odtAreas' => $this->get('tisseo_endiv.manager.odt_area')->findAll(),
                'linesByOdtArea' => $this->get('tisseo_endiv.manager.odt_area')->getLinesByOdtArea(),
            )
        );
    }

    public function deleteAction($odtAreaId)
    {
        $this->denyAccessUnlessGranted(
            array(
                'BUSINESS_MANAGE_STOPS',
                'BUSINESS_VIEW_STOPS'
            )
        );

        try {
            $odtArea = $this->get('tisseo_endiv.manager.odt_area')->find($odtAreaId);
            if (!empty($odtArea))
                $this->get('tisseo_endiv.manager.odt_area')->delete($odtArea);
        } catch(\Exception $e) {
            $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
        }

        return $this->redirectToRoute('tisseo_boa_odt_area_list');
    }

    /**
     * Edit
     * @param integer $odtAreaId
     *
     * Creating/editing odtArea
     */
    public function editAction(Request $request, $odtAreaId)
    {
        $this->denyAccessUnlessGranted('BUSINESS_MANAGE_STOPS');

        $odtAreaManager = $this->get('tisseo_endiv.manager.odt_area');
        $odtArea = $odtAreaManager->find($odtAreaId);
        if (empty($odtArea))
        {
            $odtArea = new OdtArea();
            $stopsJson = null;
        }
        else
        {
            $stopsJson = $odtAreaManager->getOdtStopsJson($odtArea);
            foreach($stopsJson as $key => $stopJson) {
                $stopsJson[$key]['route'] = $this->generateUrl(
                    'tisseo_boa_stop_edit',
                    array('stopId' => $stopJson['id'])
                );
            }
            $stopsJson = json_encode($stopsJson);
        }
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
                if (empty($odtAreaId))
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
                'odtArea' => $odtArea,
                'stopsJson' => $stopsJson
            )
        );
    }
}
