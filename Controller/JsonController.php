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
    public function CalendarsAction($calendarType = null)
    {
        $request = $this->get('request');
        if( strpos($calendarType, ',') )
            $calendarType = explode( ',', $calendarType );

        if($request->isXmlHttpRequest())
        {
            $term = $request->request->get('term');
            $array= $this->get('tisseo_endiv.calendar_manager')
                ->findCalendarsLike($term, $calendarType);

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

    public function ServiceCalendarBitmaskAction()
    {
        $request = $this->get('request');

        if($request->isXmlHttpRequest())
        {
            $id1 = $request->request->get('id1');
            $id2 = $request->request->get('id2');
            $startDate = $request->request->get('startDate');
            $endDate = $request->request->get('endDate');

            $array= $this->get('tisseo_endiv.calendar_manager')
                ->getServiceCalendarsBitmask($id1, $id2, $startDate, $endDate);

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

    public function TripTemplateAction()
    {
        $request = $this->get('request');

        if($request->isXmlHttpRequest())
        {
            $term = $request->request->get('term');
            $routeId = $request->request->get('routeId');
            $results= $this->get('tisseo_endiv.trip_manager')
                ->getTripTemplates($term, $routeId);

            $response = new Response(json_encode($results));
            $response -> headers -> set('Content-Type', 'application/json');
            return $response;
        }
    }
}
