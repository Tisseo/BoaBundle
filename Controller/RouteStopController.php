<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

use Tisseo\BoaBundle\Form\Type\RouteStopType;
use Tisseo\EndivBundle\Entity\RouteStop;
use Tisseo\EndivBundle\Entity\Route;

class RouteStopController extends AbstractController
{
    /*
     * Build Form
     * @param Route $route
     * @param integer $rank
     * @return Form $form
     *
     * Build a new RouteStopType form.
     */
    private function buildForm(Route $route, $rank = 1)
    {
        $routeStop = new RouteStop();
        $routeStop->setRoute($route);
        $routeStop->setRank($rank);

        $form = $this->createForm(
            new RouteStopType(),
            $routeStop,
            array(
                'em' => $this->getDoctrine()->getManager($this->container->getParameter('endiv_database_connection'))
            )
        );

        return ($form);
    }

    /*
     * Process Form
     * @param Form $form
     * @param integer $routeId
     *
     * If form is valid, return a new RouteStop rendered in a specific view
     * (as html table for view integration).
     * Else, return the actual form view with errors.
     */
    private function processForm(Request $request, $form)
    {
        $form->handleRequest($request);
        if ($form->isValid())
        {
            $routeStop = $form->getData();

            return $this->render(
                'TisseoBoaBundle:RouteStop:new.html.twig',
                array(
                    'routeStop' => $routeStop
                )
            );
        }

        return $this->render(
            'TisseoBoaBundle:RouteStop:form.html.twig',
            array(
                'form' => $form->createView()
            )
        );
    }

    /*
     * Render Form
     * @param integer $routeId
     * @param integer $rank
     *
     * This method is called through ajax request in order to display a new
     * fresh RouteStopType form when a previous one has just been
     * submitted and validated.
     */
    public function renderFormAction($routeId, $rank)
    {
        $this->isGranted('BUSINESS_MANAGE_ROUTES');

        $route = $this->get('tisseo_endiv.route_manager')->find($routeId);

        $form = $this->buildForm($route, $rank);

        return $this->render(
            'TisseoBoaBundle:RouteStop:form.html.twig',
            array(
                'form' => $form->createView(),
                'rank' => $rank
            )
        );
    }

    /**
     * List
     * @param integer $routeId
     * 
     * List Route's RouteStops
     */
    public function listAction($routeId)
    {
        $this->isGranted('BUSINESS_MANAGE_ROUTES');

        $route =  $this->get('tisseo_endiv.route_manager')->find($routeId);

        return $this->render(
            'TisseoBoaBundle:RouteStop:list.html.twig',
            array(
                'route' => $route
            )
        );
    }

    /**
     * Edit
     * @param integer $routeStopId
     *
     * If request's method is GET, display a pseudo-form (ajax/json) which
     * purpose is to create/delete RouteStop.
     *
     * Otherwise, the pseudo-form data is sent as AJAX POST request and is
     * decoded then will be used for database update.
     */
    public function editAction(Request $request, $routeId)
    {
        $this->isGranted('BUSINESS_MANAGE_ROUTES');
        
        $route = $this->get('tisseo_endiv.route_manager')->find($routeId);

        if ($request->isXmlHttpRequest() && $request->getMethod() === 'POST')
        {
            $routeStops = json_decode($request->getContent(), true);
            try {
                $this->get('tisseo_endiv.routestop_manager')->updateRouteStops($routeStops, $route);
                $this->get('session')->getFlashBag()->add('success', 'route_stop.edited');
                $code = 302;
            } catch (\Exception $e) {
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
                $code = 500;
            }

            $response = $this->redirect(
                $this->generateUrl(
                    'tisseo_boa_route_edit',
                    array('routeId' => $routeId)
                )
            );
            $response->setStatusCode($code);

            return $response;
        }

        return $this->render(
            'TisseoBoaBundle:RouteStop:edit.html.twig',
            array(
                'route' => $route
            )
        );
    }

    /*
     * Create
     * @param integer $routeId
     *
     * This function is called though ajax request and will launch RouteStopType
     * form validation process.
     */
    public function createAction(Request $request, $routeId)
    {
        $this->isGranted('BUSINESS_MANAGE_ROUTES');
        $this->isPostAjax($request);

        $route = $this->get('tisseo_endiv.route_manager')->find($routeId);
        
        return $this->processForm($request, $this->buildForm($route));
    }
}