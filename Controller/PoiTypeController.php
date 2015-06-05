<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\EndivBundle\Entity\PoiType;
use Tisseo\BoaBundle\Form\Type\PoiTypeType;

class PoiTypeController extends AbstractController
{
    public function listAction()
    {
        $this->isGranted(
            array(
                'BUSINESS_MANAGE_CONFIGURATION',
                'BUSINESS_VIEW_CONFIGURATION'
            )
        );
		
		$PoiTypeManager = $this->get('tisseo_endiv.poi_type_manager');
        return $this->render(
            'TisseoBoaBundle:PoiType:list.html.twig',
            array(
                'pageTitle' => 'menu.poi_type',
                'poi_types' => $PoiTypeManager->findAll()
            )
        );
    }
	
    public function editAction(Request $request, $PoiTypeId)
    {
        $this->isGranted('BUSINESS_MANAGE_PARAMETERS');
		
		$PoiTypeManager = $this->get('tisseo_endiv.poi_type_manager');
		$form = $this->buildForm($PoiTypeId, $PoiTypeManager);
        $render = $this->processForm($request, $form);
		if (!$render) {
            return $this->render(
                'TisseoBoaBundle:PoiType:form.html.twig',
                array(
                    'form' => $form->createView(),
                    'title' => ($PoiTypeId ? 'poi_type.edit' : 'poi_type.create')
                )
            );
        }
        return ($render);
    }

    private function buildForm($PoiTypeId, $PoiTypeManager)
    {
        $poi_type = $PoiTypeManager->find($PoiTypeId);
        if (empty($poi_type)) {
            $poi_type = new PoiType();
        }		

        $form = $this->createForm( new PoiTypeType(), $poi_type,
            array(
                'action' => $this->generateUrl('tisseo_boa_poi_type_edit',
											array('PoiTypeId' => $PoiTypeId)
                )
            )
        );
		
		return ($form);
    }	

    private function processForm(Request $request, $form)
    {
        $form->handleRequest($request);
        $PoiTypeManager = $this->get('tisseo_endiv.poi_type_manager');
        if ($form->isValid()) {
            $PoiTypeManager->save($form->getData());
            $this->get('session')->getFlashBag()->add('success',
                $this->get('translator')->trans(
                    'poi_type.created',
                    array(),
                    'default'
                )
            );
            return $this->redirect(
                $this->generateUrl('tisseo_boa_poi_type_list')
            );
        }
        return (null);
    }

}
