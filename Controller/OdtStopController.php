<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\CoreBundle\Controller\CoreController;
use Tisseo\BoaBundle\Form\Type\OdtStopType;
use Tisseo\EndivBundle\Entity\OdtArea;
use Tisseo\EndivBundle\Entity\OdtStop;

class OdtStopController extends CoreController
{
    /*
     * Build Form
     * @param OdtArea $odtArea
     * @return Form $form
     *
     * Build a new OdtStopType form.
     */
    private function buildForm(OdtArea $odtArea)
    {
        $odtStop = new OdtStop();
        $odtStop->setOdtArea($odtArea);

        $form = $this->createForm(
            new OdtStopType(),
            $odtStop,
            array(
                'em' => $this->getDoctrine()->getManager($this->container->getParameter('endiv_database_connection'))
            )
        );

        return $form;
    }

    /*
     * Render Form
     * @param integer $odtAreaId
     *
     * This method is called through ajax request in order to display a new
     * fresh OdtStopType form when a previous one has just been
     * submitted and validated.
     */
    public function renderFormAction($odtAreaId)
    {
        $this->denyAccessUnlessGranted('BUSINESS_MANAGE_STOPS');

        $odtArea = $this->get('tisseo_endiv.odt_area_manager')->find($odtAreaId);

        $form = $this->buildForm($odtArea);

        return $this->render(
            'TisseoBoaBundle:OdtStop:form.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    /**
     * Edit
     *
     * @param int $odtAreaId
     *
     * If request's method is GET, display a pseudo-form (ajax/json) which
     * purpose is to create/delete OdtStop.
     *
     * Otherwise, the pseudo-form data is sent as AJAX POST request and is
     * decoded then will be used for database update.
     */
    public function editAction(Request $request, $odtAreaId)
    {
        $this->denyAccessUnlessGranted('BUSINESS_MANAGE_STOPS');

        $odtArea = $this->get('tisseo_endiv.odt_area_manager')->find($odtAreaId);

        if ($request->isXmlHttpRequest() && $request->getMethod() === Request::METHOD_POST) {
            $odtStops = json_decode($request->getContent(), true);

            try {
                $this->get('tisseo_endiv.odt_stop_manager')->updateOdtStops($odtStops, $odtArea);
                $this->addFlash('success', 'tisseo.flash.success.edited');
                $code = 302;
            } catch (\Exception $e) {
                $this->addFlashException($e->getMessage());
                $code = 500;
            }

            $response = $this->redirectToRoute(
                'tisseo_boa_odt_area_edit',
                array('odtAreaId' => $odtAreaId)
            );
            $response->setStatusCode($code);

            return $response;
        }

        return $this->render(
            'TisseoBoaBundle:OdtStop:edit.html.twig',
            array(
                'odtArea' => $odtArea
            )
        );
    }

    /*
     * Create
     * @param integer $odtAreaId
     *
     * This function is called though ajax request and will launch RouteStopType
     * form validation process.
     */
    public function createAction(Request $request, $odtAreaId)
    {
        $this->denyAccessUnlessGranted('BUSINESS_MANAGE_STOPS');
        $this->isAjax($request, Request::METHOD_POST);

        $odtArea = $this->get('tisseo_endiv.odt_area_manager')->find($odtAreaId);

        $form = $this->buildForm($odtArea);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $odtStop = $form->getData();

            return $this->render(
                'TisseoBoaBundle:OdtStop:new.html.twig',
                array(
                    'odtStop' => $odtStop,
                )
            );
        }

        return $this->render(
            'TisseoBoaBundle:OdtStop:form.html.twig',
            array(
                'form' => $form->createView()
            )
        );
    }

    /*
     * Create
     * @param integer $odtAreaId
     *
     * This function is called though ajax request and will launch RouteStopType
     * form validation process.
     */
    public function createGroupAction(Request $request, $odtAreaId)
    {
        $this->denyAccessUnlessGranted('BUSINESS_MANAGE_STOPS');
        $this->isAjax($request, Request::METHOD_POST);

        $odtArea = $this->get('tisseo_endiv.odt_area_manager')->find($odtAreaId);

        $form = $this->buildForm($odtArea);
        $form->handleRequest($request);

        try {
            $data = json_decode($request->getContent(), true);
            $odtStops = $this->get('tisseo_endiv.odt_stop_manager')->getGroupedOdtStops($data, $odtArea);
        } catch (\Exception $e) {
            $this->addFlashException($e->getMessage());
            $response = $this->redirectToRoute(
                'tisseo_boa_odt_area_edit',
                array('odtAreaId' => $odtAreaId)
            );
            $response->setStatusCode(500);

            return $response;
        }

        return $this->render(
            'TisseoBoaBundle:OdtStop:new.group.html.twig',
            array(
                'odtStops' => $odtStops,
            )
        );
    }
}
