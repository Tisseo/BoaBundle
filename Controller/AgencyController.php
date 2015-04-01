<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\EndivBundle\Entity\Agency;
use Tisseo\BoaBundle\Form\Type\AgencyType;

class AgencyController extends AbstractController
{
    public function listAction()
    {
        $this->isGranted('BUSINESS_MANAGE_PARAMETERS');
		
		$AgencyManager = $this->get('tisseo_endiv.agency_manager');
        return $this->render(
            'TisseoBoaBundle:Agency:list.html.twig',
            array(
                'pageTitle' => 'menu.agency',
                'agencies' => $AgencyManager->findAll()
            )
        );
    }
	
    public function editAction(Request $request, $AgencyId)
    {
        $this->isGranted('BUSINESS_MANAGE_PARAMETERS');
		
		$AgencyManager = $this->get('tisseo_endiv.agency_manager');
		$form = $this->buildForm($AgencyId, $AgencyManager);
        $render = $this->processForm($request, $form);
		if (!$render) {
            return $this->render(
                'TisseoBoaBundle:Agency:form.html.twig',
                array(
                    'form' => $form->createView(),
                    'title' => ($AgencyId ? 'agency.edit' : 'agency.create')
                )
            );
        }
        return ($render);
    }

    private function buildForm($AgencyId, $AgencyManager)
    {
        $agency = $AgencyManager->find($AgencyId);
        if (empty($agency)) {
            $agency = new Agency();
        }		

        $form = $this->createForm( new AgencyType(), $agency,
            array(
                'action' => $this->generateUrl('tisseo_boa_agency_edit',
											array('AgencyId' => $AgencyId)
                )
            )
        );
		
		return ($form);
    }	

    private function processForm(Request $request, $form)
    {
        $form->handleRequest($request);
        $AgencyManager = $this->get('tisseo_endiv.agency_manager');
        if ($form->isValid()) {
            $AgencyManager->save($form->getData());
            $this->get('session')->getFlashBag()->add('success',
                $this->get('translator')->trans(
                    'agency.created',
                    array(),
                    'default'
                )
            );
            return $this->redirect(
                $this->generateUrl('tisseo_boa_agency_list')
            );
        }
        return (null);
    }

}
