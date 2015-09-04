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
        }
        else
        {
            $linesByStop = $stopAreaManager->getLinesByStop($stopAreaId);
            $mainStopArea = $stopArea->isMainOfCity();
            $stops = $stopAreaManager->getCurrentStops($stopArea);
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
                'linesByStop' => $linesByStop,
                'mainStopArea' => $mainStopArea
            )
        );
    }

    // TODO: check this
    public function internalTransferAction($stopAreaId)
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');

        $StopAreaManager = $this->get('tisseo_endiv.stop_area_manager');
        $TransferManager = $this->get('tisseo_endiv.transfer_manager');
        $stopArea = $StopAreaManager->find($stopAreaId);
        $stops = $StopAreaManager->getStopsOrderedByCode($stopAreaId);
        $transfers = $TransferManager->getInternalTransfer($stopArea);
        $stopAreaLabel = $stopArea->getNameLabel();

        $form = $this->createForm( new StopAreaTransferType($StopAreaManager), $stopArea,
            array(
                'action' => $this->generateUrl('tisseo_boa_internal_transfer_edit',
                    array('stopAreaId' => $stopAreaId)
                )
            )
        );

        return $this->render(
            'TisseoBoaBundle:StopArea:internal_transfer.html.twig',
            array(
                'form' => $form->createView(),
                'title' => 'stop_area.transfer',
                'stopArea' => $stopArea,
                'stops' => $stops,
                'transfers' => $transfers,
                'stopAreaLabel' => $stopAreaLabel
            )
        );
    }

    public function saveInternalTransferAction(Request $request, $stopAreaId)
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');

        $StopAreaManager = $this->get('tisseo_endiv.stop_area_manager');
        $TransferManager = $this->get('tisseo_endiv.transfer_manager');
        $stopArea = $StopAreaManager->find($stopAreaId);

        $form = $this->createForm( new StopAreaTransferType($StopAreaManager), $stopArea);
        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                $datas = $form->getData();
                $transfers = $request->request->get('transfer');

                $StopAreaManager->save($datas);    //save transfer_duration
                $TransferManager->saveInternalTransfers($transfers);

                $response['success'] = true;
            } catch(\Exception $e) {
                $response['success'] = false;
                $response['cause'] = $e->getMessage();
            }
        }

        return new JsonResponse( $response );
    }

    // TODO: check this
    public function externalTransferAction($stopAreaId)
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');

        $StopAreaManager = $this->get('tisseo_endiv.stop_area_manager');
        $TransferManager = $this->get('tisseo_endiv.transfer_manager');
        $stopArea = $StopAreaManager->find($stopAreaId);
        $stops = $StopAreaManager->getStopsOrderedByCode($stopAreaId);
        $transfers = $TransferManager->getExternalTransfer($stopArea);
        $stopAreaLabel = $stopArea->getNameLabel();

        $form = $this->createForm( new StopAreaTransferType($StopAreaManager), $stopArea,
            array(
                'action' => $this->generateUrl('tisseo_boa_external_transfer_edit',
                    array('stopAreaId' => $stopAreaId)
                )
            )
        );

        return $this->render(
            'TisseoBoaBundle:StopArea:external_transfer.html.twig',
            array(
                'form' => $form->createView(),
                'title' => 'stop_area.transfer',
                'stopArea' => $stopArea,
                'stops' => $stops,
                'transfers' => $transfers,
                'stopAreaLabel' => $stopAreaLabel
            )
        );
    }

    // TODO: check this
    public function saveExternalTransferAction(Request $request, $stopAreaId)
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');

        $StopAreaManager = $this->get('tisseo_endiv.stop_area_manager');
        $TransferManager = $this->get('tisseo_endiv.transfer_manager');
        $stopArea = $StopAreaManager->find($stopAreaId);
        try {
            $transfers = $request->request->get('transfer');
            $ignoredTransfers = $TransferManager->saveExternalTransfers($stopArea, $transfers);

            if( $ignoredTransfers > 0 ) {
                $response['cause'] = $this->get('translator')->trans(
                                                    'stop_area.transfer.error.duplicate_transfer',
                                                    array('%i%' => $ignoredTransfers),
                                                    'messages'
                                                );
            }

            $response['success'] = true;
        } catch(\Exception $e) {
            $response['success'] = false;
            $response['cause'] = $e->getMessage();
        }
        return new JsonResponse( $response );
    }

    // TODO: check this
    public function aliasAction(Request $request, $stopAreaId)
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');

        $StopAreaManager = $this->get('tisseo_endiv.stop_area_manager');
        $stopArea = $StopAreaManager->find($stopAreaId);
        $stopAreaLabel = $stopArea->getNameLabel();

        $originalAliases = new ArrayCollection();
        foreach ($stopArea->getAlias() as $alias) {
            $originalAliases->add($alias);
        }

        $formBuilder = $this->createFormBuilder($stopArea)
            ->setAction(
                $this->generateUrl('tisseo_boa_stop_area_alias',
                    array('stopAreaId' => $stopAreaId)
                )
            )
            ->add('alias', 'collection',
            array(
                'label' => 'stop_area.labels.alias',
                'allow_add' => true,
                'allow_delete' => true,
                'type' => new AliasType(),
                'by_reference' => false,
            )
        );
        $form = $formBuilder->getForm();
        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                $datas = $form->getData();
                $StopAreaManager->saveAliases($datas, $originalAliases);
            } catch(\Exception $e) {
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }

            return $this->redirect(
                $this->generateUrl('tisseo_boa_stop_area_edit',
                    array('stopAreaId' => $stopAreaId)
                )
            );
        }

        return $this->render(
            'TisseoBoaBundle:StopArea:alias.html.twig',
            array(
                'form' => $form->createView(),
                'title' => 'stop_area.alias',
                'stopAreaLabel' => $stopAreaLabel
            )
        );
    }
}
