<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class JsonController extends AbstractController
{
    public function CalendarsAction($CalendarType = null)
    {
		$request = $this->get('request');
		
        if($request->isXmlHttpRequest())
        {
            $term = $request->request->get('term');
            $array= $this->get('tisseo_endiv.calendar_manager')
                ->findCalendarsLike($term, $CalendarType);
				
            $response = new Response(json_encode($array));
            $response -> headers -> set('Content-Type', 'application/json');
            return $response;
    	}
    }

    public function BitmaskAction()
    {
		
        $request = $this->get('request');
 
		if($request->isXmlHttpRequest())
        {
			$id = $request->request->get('id');
            $startDate = $request->request->get('startDate');
            $endDate = $request->request->get('endDate');
			
            $array= $this->get('tisseo_endiv.calendar_manager')
                ->getCalendarsBitmask($id, $startDate, $endDate);
				
            $response = new Response(json_encode($array));
            $response -> headers -> set('Content-Type', 'application/json');
            return $response;
    	}
    }
	
    public function StopAction()
    {
        $request = $this->get('request');
		
        if($request->isXmlHttpRequest())
        {
            $term = $request->request->get('term');
            $array= $this->get('tisseo_endiv.stop_manager')
                ->findStopsLike($term);

            $response = new Response(json_encode($array));
            $response -> headers -> set('Content-Type', 'application/json');
            return $response;
    	}
    }

    public function StopAreaAction()
    {
        $request = $this->get('request');
		
        if($request->isXmlHttpRequest())
        {
            $term = $request->request->get('term');
            $array= $this->get('tisseo_endiv.stop_area_manager')
                ->findStopAreasLike($term);
				
            $response = new Response(json_encode($array));
            $response -> headers -> set('Content-Type', 'application/json');
            return $response;
    	}
    }
	
    public function CityAction()
    {
		
        $request = $this->get('request');
 
		if($request->isXmlHttpRequest())
        {
			$term = $request->request->get('term');
			
            $array= $this->get('tisseo_endiv.city_manager')
                ->findCityLike($term);
				
            $response = new Response(json_encode($array));
            $response -> headers -> set('Content-Type', 'application/json');
            return $response;
    	}
    }
	
    public function InternalTransferAction()
    {
		
        $request = $this->get('request');
 
		if($request->isXmlHttpRequest())
        {
			$StopAreaManager = $this->get('tisseo_endiv.stop_area_manager');
			
            $stopAreaId = $request->request->get('stopAreaId');
			$stopArea = $StopAreaManager->find($stopAreaId);
			
            $array= $this->get('tisseo_endiv.transfer_manager')
                ->getInternalTransfer($stopArea);
				
            $response = new Response(json_encode($array));
            $response -> headers -> set('Content-Type', 'application/json');
            return $response;
    	}
    }
	
    public function ExternalTransferAction()
    {
		
        $request = $this->get('request');
 
		if($request->isXmlHttpRequest())
        {
			$StopAreaManager = $this->get('tisseo_endiv.stop_area_manager');
			
            $stopAreaId = $request->request->get('stopAreaId');
			$stopArea = $StopAreaManager->find($stopAreaId);
			
            $array= $this->get('tisseo_endiv.transfer_manager')
                ->getExternalTransfer($stopArea);
				
            $response = new Response(json_encode($array));
            $response -> headers -> set('Content-Type', 'application/json');
            return $response;
    	}
    }

    public function routeTableAction(){


        $request = $this->get('request');


        if($request->isXmlHttpRequest()){
            $results= 1;
            $stopManager = $this->get('tisseo_endiv.stop_manager');
            $routeStopManager = $this->get('tisseo_endiv.routestop_manager');
            $routeManager = $this->get('tisseo_endiv.route_manager');
            $id = $request->request->get('routeId');
            $stops = $stopManager->getStopsByRoute($id);
            $index = -1;
            $isZone = false;

            if(isset($id)) {
                $route = $routeManager->findById($id);

                $trips = $this->getDoctrine()
                    ->getRepository("Tisseo\EndivBundle\Entity\Trip", "endiv")
                    ->findBy(array("route" => $id));



            if($routeManager->checkZoneStop($route) == true){
                $isZone = true;
            }

                $results=array();
                foreach ($trips as $trip) {

                    if($trip->getIsPattern() == true){
                        $services = [];
                        $services['services'] = $trip->getName();
                        array_push($results,$services);

                    }

                }

                foreach($stops as $stop) {

                    $index++;
                    $rank = $stop["rank"];
                    $dropOff = $stop["dropOff"];
                    $pickup = $stop["pickup"];
                    $idStop = $stop["id"];

                    $stopPoints = [];

                    if($isZone == false){
                        $stops = $stopManager->getStopsByRoute($id);
                        $waypointId = $stop["waypoint"];
                        $stopAreas[] = $stopManager->getStops($waypointId);

                        $city = $stopAreas[$index][0]["city"];
                        $name = $stopAreas[$index][0]["shortName"];
                        $code = $stopAreas[$index][0]["code"];

                    }

                    else {

                        $zone = $this->getDoctrine()
                            ->getRepository("Tisseo\EndivBundle\Entity\OdtArea","endiv")
                            ->find($stop["waypoint"]);
                        $city = "";

                        $name = $zone->getName();
                        $code = "zone";
                    }

                    $stopPoints["rank"] = $rank;
                    $stopPoints["city"] = $city;
                    $stopPoints["name"] = $name;
                    $stopPoints["id"] = $idStop;
                    $stopPoints["code"] = $code;
                    $stopPoints["dropOff"] = $dropOff;
                    $stopPoints["pickup"] = $pickup;

                    array_push($results,$stopPoints);
                }



            }
                /**
            }**/


        }
        $response = new Response(json_encode($results));
        $response -> headers -> set('Content-Type', 'application/json');
        return $response;

       // 'trips' => $trips,
        //'isZone' => $isZone

    }
    public function StopTransferAction()
    {
		
        $request = $this->get('request');
 
		if($request->isXmlHttpRequest())
        {
            $term = $request->request->get('term');
			$array = array();
			$results = array();
			$array= $this->get('tisseo_endiv.stop_manager')
                ->findStopsLike($term);
			foreach($array as $item) {
				$results[] = array(
					"id" => $item["id"],
					"name" => $item["name"],
					"category" => "stop"
				);
			}
            $array= $this->get('tisseo_endiv.stop_area_manager')
                ->findStopAreasLike($term);
			foreach($array as $item) {
				$results[] = array(
					"id" => $item["id"],
					"name" => $item["name"],
					"category" => "stop_area"
				);
			}
			
            $response = new Response(json_encode($results));
            $response -> headers -> set('Content-Type', 'application/json');
            return $response;
    	}
    }
}