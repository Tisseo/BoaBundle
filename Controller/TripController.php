<?php
/**
 * Created by PhpStorm.
 * User: clesauln
 * Date: 29/04/2015
 * Time: 09:15
 */

namespace Tisseo\BoaBundle\Controller;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Tisseo\EndivBundle\Entity\Trip;

class TripController extends AbstractController {

    public function deleteTripAction(){

        $request = $this->get('request');

        $TripManager = $this->get('tisseo_endiv.trip_manager');



        if($request->isMethod('POST')){

            $tripName = $request->request->get('trip');

            $trip = $TripManager->findByName($tripName);


            $idTrip = $trip->getId();

            $trips = $TripManager->hasTrips($idTrip);

            //if trip has trips..//

            if($trips  == true){

                $response = "Services found, copy result";


            }
            else {
                $response = "no Services";
                $TripManager->deleteTrip($trip);
            }
            return new Response($response,200);

        }
    }


}