<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Tisseo\EndivBundle\Entity\StopArea;
use Tisseo\BoaBundle\Form\Type\StopAreaType;
use Tisseo\BoaBundle\Form\Type\AliasType;
use Tisseo\BoaBundle\Form\Type\StopAreaTransferType;

use Doctrine\Common\Collections\ArrayCollection;

class StopAreaController extends AbstractController
{
    public function searchAction()
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');
		
        return $this->render(
            'TisseoBoaBundle:StopArea:search.html.twig',
            array(
                'title' => 'stop_area.title'
            )
        );
    }	
	
    public function editAction(Request $request, $StopAreaId = null)
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');
		
		$StopAreaManager = $this->get('tisseo_endiv.stop_area_manager');
        $stopArea = $StopAreaManager->find($StopAreaId);
		$lines = $StopAreaManager->getLines($stopArea);
		if (empty($stopArea)) $stopArea = new StopArea($StopAreaManager);
		
		$city = $stopArea->getCity();
		$cityLabel = "";
		$cityMain = $StopAreaManager->getMainStopCityName($stopArea);
		if (!empty($city)) {
			$cityLabel = $city->getName()." (".$city->getInsee().")";
		}
		
		$stops = $StopAreaManager->getCurrentStops($stopArea);
		
		$form = $this->createForm( new StopAreaType($StopAreaManager), $stopArea);
        $form->handleRequest($request);
        if ($form->isValid()) {
			try {
				$datas = $form->getData();
				$StopAreaManager->save($datas);
				if( !$StopAreaId ) $StopAreaId = $datas->getId();
				
				return $this->redirect(
								$this->generateUrl(
									'tisseo_boa_stop_area_edit', 
									array('StopAreaId' => $StopAreaId)
								)
				);

				
			} catch(\Exception $e) {
				$this->get('session')->getFlashBag()->add('danger', $e->getMessage());
			}
		}

		return $this->render(
			'TisseoBoaBundle:StopArea:form.html.twig',
			array(
				'form' => $form->createView(),
				'title' => ($StopAreaId ? 'stop_area.edit' : 'stop_area.create'),
				'cityLabel' => $cityLabel,
				'cityMain' => $cityMain,
				'stopArea' => $stopArea,
				'stops' => $stops,
				'lines' => $lines
			)
		);
	}		
	
    public function internalTransferAction(Request $request, $StopAreaId)
	{
		$this->isGranted('BUSINESS_MANAGE_STOPS');
		$StopAreaManager = $this->get('tisseo_endiv.stop_area_manager');
		$TransferManager = $this->get('tisseo_endiv.transfer_manager');
		$stopArea = $StopAreaManager->find($StopAreaId);
		$stops = $StopAreaManager->getStopsOrderedByCode($StopAreaId);
		$transfers = $TransferManager->getInternalTransfer($stopArea);
		$stopAreaLabel = $stopArea->getNameLabel();
		
		$form = $this->createForm( new StopAreaTransferType($StopAreaManager), $stopArea,
			array(
				'action' => $this->generateUrl('tisseo_boa_internal_transfer_edit',
					array('StopAreaId' => $StopAreaId)
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

    public function saveInternalTransferAction(Request $request, $StopAreaId)
	{
		$this->isGranted('BUSINESS_MANAGE_STOPS');
		
		$StopAreaManager = $this->get('tisseo_endiv.stop_area_manager');
		$TransferManager = $this->get('tisseo_endiv.transfer_manager');
		$stopArea = $StopAreaManager->find($StopAreaId);
		
		$form = $this->createForm( new StopAreaTransferType($StopAreaManager), $stopArea);
		$form->handleRequest($request);
		if ($form->isValid()) {
			try {
				$datas = $form->getData();
				$transfers = $request->request->get('transfer');
				
				$StopAreaManager->save($datas);	//save transfer_duration
				$TransferManager->saveInternalTransfers($transfers);
				
				$response['success'] = true;
			} catch(\Exception $e) {
				$response['success'] = false;
				$response['cause'] = $e->getMessage();
			}
		}
	
		return new JsonResponse( $response );
	}

    public function externalTransferAction(Request $request, $StopAreaId)
	{
		$this->isGranted('BUSINESS_MANAGE_STOPS');
		$StopAreaManager = $this->get('tisseo_endiv.stop_area_manager');
		$TransferManager = $this->get('tisseo_endiv.transfer_manager');
		$stopArea = $StopAreaManager->find($StopAreaId);
		$stops = $StopAreaManager->getStopsOrderedByCode($StopAreaId);
		$transfers = $TransferManager->getExternalTransfer($stopArea);
		$stopAreaLabel = $stopArea->getNameLabel();
		
		$form = $this->createForm( new StopAreaTransferType($StopAreaManager), $stopArea,
			array(
				'action' => $this->generateUrl('tisseo_boa_external_transfer_edit',
					array('StopAreaId' => $StopAreaId)
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
	
    public function saveExternalTransferAction(Request $request, $StopAreaId)
	{
		$this->isGranted('BUSINESS_MANAGE_STOPS');
		
		$StopAreaManager = $this->get('tisseo_endiv.stop_area_manager');
		$TransferManager = $this->get('tisseo_endiv.transfer_manager');
		$stopArea = $StopAreaManager->find($StopAreaId);
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
	
	
    public function aliasAction(Request $request, $StopAreaId)
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');
		$StopAreaManager = $this->get('tisseo_endiv.stop_area_manager');
		$stopArea = $StopAreaManager->find($StopAreaId);
		$stopAreaLabel = $stopArea->getNameLabel();
		
		$originalAliases = new ArrayCollection();
		foreach ($stopArea->getAlias() as $alias) {
			$originalAliases->add($alias);
		}

		$formBuilder = $this->createFormBuilder($stopArea)
			->setAction(
				$this->generateUrl('tisseo_boa_stop_area_alias',
					array('StopAreaId' => $StopAreaId)
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
					array('StopAreaId' => $StopAreaId)
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
