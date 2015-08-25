<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\CoreBundle\Controller\CoreController;
use Tisseo\EndivBundle\Entity\Agency;
use Tisseo\BoaBundle\Form\Type\AgencyType;

class AgencyController extends CoreController
{
    /**
     * List
     *
     * Listing all Agencies
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
            'TisseoBoaBundle:Agency:list.html.twig',
            array(
                'navTitle' => 'tisseo.boa.menu.configuration',
                'pageTitle' => 'tisseo.boa.agency.title.list',
                'agencies' => $this->get('tisseo_endiv.agency_manager')->findAll()
            )
        );
    }

    /**
     * Edit
     * @param integer $agencyId
     *
     * Creating/editing Agency
     */
    public function editAction(Request $request, $agencyId)
    {
        $this->isGranted('BUSINESS_MANAGE_CONFIGURATION');

        $agencyManager = $this->get('tisseo_endiv.agency_manager');
        $agency = $agencyManager->find($agencyId);

        if (empty($agency))
            $agency = new Agency();

        $form = $this->createForm(
            new AgencyType(),
            $agency,
            array(
                'action' => $this->generateUrl(
                    'tisseo_boa_agency_edit',
                    array('agencyId' => $agencyId)
                )
            )
        );

        $form->handleRequest($request);
        if ($form->isValid())
        {
            try
            {
                $agencyManager->save($form->getData());
                $this->addFlash('success', ($agencyId ? 'tisseo.flash.success.edited' : 'tisseo.flash.success.created'));
            }
            catch (\Exception $e)
            {
                $this->addFlashException($e->getMessage());
            }

            return $this->redirectToRoute('tisseo_boa_agency_list');
        }

        return $this->render(
            'TisseoBoaBundle:Agency:form.html.twig',
            array(
                'form' => $form->createView(),
                'title' => ($agencyId ? 'tisseo.boa.agency.title.edit' : 'tisseo.boa.agency.title.create')
            )
        );
    }
}
