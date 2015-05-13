<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tisseo\EndivBundle\Entity\RouteStop;
use Tisseo\EndivBundle\Entity\Stop;
use Tisseo\EndivBundle\Entity\StopTime;
use Tisseo\EndivBundle\Entity\Waypoint;
use Doctrine\Common\Persistence\ObjectManager;

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

    public function addRouteStopAction(){

        $request = $this->get('request');
        $routeStopManager = $this->get('tisseo_endiv.routestop_manager');
        $results = [];

        $this->isGranted('BUSINESS_MANAGE_ROUTES');
        $StopId =null;


        if($request->isMethod('POST')) {

            
            $routeId = $request->request->get('routeId');
            $route = $this->getDoctrine()
                ->getRepository('Tisseo\EndivBundle\Entity\Route','endiv')
                ->find($routeId);

            $routeManager = $this->get('tisseo_endiv.route_manager');
            $routeStopManager = $this->get('tisseo_endiv.routestop_manager');
            $stoptimeManager = $this->get('tisseo_endiv.stoptime_manager');
            $stopId = $request->request->get('stopId');
            $toRemove = $request->request->get('toRemove');

            $name = $request->request->get('name');
            $descStop=$request->request->get('descStop');
            $upStop=$request->request->get('upStop');
            $rank=$request->request->get('rank')+1;

            $stoptimes = $request->request->get('stoptimes');
            $departures = $request->request->get('departures');
            $mode = $request->request->get('isZone');
            $isZone = false;
            $index = -1;

            if($routeManager->checkZoneStop($route) == true){
                $isZone = true;
            }

            if( !empty($stopId)) {
                $waypoint = $this->getDoctrine()
                             ->getRepository('Tisseo\EndivBundle\Entity\Waypoint', 'endiv')
                             ->find($stopId);

                $idWaypoint = $waypoint->getId();

                $routeStop = new RouteStop();
                $routeStop->setDropOff($descStop);
                $routeStop->setPickup($upStop);
                $routeStop->setRank($rank);
                $routeStop->setRoute($route);

                $routeStop->setWaypoint($waypoint);

                $routeStopManager->save($routeStop);

                 if($mode == "TAD") {
                        foreach($stoptimes as $val) {

                            foreach($val as $key=>$val){
                                $index++;
                                $trip = $this->getDoctrine()
                                    ->getRepository('Tisseo\EndivBundle\Entity\Trip', 'endiv')
                                    ->findOneBy(array("name"=>(string)$key));

                                $tripId = $trip->getId();
                                if($index%2 == 0){
                                    $time = new StopTime();
                                }
                                $val = explode(":",$val);

                                if(empty($val)){
                                    $val = 0;
                                }
                                else {
                                    $hours = intval($val[0]) * 3600;
                                    $minutes = intval($val[1]) * 60;

                                    $timeValue = $hours + $minutes;

                                }

                                $time->setDepartureTime($timeValue);
                                    if($index%2 != 0){
                                        $time->setArrivalTime($timeValue);
                                        $time->setTrip($trip);
                                        $time->setRouteStop($routeStop);
                                        $stoptimeManager->save($time);
                                    }
                                }
                            }
                        }

                else {
                  
                    
                    foreach($stoptimes as $stoptime=>$val) {
                                               
                             $index++;
                            foreach($val as $tripName=>$stoptime){

                                if($stoptime != ""){
                                        $trip = $this->getDoctrine()
                                                     ->getRepository('Tisseo\EndivBundle\Entity\Trip', 'endiv')
                                                     ->findOneBy(array("name"=>$tripName));


                                        $tripId = $trip->getId();
                                                   
                                        $time = new StopTime();

                                        $val = explode(":",$stoptime);

                                        if(empty($val)){
                                            $val = 0;
                                        }

                                        else{
                                                $hours = intval($val[0])*3600;

                                                if(sizeof($val)>1){
                                                    $minutes = intval($val[1])*60;
                                                }

                                                else {
                                                    $minutes = 0;
                                                }

                                        }

                                        $val = $hours+$minutes;

                                        if($isZone == false) {

                                            $arrivalTime = $departures[$index]["depart"] + $val;
                                            $time->setArrivalTime($arrivalTime);
                                        }


                                        $time->setArrivalTime($arrivalTime);
                                        $time->setTrip($trip);

                                        $time->setRouteStop($routeStop);
                                        $stoptimeManager->save($time);

                                   }
                                }           
                            }
                    }
                }
            
            if(isset($toRemove)){

                foreach ($toRemove as $routestop) {
                    # code...
                    $routeStopOld = $this->getDoctrine()
                                         ->getRepository('Tisseo\EndivBundle\Entity\RouteStop', 'endiv')
                                         ->find($routestop);

                    $routeStopManager->remove($routeStopOld);
                }
            }

           




        }
        $response = new Response(json_encode($results));
        $response -> headers -> set('Content-Type', 'application/json');
        return $response;
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
            $indexStop = 0;
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

                        $services = [];
                        $services['services'] = $trip->getName();

                        $stoptimes =  $routeStopManager->getStoptimes($trip->getId());

                        if(isset($stoptimes)){

                            $services["stoptimes"]=[];
                            $services["stoptimes"] = $stoptimes;
                        }

                        array_push($results,$services);


                }

                $type = [];
                $type['iszone'] = $isZone;

                array_push($results,$type);

                foreach($stops as $stop) {

                    $index++;

                    $idStop = $stop["id"];
                    $rank = $stop["rank"];
                    $stopPoints = [];

                    if($isZone == false){
                        $stops = $stopManager->getStopsByRoute($id);
                        $waypointId = $stop["waypoint"];
                        $stopAreas[] = $stopManager->getStops($waypointId);

                        $city = $stopAreas[$index][0]["city"];
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

                    $stopPoints["city"] = $city;
                    $stopPoints["id"] = $idStop;
                    $stopPoints["code"] = $code;
                    $stopPoints["rank"] = $rank;

                    array_push($results,$stopPoints);
                }
                $size= [];
                $size["size"] = $index;

                array_push($results,$size);
            }



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