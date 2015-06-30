<?php

namespace Tisseo\BoaBundle\Controller;

use Tisseo\EndivBundle\Entity\Trip;
use Tisseo\EndivBundle\Entity\TripDatasource;
use Tisseo\BoaBundle\Form\Type\TripType;
use Tisseo\BoaBundle\Form\Type\NewTripType;

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
        $route = $routeManager->findById($routeId);
        $trips = $route->getTrips()->filter(
                    function($t) {
                        return $t->getIsPattern() == false;
                    }
        );
        $tripBounds = $tripManager->getDateBounds($route);

        return $this->render(
            'TisseoBoaBundle:Trip:list.html.twig',
            array(
                'route' => $routeManager->findById($routeId),
                'trips' => $trips,
                'tripBounds' => $tripBounds
            )
        );
    }

    public function editAction($tripId)
    {
        $this->isGranted('BUSINESS_MANAGE_ROUTES');
        $request = $this->getRequest();

        $tripManager = $this->get('tisseo_endiv.trip_manager');
        $trip = $tripManager->find($tripId);
        $stopTimes = $tripManager->getStopTimes($tripId);

        $form = $this->createForm(
            new TripType(),
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
            try {
                $trip = $form->getData();
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

        return $this->render(
            'TisseoBoaBundle:Trip:form.html.twig',
            array(
                'form' => $form->createView(),
                'trip' => $trip,
                'stopTimes' => $stopTimes
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


    public function newAction($routeId)
    {
        $this->isGranted('BUSINESS_MANAGE_ROUTES');
        $request = $this->getRequest();

        $routeManager = $this->get('tisseo_endiv.route_manager');
        $route = $routeManager->findById($routeId);

        $trip = new Trip();
        $trip->setRoute($route);
        $lineVersion = $route->getLineVersion();
        $trip->setName($lineVersion->getLine()->getNumber()."_".$lineVersion->getVersion()."_".$route->getWay()[0]);

        $user = $this->get('security.token_storage')->getToken()->getUser()->getUsername();

        $datasourceManager = $this->get('tisseo_endiv.datasource_manager');

        // TODO: Change this ugly way to get back a specific datasource 
        $datasource = $datasourceManager->findByName('Service Données');
        if (count($datasource) === 1)
            $datasource = $datasource[0];
        else
            $datasource = null;

        $form = $this->createForm(
            new NewTripType($user, $datasource),
            $trip,
            array(
                "action"=>$this->generateUrl(
                    'tisseo_boa_trip_create',
                    array("routeId" => $routeId)
                )
            )
        );

        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $newTrip = $form->getData();
                $datasource = array();
                $datasource['datasource'] = $form->get('datasource')->getData();
                $datasource['code'] = $form->get('code')->getData();

                $stopTimes = $request->request->get('stop_times');
                $tripManager = $this->get('tisseo_endiv.trip_manager');
                $tripManager->createTripAndStopTimes($newTrip, $stopTimes, $route, $datasource, false);
            } catch(\Exception $e) {
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }

            return $this->redirect(
                $this->generateUrl('tisseo_boa_trip_list',
                    array("routeId" => $routeId)
                )
            );
        }

        return $this->render(
            'TisseoBoaBundle:Trip:new.html.twig',
            array(
                'title' => 'trip.create',
                'form' => $form->createView(),
                'trip' => $trip
            )
        );
    }
}
