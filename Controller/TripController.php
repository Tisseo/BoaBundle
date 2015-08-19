<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Tisseo\EndivBundle\Entity\Route;
use Tisseo\EndivBundle\Entity\Trip;
use Tisseo\EndivBundle\Entity\TripDatasource;
use Tisseo\BoaBundle\Form\Type\TripCreateType;
use Tisseo\BoaBundle\Form\Type\TripEditType;

class TripController extends AbstractController
{
    public function listAction($routeId)
    {
        $this->isGranted(
            array(
                'BUSINESS_MANAGE_ROUTES',
                'BUSINESS_VIEW_ROUTES'
            )
        );

        $tripManager = $this->get('tisseo_endiv.trip_manager');
        $routeManager = $this->get('tisseo_endiv.route_manager');

        $route = $routeManager->find($routeId);
        $tripBounds = $tripManager->getDateBounds($route);
        $yesterday = new \Datetime('-1 day');

        return $this->render(
            'TisseoBoaBundle:Trip:list.html.twig',
            array(
                'pageTitle' => 'menu.route_manage',
                'route' => $route,
                'tripBounds' => $tripBounds,
                'yesterday' => $yesterday
            )
        );
    }

    public function editAction(Request $request, $tripId)
    {
        $this->isGranted('BUSINESS_MANAGE_ROUTES');

        $tripManager = $this->get('tisseo_endiv.trip_manager');
        $trip = $tripManager->find($tripId);

        $form = $this->createForm(
            new TripEditType(),
            $trip,
            array(
                "action"=>$this->generateUrl(
                    'tisseo_boa_trip_edit',
                    array("tripId" => $tripId)
                )
            )
        );

        $form->handleRequest($request);
        if ($form->isValid())
        {
            $trip = $form->getData();

            try {
                $tripManager->save($trip);
            } catch(\Exception $e) {
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }

            return $this->redirect(
                $this->generateUrl(
                    'tisseo_boa_trip_list',
                    array("routeId" => $trip->getRoute()->getId())
                )
            );
        }

        $startDate = new \Datetime('first day of last month');
        $endDate = new \Datetime('last day of next month');

        return $this->render(
            'TisseoBoaBundle:Trip:edit.html.twig',
            array(
                'pageTitle' => 'menu.route_manage',
                'title' => 'trip.edit',
                'form' => $form->createView(),
                'trip' => $trip,
                'startDate' => $startDate,
                'endDate' => $endDate
            )
        );
    }

    public function deleteAction($tripId)
    {
        $this->isGranted('BUSINESS_MANAGE_ROUTES');

        $tripManager = $this->get('tisseo_endiv.trip_manager');
        $trip = $tripManager->find($tripId);
        try {
            $tripManager->remove($trip);
        } catch(\Exception $e) {
            $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
        }

        return $this->redirect(
            $this->generateUrl(
                'tisseo_boa_trip_list',
                array("routeId" => $trip->getRoute()->getId())
            )
        );
    }


    public function createAction(Request $request, $routeId)
    {
        $this->isGranted('BUSINESS_MANAGE_ROUTES');

        $route = $this->get('tisseo_endiv.route_manager')->find($routeId);
        $lineVersion = $route->getLineVersion();

        $trip = new Trip();
        $tripDatasource = new TripDatasource();
        $this->buildDefaultDatasource($tripDatasource);

        $trip->setRoute($route);
        $trip->setName($lineVersion->getLine()->getNumber()."_".$lineVersion->getVersion()."_".$route->getWay()[0]);
        $trip->addTripDatasource($tripDatasource);

        $form = $this->createForm(
            new TripCreateType(),
            $trip,
            array(
                "action"=>$this->generateUrl(
                    'tisseo_boa_trip_create',
                    array("routeId" => $routeId)
                ),
                'em' => $this->getDoctrine()->getManager($this->container->getParameter('endiv_database_connection'))
            )
        );

        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $newTrip = $form->getData();
                $stopTimes = $request->request->get('stop_times');
                $this->get('tisseo_endiv.trip_manager')->createTripAndStopTimes($newTrip, $stopTimes);
                $this->get('session')->getFlashBag()->add('success', 'trip.created');
            } catch(\Exception $e) {
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }

            return $this->redirect(
                $this->generateUrl(
                    $request->headers->get('referer')
                )
            );
        }

        return $this->render(
            'TisseoBoaBundle:Trip:create.html.twig',
            array(
                'title' => 'trip.create',
                'form' => $form->createView(),
                'trip' => $trip
            )
        );
    }

    public function editPatternAction(Request $request, $routeId)
    {
        $this->isGranted('BUSINESS_MANAGE_ROUTES');

        $routeManager = $this->get('tisseo_endiv.route_manager');
        $route = $routeManager->find($routeId);

        if ($request->isXmlHttpRequest() && $request->getMethod() === 'POST')
        {
            $tripPatterns = json_decode($request->getContent(), true);
            $tripDatasource = new TripDatasource();
            $this->buildDefaultDatasource($tripDatasource);

            try {
                $this->get('tisseo_endiv.trip_manager')->updateTripPatterns($tripPatterns, $route, $tripDatasource);
                $this->get('session')->getFlashBag()->add('success', 'trip.pattern.edited');
                return $this->redirect(
                    $this->generateUrl(
                        'tisseo_boa_route_edit',
                        array('routeId' => $routeId)
                    )
                );
            } catch (\Exception $e) {
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
