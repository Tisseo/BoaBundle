<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\EndivBundle\Entity\Stop;
use Tisseo\BoaBundle\Form\Type\StopType;

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

    public function editAction(Request $request, $StopId=null)
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');
		
		//current stop
		$StopManager = $this->get('tisseo_endiv.stop_manager');
        $stop = $StopManager->find($StopId);
		
        if (empty($stop)) $stop = new Stop($StopManager);
		
		
		$masterStopLabel = "";
		$masterStop = $stop->getMasterStop();
		if (!empty($masterStop)) {
			$masterStopLabel = $StopManager->getStopLabel($masterStop);
		}
			
        $form = $this->createForm( new StopType($StopManager), $stop,
				array('action' => $this->generateUrl('tisseo_boa_stop_edit',
														array('StopId' => $StopId)
											)
				)
        );		
		
        $form->handleRequest($request);
        if ($form->isValid()) {
			try {
				$StopManager->save($form->getData());
			} catch(\Exception $e) {
				$this->get('session')->getFlashBag()->add('danger', $e->getMessage());
			}
/*				
            return $this->redirect(
                $this->generateUrl('tisseo_tid_line_list')
            );
*/			
        }
		
		return $this->render(
			'TisseoBoaBundle:Stop:form.html.twig',
			array(
				'form' => $form->createView(),
				'title' => ($StopId ? 'stop.edit' : 'stop.create'),
				'masterStopLabel' => $masterStopLabel
			)
		);
	}
	
}
