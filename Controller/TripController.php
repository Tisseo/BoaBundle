<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tisseo\CoreBundle\Controller\CoreController;
use Tisseo\EndivBundle\Entity\Route;
use Tisseo\EndivBundle\Entity\Trip;
use Tisseo\EndivBundle\Entity\TripDatasource;
use Tisseo\BoaBundle\Form\Type\TripCreateType;
use Tisseo\BoaBundle\Form\Type\TripEditType;

class TripController extends CoreController
{
    /**
     * List
     * @param integer $routeId
     *
     * Listing all Trips of a specific Route
     */
    public function listAction($routeId)
    {
        $this->denyAccessUnlessGranted(array(
            'BUSINESS_MANAGE_ROUTES',
            'BUSINESS_VIEW_ROUTES'
        ));

        $route = $this->get('tisseo_endiv.route_manager')->find($routeId);
        $tripBounds = $this->get('tisseo_endiv.trip_manager')->getDateBounds($route);
        $yesterday = new \Datetime('-1 day');

        return $this->render(
            'TisseoBoaBundle:Trip:list.html.twig',
            array(
                'pageTitle' => 'tisseo.boa.trip.title.list',
                'route' => $route,
                'tripBounds' => $tripBounds,
                'yesterday' => $yesterday
            )
        );
    }

    /**
     * Create
     * @param integer $routeId
     *
     * Creating Trip in a specific Route
     */
    public function createAction(Request $request, $routeId)
    {
        $this->denyAccessUnlessGranted('BUSINESS_MANAGE_ROUTES');

        $route = $this->get('tisseo_endiv.route_manager')->find($routeId);
        $lineVersion = $route->getLineVersion();

        $trip = new Trip();
        $tripDatasource = new TripDatasource();
        $this->addBoaDatasource($tripDatasource);

        $trip->setRoute($route);
        $trip->setName($lineVersion->getLine()->getNumber()."_".$lineVersion->getVersion()."_".$route->getWay()[0]);
        $trip->addTripDatasource($tripDatasource);

        $form = $this->createForm(
            new TripCreateType(),
            $trip,
            array(
                "action" => $this->generateUrl(
                    'tisseo_boa_trip_create',
                    array("routeId" => $routeId)
                ),
                'em' => $this->getDoctrine()->getManager($this->container->getParameter('endiv_database_connection'))
            )
        );

        $form->handleRequest($request);
        if ($form->isValid())
        {
            try
            {
                $newTrip = $form->getData();
                $stopTimes = $request->request->get('stopTimes');
                $this->get('tisseo_endiv.trip_manager')->createTripAndStopTimes($newTrip, $stopTimes);
                $this->addFlash('success', 'tisseo.flash.success.created');
            }
            catch(\Exception $e)
            {
                $this->addFlashException($e->getMessage());
            }

            return $this->redirect($request->headers->get('referer'));
        }

        return $this->render(
            'TisseoBoaBundle:Trip:create.html.twig',
            array(
                'title' => 'tisseo.boa.trip.title.create',
                'form' => $form->createView(),
                'trip' => $trip
            )
        );
    }

    /**
     * Edit
     * @param integer $tripId
     *
     * Editing Trip
     */
    public function editAction(Request $request, $tripId)
    {
        $this->denyAccessUnlessGranted('BUSINESS_MANAGE_ROUTES');

        $tripManager = $this->get('tisseo_endiv.trip_manager');
        $trip = $tripManager->find($tripId);

        $form = $this->createForm(
            new TripEditType(),
            $trip,
            array(
                "action" => $this->generateUrl(
                    'tisseo_boa_trip_edit',
                    array("tripId" => $tripId)
                )
            )
        );

        $form->handleRequest($request);
        if ($form->isValid())
        {
            $trip = $form->getData();

            try
            {
                $tripManager->save($trip);
                $this->addFlash('success', 'tisseo.flash.success.edited');
            }
            catch(\Exception $e)
            {
                $this->addFlashException($e->getMessage());
            }

            return $this->redirectToRoute(
                'tisseo_boa_trip_list',
                array('routeId' => $trip->getRoute()->getId())
            );
        }

        $startDate = new \Datetime('first day of last month');
        $endDate = new \Datetime('last day of next month');

        return $this->render(
            'TisseoBoaBundle:Trip:edit.html.twig',
            array(
                'pageTitle' => 'tisseo.boa.trip.title.edit',
                'form' => $form->createView(),
                'trip' => $trip,
                'startDate' => $startDate,
                'endDate' => $endDate
            )
        );
    }

    /**
     * Delete
     * @param $tripId
     *
     * Deleting Trip
     */
    public function deleteAction($tripId)
    {
        $this->denyAccessUnlessGranted('BUSINESS_MANAGE_ROUTES');

        $tripManager = $this->get('tisseo_endiv.trip_manager');
        $trip = $tripManager->find($tripId);

        try
        {
            $tripManager->remove($trip);
            $this->addFlash('success', 'tisseo.flash.success.deleted');
        }
        catch(\Exception $e)
        {
            $this->addFlashException($e->getMessage());
        }

        return $this->redirectToRoute(
            'tisseo_boa_trip_list',
            array('routeId' => $trip->getRoute()->getId())
        );
    }

    /**
     * Delete all
     * @param integer $routeId
     *
     * Deleting all or selected Trips from a Route
     */
    public function deleteAllAction(Request $request, $routeId)
    {
        $this->denyAccessUnlessGranted('BUSINESS_MANAGE_ROUTES');

        $route = $this->get('tisseo_endiv.route_manager')->find($routeId);

        try
        {
            if ($request->getMethod() == 'POST') {
                $idTrips = json_decode($request->getContent(), true);
                $this->get('tisseo_endiv.trip_manager')->deleteTripsFromRoute($route, $idTrips);
                $this->addFlash('success', 'tisseo.boa.trip.message.select_deleted');
            } else {
                $this->get('tisseo_endiv.trip_manager')->deleteTripsFromRoute($route);
                $this->addFlash('success', 'tisseo.boa.trip.message.all_deleted');
            }
        }
        catch(\Exception $e)
        {
            $this->addFlashException($e->getMessage());
        }

        return $this->redirectToRoute(
            'tisseo_boa_trip_list',
            array('routeId' => $routeId)
        );
    }

    /**
     * Edit pattern
     * @param integer $routeId
     *
     * Editing Trip 'pattern' in a Route
     */
    public function editPatternAction(Request $request, $routeId)
    {
        $this->denyAccessUnlessGranted('BUSINESS_MANAGE_ROUTES');

        $route = $this->get('tisseo_endiv.route_manager')->find($routeId);

        if ($request->isXmlHttpRequest() && $request->getMethod() === 'POST')
        {
            $tripPatterns = json_decode($request->getContent(), true);
            $tripDatasource = new TripDatasource();
            $this->addBoaDatasource($tripDatasource);

            try
            {
                $this->get('tisseo_endiv.trip_manager')->updateTripPatterns($tripPatterns, $route, $tripDatasource);
                $this->addFlash('success', 'tisseo.flash.success.edited');

                return $this->redirectToRoute(
                    'tisseo_boa_route_edit',
                    array('routeId' => $routeId)
                );
            }
            catch (\Exception $e)
            {
                return new Response($e->getMessage());
            }
        }

        return $this->render(
            'TisseoBoaBundle:Trip:editPattern.html.twig',
            array(
                'route' => $route
            )
        );
    }
}
