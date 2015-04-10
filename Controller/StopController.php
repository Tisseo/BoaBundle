<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\EndivBundle\Entity\Stop;
use Tisseo\BoaBundle\Form\Type\StopType;
use Tisseo\BoaBundle\Form\Type\NewStopType;

class StopController extends AbstractController
{
    public function searchAction()
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');
		
        return $this->render(
            'TisseoBoaBundle:Stop:search.html.twig',
            array(
                'title' => 'stop.title'
            )
        );
    }

    public function editAction(Request $request, $StopId)
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');
		
		//current stop
		$StopManager = $this->get('tisseo_endiv.stop_manager');
        $stop = $StopManager->find($StopId);
		
		if (empty($stop)) $stop = new Stop($StopManager);
		
		$masterStopLabel = "";
		$masterStop = $stop->getMasterStop();
		$stopHistories = $stop->getStopHistories();
		$phantoms = $stop->getPhantoms();
		$accessibilities = $stop->getStopAccessibilities();
		if (!empty($masterStop)) {
			$masterStopLabel = $StopManager->getStopLabel($masterStop);
		}

		$form = $this->createForm( new StopType($StopManager), $stop);
        $form->handleRequest($request);
        if ($form->isValid()) {
			try {
				$StopManager->save($form->getData());
			} catch(\Exception $e) {
				$this->get('session')->getFlashBag()->add('danger', $e->getMessage());
			}
        }
		
		return $this->render(
			'TisseoBoaBundle:Stop:form.html.twig',
			array(
				'form' => $form->createView(),
				'title' => 'stop.edit',
				'masterStopLabel' => $masterStopLabel,
				'stopHistories' => $stopHistories,
				'phantoms' => $phantoms,
				'accessibilities' => $accessibilities
			)
		);
	}	

    public function newAction(Request $request)
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');
		
		$StopManager = $this->get('tisseo_endiv.stop_manager');
		$stop = new Stop($StopManager);
		
        $form = $this->createForm( new NewStopType($StopManager), $stop,
				array('action' => $this->generateUrl('tisseo_boa_stop_new',
														array('StopId' => $stop)
											)
				)
        );		

        $form->handleRequest($request);
        if ($form->isValid()) {
			$StopId =null;
			try {				
				$datas = $form->getData();
				$StopManager->save($datas);
				$StopId = $datas->getId();

				return $this->redirect(
					$this->generateUrl('tisseo_boa_stop_edit', 
						array('StopId' => $StopId)
					)
				);				
			} catch(\Exception $e) {
				$this->get('session')->getFlashBag()->add('danger', $e->getMessage());
			}

			return $this->redirect(
				$this->generateUrl('tisseo_boa_stop_edit', 
					array('StopId' => $StopId)
				)
			);				
        }
				
		return $this->render(
			'TisseoBoaBundle:Stop:new.html.twig',
			array(
				'form' => $form->createView(),
				'title' => 'stop.create'
			)
		);
    }
}
