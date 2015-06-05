<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\EndivBundle\Entity\Trip;
use Tisseo\BoaBundle\Form\Type\TripType;
use Tisseo\BoaBundle\Form\Type\NewTripType;

class TripController extends AbstractController
{
    public function listAction($RouteId)
    {
        $this->isGranted('BUSINESS_MANAGE_ROUTES');
		
		$tripManager = $this->get('tisseo_endiv.trip_manager');
        $routeManager = $this->get('tisseo_endiv.route_manager');
        $route = $routeManager->findById($RouteId);
        $trips = $route->getTrips()->filter( function($t) {
                    return $t->getIsPattern() == false;
                });
        $tripBounds = $tripManager->getDateBounds($route);

        return $this->render(
            'TisseoBoaBundle:Trip:list.html.twig',
            array(
                'route' => $routeManager->findById($RouteId),
                'trips' => $trips,
                'tripBounds' => $tripBounds
            )
        );
    }

    public function editAction(Request $request, $TripId)
    {
        $this->isGranted('BUSINESS_MANAGE_ROUTES');
        
        $tripManager = $this->get('tisseo_endiv.trip_manager');
        $trip = $tripManager->find($TripId);
        $stopTimes = $tripManager->getStopTimes($TripId);

        $form = $this->createForm(new TripType(), $trip,
            array(
                "action"=>$this->generateUrl(
                    'tisseo_boa_trip_edit',
                    array("TripId" => $TripId)
                )
            )
        );

        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                $trip = $form->getData();
                $tripManager->save($trip);
                $RouteId = $trip->getRoute()->getId();
            } catch(\Exception $e) {
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }
            
            return $this->redirect(
                $this->generateUrl('tisseo_boa_trip_list', 
                    array("RouteId" => $RouteId)
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

    public function deleteAction(Request $request, $TripId)
    {
        $this->isGranted('BUSINESS_MANAGE_ROUTES');
        $tripManager = $this->get('tisseo_endiv.trip_manager');
        $trip = $tripManager->find($TripId);
        $RouteId = $trip->getRoute()->getId();
        try {
            $tripManager->remove($trip);
        } catch(\Exception $e) {
            $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
        }

        return $this->redirect(
            $this->generateUrl('tisseo_boa_trip_list', 
                array("RouteId" => $RouteId)
            )
        );
    }


    public function newAction(Request $request, $RouteId)
    {
        $this->isGranted('BUSINESS_MANAGE_ROUTES');

        $routeManager = $this->get('tisseo_endiv.route_manager');
        $route = $routeManager->findById($RouteId);
        $trip = new Trip();
        $trip->setRoute($route);

        $form = $this->createForm(new NewTripType(), $trip,
            array(
                "action"=>$this->generateUrl('tisseo_boa_trip_new',
                                             array("RouteId" => $RouteId)
                )
            )
        );

        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                $new_trip = $form->getData();
                $stop_times = $request->request->get('stop_times');
                $tripManager = $this->get('tisseo_endiv.trip_manager');
                $tripManager->createTripAndStopTimes($new_trip, $stop_times, $route, false);
            } catch(\Exception $e) {
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }
            
            return $this->redirect(
                $this->generateUrl('tisseo_boa_trip_list', 
                    array("RouteId" => $RouteId)
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
