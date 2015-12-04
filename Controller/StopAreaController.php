<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Common\Collections\ArrayCollection;
use Tisseo\CoreBundle\Controller\CoreController;
use Tisseo\EndivBundle\Entity\StopArea;
use Tisseo\EndivBundle\Entity\StopAreaDatasource;
use Tisseo\BoaBundle\Form\Type\StopAreaType;
use Tisseo\BoaBundle\Form\Type\AliasType;
use Tisseo\BoaBundle\Form\Type\StopAreaTransferType;

class StopAreaController extends CoreController
{
    /**
     * Search
     *
     * Searching for StopAreas
     */
    public function searchAction()
    {
        $this->isGranted(array(
                'BUSINESS_MANAGE_STOPS',
                'BUSINESS_VIEW_STOPS',
            )
        );

        return $this->render(
            'TisseoBoaBundle:StopArea:search.html.twig',
            array(
                'navTitle' => 'tisseo.boa.menu.stop.manage',
                'pageTitle' => 'tisseo.boa.menu.stop.area'
            )
        );
    }

    /**
     * Edit
     * @param integer $stopAreaId
     *
     * Creating/editing StopArea
     */
    public function editAction(Request $request, $stopAreaId = null)
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');

        $stopAreaManager = $this->get('tisseo_endiv.stop_area_manager');
        $stopArea = $stopAreaManager->find($stopAreaId);
        if (empty($stopArea))
        {
            $stopArea = new StopArea();
            $stopAreaDatasource = new StopAreaDatasource();
            $this->addBoaDatasource($stopAreaDatasource);
            $stopArea->addStopAreaDatasources($stopAreaDatasource);
            $linesByStop = null;
            $mainStopArea = false;
            $stops = null;
            $stopsJson = null;
        }
        else
        {
            $linesByStop = $stopAreaManager->getLinesByStop($stopAreaId);
            $mainStopArea = $stopArea->isMainOfCity();
            $stops = $stopAreaManager->getCurrentStops($stopArea);
            $stopsJson = $stopAreaManager->getStopsJson($stopArea);
            foreach($stopsJson as $key => $stopJson) {
                $stopsJson[$key]['route'] = $this->generateUrl(
                    'tisseo_boa_stop_edit',
                    array('stopId' => $stopJson['id'])
                );
            }
            $stopsJson = json_encode($stopsJson);
        }

        $form = $this->createForm(
            new StopAreaType(),
            $stopArea,
            array(
                'action' => $this->generateUrl(
                    'tisseo_boa_stop_area_edit',
                    array('stopAreaId' => $stopAreaId)
                )
            )
        );

        $form->handleRequest($request);
        if ($form->isValid())
        {
            $stopArea = $form->getData();

            try
            {
                $stopAreaId = $stopAreaManager->save($stopArea);
                $this->addFlash('success', 'tisseo.flash.success.edited');
            }
            catch(\Exception $e)
            {
                $stopAreaId = null;
                $this->addFlashException($e->getMessage());
            }

            return $this->redirectToRoute(
                'tisseo_boa_stop_area_edit',
                array('stopAreaId' => $stopAreaId)
            );
        }

        return $this->render(
            'TisseoBoaBundle:StopArea:edit.html.twig',
            array(
                'pageTitle' => 'tisseo.boa.stop_area.title.edit',
                'form' => $form->createView(),
                'stopArea' => $stopArea,
                'stops' => $stops,
                'stopsJson' => $stopsJson,
                'linesByStop' => $linesByStop,
                'mainStopArea' => $mainStopArea
            )
        );
    }

    public function internalTransferAction(Request $request, $stopAreaId)
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');

        $stopAreaManager = $this->get('tisseo_endiv.stop_area_manager');
        $transferManager = $this->get('tisseo_endiv.transfer_manager');
        $stopArea = $stopAreaManager->find($stopAreaId);
        $transfers = $transferManager->getInternalTransfers($stopArea);

        if ($request->isXmlHttpRequest() && $request->getMethod() === 'POST')
        {
            $data = json_decode($request->getContent(), true);
            $stopAreaTransferDuration = $data['stopAreaTransferDuration'];
            $transfers = $data['transfers'];

            try {
                if (empty($stopAreaTransferDuration) || !is_numeric($stopAreaTransferDuration) || $stopAreaTransferDuration < 0 || $stopAreaTransferDuration > 60){
                    throw new \Exception($this->get('translator')->trans('tisseo.boa.transfer.error.stop_area_transfer_duration_invalid'));
                }

                $transferManager->saveInternalTransfers($transfers, $stopArea);

                if ($stopArea->getTransferDuration() !== (int)$stopAreaTransferDuration) {
                    $stopArea->setTransferDuration($stopAreaTransferDuration);
                    $stopAreaManager->save($stopArea);
                }

                $this->addFlash('success', 'tisseo.flash.success.edited');
                $code = 302;
            } catch (\Exception $e) {
                $this->addFlashException($e->getMessage());
                $code = 500;
            }

            $response = $this->redirectToRoute(
                'tisseo_boa_stop_area_edit',
                array('stopAreaId' => $stopAreaId)
            );
            $response->setStatusCode($code);

            return $response;
        }

        return $this->render(
            'TisseoBoaBundle:StopArea:internal_transfer.html.twig',
            array(
                'title' => 'tisseo.boa.transfer.title.list',
                'stopArea' => $stopArea,
                'transfers' => $transfers,
            )
        );
    }

    public function externalTransferAction(Request $request, $stopAreaId)
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');

        $stopAreaManager = $this->get('tisseo_endiv.stop_area_manager');
        $transferManager = $this->get('tisseo_endiv.transfer_manager');
        $stopArea = $stopAreaManager->find($stopAreaId);
        $startStops = $stopAreaManager->getStopsOrderedByCode($stopArea, true);
        $transfers = $transferManager->getExternalTransfers($stopArea);

        if ($request->isXmlHttpRequest() && $request->getMethod() === 'POST')
        {
            $data = json_decode($request->getContent(), true);

            try {
                $transferManager->saveExternalTransfers($data, $stopArea);
                $this->addFlash('success', 'tisseo.flash.success.edited');
                $code = 302;
            } catch (\Exception $e) {
                $this->addFlashException($e->getMessage());
                $code = 500;
            }

            $response = $this->redirectToRoute(
                'tisseo_boa_stop_area_edit',
                array('stopAreaId' => $stopAreaId)
            );
            $response->setStatusCode($code);

            return $response;
        }

        return $this->render(
            'TisseoBoaBundle:StopArea:external_transfer.html.twig',
            array(
                'title' => 'tisseo.boa.transfer.title.list',
                'stopArea' => $stopArea,
                'startStops' => $startStops,
                'transfers' => $transfers
            )
        );
    }

        /*
     * Create
     * @param integer $stopAreaId
     *
     * This function is called though ajax request in order to create transfers
     */
    public function createExternalTransferAction(Request $request, $stopAreaId)
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');
        $this->isPostAjax($request);

        $stopAreaManager = $this->get('tisseo_endiv.stop_area_manager');
        $stopArea = $stopAreaManager->find($stopAreaId);

        if ($request->isXmlHttpRequest() && $request->getMethod() === 'POST')
        {
            try {
                $data = json_decode($request->getContent(), true);
                $transfers = $this->get('tisseo_endiv.transfer_manager')->createExternalTransfers($data, $stopArea);
            } catch (\Exception $e) {
                $this->addFlashException($e->getMessage());
                $response = $this->redirectToRoute(
                    'tisseo_boa_stop_area_edit',
                    array('stopAreaId' => $stopAreaId)
                );
                $response->setStatusCode(500);
                return $response;
            }
            return $this->render(
                'TisseoBoaBundle:StopArea:create_external_transfer.html.twig',
                array(
                    'transfers' => $transfers,
                )
            );
        }
    }

/**
     * EditAliases
     * @param integer $stopAreaId
     *
     * the pseudo-form data is sent as AJAX POST request and is
     * decoded then will be used for database update.
     */
    public function editAliasesAction(Request $request, $stopAreaId)
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');

        $stopArea = $this->get('tisseo_endiv.stop_area_manager')->find($stopAreaId);

        if ($request->isXmlHttpRequest() && $request->getMethod() === 'POST')
        {
            $aliases = json_decode($request->getContent(), true);

            try {
                $this->get('tisseo_endiv.stop_area_manager')->updateAliases($aliases, $stopArea);
                $this->addFlash('success', 'tisseo.flash.success.edited');
                $code = 302;
            } catch (\Exception $e) {
                $this->addFlashException($e->getMessage());
                $code = 500;
            }

            $response = $this->redirectToRoute(
                'tisseo_boa_stop_area_edit',
                array('stopAreaId' => $stopAreaId)
            );
            $response->setStatusCode($code);

            return $response;
        }

        return $this->render(
            'TisseoBoaBundle:StopArea:alias.html.twig',
            array(
                'stopArea' => $stopArea,
                'title' => 'tisseo.boa.alias.title.list'
            )
        );
    }
}
