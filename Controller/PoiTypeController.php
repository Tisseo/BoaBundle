<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\CoreBundle\Controller\CoreController;
use Tisseo\EndivBundle\Entity\PoiType;
use Tisseo\BoaBundle\Form\Type\PoiTypeType;

class PoiTypeController extends CoreController
{
    /**
     * List
     *
     * Listing all PoiTypes
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted(
            array(
                'BUSINESS_MANAGE_CONFIGURATION',
                'BUSINESS_VIEW_CONFIGURATION'
            )
        );

        return $this->render(
            'TisseoBoaBundle:PoiType:list.html.twig',
            array(
                'navTitle' => 'tisseo.boa.menu.configuration',
                'pageTitle' => 'tisseo.boa.poi_type.title.list',
                'poiTypes' => $this->get('tisseo_endiv.manager.poi_type')->findAll()
            )
        );
    }

    /**
     * Edit
     * @param integer $poiTypeId
     *
     * Creating/editing PoiType
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, $poiTypeId)
    {
        $this->denyAccessUnlessGranted('BUSINESS_MANAGE_CONFIGURATION');

        $poiTypeManager = $this->get('tisseo_endiv.manager.poi_type');
        $poiType = $poiTypeManager->find($poiTypeId);

        if (empty($poiType))
            $poiType = new PoiType();

        $form = $this->createForm(
            new PoiTypeType(),
            $poiType,
            array(
                'action' => $this->generateUrl(
                    'tisseo_boa_poi_type_edit',
                    array('poiTypeId' => $poiTypeId)
                )
            )
        );

        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                $poiTypeManager->save($form->getData());
                $this->addFlash('success', ($poiTypeId ? 'tisseo.flash.success.edited' : 'tisseo.flash.success.created'));
            } catch (\Exception $e) {
                $this->addFlashException($e->getMessage());
            }

            return $this->redirectToRoute('tisseo_boa_poi_type_list');
        }

        return $this->render(
            'TisseoBoaBundle:PoiType:form.html.twig',
            array(
                'form' => $form->createView(),
                'title' => ($poiTypeId ? 'tisseo.boa.poi_type.title.edit' : 'tisseo.boa.poi_type.title.create')
            )
        );
    }
}
