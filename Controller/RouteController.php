<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\CoreBundle\Controller\CoreController;
use Tisseo\BoaBundle\Form\Type\RouteCreateType;
use Tisseo\BoaBundle\Form\Type\RouteEditType;
use Tisseo\BoaBundle\Form\Type\RouteDuplicateType;
use Tisseo\EndivBundle\Entity\Route;
use Tisseo\EndivBundle\Entity\RouteDatasource;

class RouteController extends CoreController
{
    /**
     * List
     * @param integer $lineVersionId
     *
     * Listing all Routes
     */
    public function listAction($lineVersionId)
    {
        $this->isGranted(
            array(
                'BUSINESS_MANAGE_ROUTES',
                'BUSINESS_VIEW_ROUTES'
            )
        );

        $lineVersion = $this->get('tisseo_endiv.line_version_manager')->find($lineVersionId);
        return $this->render('TisseoBoaBundle:Route:list.html.twig',
            array(
                'navTitle' => 'tisseo.boa.menu.route',
                'pageTitle' => 'tisseo.boa.route.title.list',
                'titleParameters' => array(
                    '%number%' => $lineVersion->getLine()->getNumber(),
                    '%version%' => $lineVersion->getVersion()
                ),
                'lineVersion' => $lineVersion
            )
        );
    }

    /**
     * Create
     * @param integer $lineVersionId
     */
    public function createAction(Request $request, $lineVersionId)
    {
        $this->isGranted('BUSINESS_MANAGE_ROUTES');

        $lineVersionManager = $this->get('tisseo_endiv.line_version_manager');
        $lineVersion = $lineVersionManager->find($lineVersionId);

        $route = new Route();
        $route->setLineVersion($lineVersion);
        $routeDatasource = new RouteDatasource();
        $this->addBoaDatasource($routeDatasource);
        $route->addRouteDatasource($routeDatasource);

        $form = $this->createForm(
            new RouteCreateType(),
            $route,
            array(
                'action' => $this->generateUrl(
                    'tisseo_boa_route_create',
                    array('lineVersionId' => $lineVersionId)
                ),
                'em' => $this->getDoctrine()->getManager($this->container->getParameter('endiv_database_connection'))
            )
        );

        $form->handleRequest($request);
        if ($form->isValid())
        {
            $route = $form->getData();

            try
            {
                $this->get('tisseo_endiv.route_manager')->save($route);
                $this->addFlash('success', 'route.message.created');
                return $this->redirectToRoute(
                    'tisseo_boa_route_edit',
                    array('routeId' => $route->getId())
                );
            }
            catch(\Exception $e)
            {
                $this->addFlashException($e->getMessage());
                return $this->redirectToRoute(
                    'tisseo_boa_line_version_list',
                    array('lineVersionId' => $lineVersionId)
                );
            }
        }

        return $this->render('TisseoBoaBundle:Route:create.html.twig',
            array(
                'title' => 'tisseo.boa.route.title.create',
                'form' => $form->createView(),
                'route' => $route
            )
        );
    }

    /**
     * Edit
     * @param integer $routeId
     *
     * Editing Route
     */
    public function editAction(Request $request, $routeId)
    {
        $this->isGranted('BUSINESS_MANAGE_ROUTES');

        $routeManager = $this->get('tisseo_endiv.route_manager');
        $route = $routeManager->find($routeId);

        $form = $this->createForm(
            new RouteEditType(),
            $route,
            array(
                'action'=> $this->generateUrl(
                    'tisseo_boa_route_edit',
                    array('routeId' => $routeId)
                )
            )
        );

        $form->handleRequest($request);
        if ($form->isValid())
        {
            $route = $form->getData();

            try
            {
                $routeManager->save($route);
                $this->addFlash('success', 'tisseo.flash.success.created');
            }
            catch(\Exception $e)
            {
                $this->addFlashException($e->getMessage());
            }

            return $this->redirectToRoute(
                'tisseo_boa_route_edit',
                array('routeId' => $routeId)
            );
        }

        return $this->render(
            'TisseoBoaBundle:Route:edit.html.twig',
            array(
                'pageTitle' => 'tisseo.boa.route.title.edit',
                'form' => $form->createView(),
                'route' => $route
            )
        );
    }

    /**
     * Delete
     * @param integer $routeId
     */
    public function deleteAction($routeId)
    {
        $this->isGranted('BUSINESS_MANAGE_ROUTES');

        $routeManager = $this->get('tisseo_endiv.route_manager');
        try
        {
            $lineVersionId = $routeManager->remove($routeId);
            $this->addFlash('success', 'route.message.removed');
        }
        catch(\Exception $e)
        {
            $lineVersionId = $routeManager->find($routeId)->getLineVersion()->getId();
            $this->addFlashException($e->getMessage());
        }

        return $this->redirectToRoute(
            'tisseo_boa_route_list',
            array('lineVersionId' => $lineVersionId)
        );
    }

    /**
     * TripCalendar
     * @param integer $lineVersionId
     *
     * Editing Trips in order to link them to TripCalendar
     */
    public function tripCalendarAction(Request $request, $lineVersionId)
    {
        $this->isGranted('BUSINESS_MANAGE_ROUTES');

        $routeManager = $this->get('tisseo_endiv.route_manager');
        $lineVersion = $this->get('tisseo_endiv.line_version_manager')->find($lineVersionId);

        if ($request->getMethod() === 'POST')
        {
            $datas = $request->request->get('route');

            try
            {
                $routeManager->linkTripCalendars($datas);
                $this->addFlash('success', 'tisseo.flash.success.edited');
            }
            catch(\Exception $e)
            {
                $this->addFlashException($e->getMessage());
            }

            return $this->redirectToRoute(
                'tisseo_boa_route_trip_calendar',
                array('lineVersionId' => $lineVersionId)
            );
        }

        return $this->render('TisseoBoaBundle:Route:tripCalendar.html.twig',
            array(
                'pageTitle' => 'tisseo.boa.route.title.link_trip_calendar',
                'lineVersion' => $lineVersion,
                'calendars' => $routeManager->getTimetableCalendars($lineVersionId),
                'calendarTypes' => $routeManager->getSortedTypesOfGridMaskType(),
                'calendarPeriods' => $routeManager->getSortedPeriodsOfGridMaskType()
            )
        );
    }

    /**
     * Duplicate
     * @param integer $routeId
     */
    public function duplicateAction(Request $request, $routeId)
    {
        $this->isGranted('BUSINESS_MANAGE_ROUTES');

        $routeManager = $this->get('tisseo_endiv.route_manager');
        $route = $routeManager->find($routeId);

        $form = $this->createForm(
            new RouteDuplicateType(),
            $route,
            array(
                'action' => $this->generateUrl(
                    'tisseo_boa_route_duplicate',
                    array('routeId' => $routeId)
                )
            )
        );

        $form->handleRequest($request);
        if ($form->isValid())
        {
            try
            {
                // TODO: check duplicate function in RouteManager and 'line_version' parameter from view
                $userName = $this->get('security.context')->getToken()->getUser()->getUsername();
                $lineVersionId = $request->request->get('line_version');
                $lineVersion = $this->get('tisseo_endiv.line_version_manager')->find($lineVersionId);
                $routeManager->duplicate($route, $lineVersion, $userName);
                $this->addFlash('success', 'route.message.duplicated');
            }
            catch(\Exception $e)
            {
                $this->addFlashException($e->getMessage());
            }

            return $this->redirectToRoute(
                'tisseo_boa_route_list',
                array('lineVersionId' => $route->getLineVersion()->getId())
            );
        }

        return $this->render('TisseoBoaBundle:Route:duplicate.html.twig',
            array(
                'title' => 'route.duplicate',
                'form' => $form->createView(),
                'activeLineVersions' => $route->getLineVersion()->getLine()->getActiveLineVersions()
            )
        );
    }
}
