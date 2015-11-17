<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Tisseo\CoreBundle\Controller\CoreController;

class JsonController extends CoreController
{
    private function sendJsonResponse($data)
    {
        $response = new JsonResponse();
        $response->setData($data);

        return $response;
    }

    public function CalendarsAction(Request $request, $calendarType = null)
    {
        $this->isGranted(
            array(
                'BUSINESS_VIEW_CALENDARS',
                'BUSINESS_MANAGE_CALENDARS'
            )
        );

        $this->isPostAjax($request);

        if (strpos($calendarType, ','))
            $calendarType = explode(',', $calendarType);

        $term = $request->request->get('term');
        $data = $this->get('tisseo_endiv.calendar_manager')->findCalendarsLike($term, $calendarType);

        return $this->sendJsonResponse($data);
    }

    public function StopAction(Request $request)
    {
        $this->isGranted(
            array(
                'BUSINESS_VIEW_STOPS',
                'BUSINESS_MANAGE_STOPS'
            )
        );

        $this->isPostAjax($request);

        $term = $request->request->get('term');
        $data = $this->get('tisseo_endiv.stop_manager')->findStopsLike($term);

        return $this->sendJsonResponse($data);
    }

    public function StopAreaAction(Request $request)
    {
        $this->isGranted(
            array(
                'BUSINESS_VIEW_STOPS',
                'BUSINESS_MANAGE_STOPS'
            )
        );

        $this->isPostAjax($request);

        $term = $request->request->get('term');
        $data = $this->get('tisseo_endiv.stop_area_manager')->findStopAreasLike($term);
        return $this->sendJsonResponse($data);
    }

    public function StopAndStopAreaAction(Request $request)
    {
        $this->isGranted(
            array(
                'BUSINESS_VIEW_STOPS',
                'BUSINESS_MANAGE_STOPS'
            )
        );

        $this->isPostAjax($request);

        $term = $request->request->get('term');
        $stopData = $this->get('tisseo_endiv.stop_manager')->findStopsLike($term);
        foreach ($stopData as $key => $stopItem){
            $stopData[$key]['type'] = 'sp';
        }

        $stopAreaData = $this->get('tisseo_endiv.stop_area_manager')->findStopAreasLike($term);
        foreach ($stopAreaData as $key => $stopItem){
            $stopAreaData[$key]['type'] = 'sa';
        }
        $data = array_merge($stopAreaData, $stopData);
        return $this->sendJsonResponse($data);
    }

    public function CityAction(Request $request)
    {
        $this->isGranted(
            array(
                'BUSINESS_VIEW_STOPS',
                'BUSINESS_MANAGE_STOPS'
            )
        );

        $this->isPostAjax($request);

        $term = $request->request->get('term');
        $data = $this->get('tisseo_endiv.city_manager')->findCityLike($term);

        return $this->sendJsonResponse($data);
    }

    public function InternalTransferAction(Request $request)
    {
        $this->isGranted(
            array(
                'BUSINESS_VIEW_STOPS',
                'BUSINESS_MANAGE_STOPS'
            )
        );

        $this->isPostAjax($request);

        $stopArea = $this->get('tisseo_endiv.stop_area_manager')->find($request->request->get('stopAreaId'));
        $data = $this->get('tisseo_endiv.transfer_manager')->getInternalTransfer($stopArea);

        return $this->sendJsonResponse($data);
    }

    public function ExternalTransferAction(Request $request)
    {
        $this->isGranted(
            array(
                'BUSINESS_VIEW_STOPS',
                'BUSINESS_MANAGE_STOPS'
            )
        );

        $this->isPostAjax($request);

        $stopArea = $this->get('tisseo_endiv.stop_area_manager')->find($request->request->get('stopAreaId'));
        $data = $this->get('tisseo_endiv.transfer_manager')->getExternalTransfer($stopArea);

        return $this->sendJsonResponse($data);
    }

    public function StopTransferAction(Request $request)
    {
        $this->isGranted(
            array(
                'BUSINESS_VIEW_STOPS',
                'BUSINESS_MANAGE_STOPS'
            )
        );

        $this->isPostAjax($request);

        $term = $request->request->get('term');
        $result = array();
        $data = $this->get('tisseo_endiv.stop_manager')->findStopsLike($term);
        foreach ($data as $item)
        {
            $result[] = array(
                "id" => $item["id"],
                "name" => $item["name"],
                "category" => "stop"
            );
        }
        $data = $this->get('tisseo_endiv.stop_area_manager')->findStopAreasLike($term);
        foreach ($data as $item)
        {
            $result[] = array(
                "id" => $item["id"],
                "name" => $item["name"],
                "category" => "stop_area"
            );
        }

        return $this->sendJsonResponse($result);
    }

    public function TripTemplateAction(Request $request)
    {
        $this->isGranted(
            array(
                'BUSINESS_VIEW_ROUTES',
                'BUSINESS_MANAGE_ROUTES'
            )
        );

        $this->isPostAjax($request);

        $data = $this->get('tisseo_endiv.trip_manager')->getTripTemplates(
            $request->request->get('term'),
            $request->request->get('routeId')
        );

        return $this->sendJsonResponse($data);
    }
}
