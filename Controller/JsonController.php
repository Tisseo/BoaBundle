<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Tisseo\CoreBundle\Controller\CoreController;

class JsonController extends CoreController
{
    public function calendarsAction(Request $request, $calendarType = null)
    {
        $this->denyAccessUnlessGranted(array(
            'BUSINESS_VIEW_CALENDARS',
            'BUSINESS_MANAGE_CALENDARS'
        ));

        $this->isAjax($request, Request::METHOD_POST);

        if ($calendarType !== null) {
            if (strpos($calendarType, ',')) {
                $calendarType = explode(',', $calendarType);
            } else if ($calendarType !== null) {
                $calendarType = array($calendarType);
            }
        } else {
            $calendarType = array();
        }

        $term = $request->request->get('term');
        $lineVersionId = $request->query->get('line_version_id');

        $data = $this->get('tisseo_endiv.manager.calendar')->findCalendarsLike($term, $calendarType, $lineVersionId);

        if (empty($data)) {
            return $this->prepareJsonResponse(
                $this->get('translator')->trans('tisseo.global.no_data'),
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        return $this->prepareJsonResponse($data);
    }

    public function stopAction(Request $request)
    {

        $this->denyAccessUnlessGranted(array(
            'BUSINESS_VIEW_STOPS',
            'BUSINESS_MANAGE_STOPS'
        ));

        $this->isAjax($request, Request::METHOD_POST);

        $term = $request->request->get('term');
        $data = $this->get('tisseo_endiv.manager.stop')->findStopsLike($term, null, true);

        return $this->prepareJsonResponse($data);
    }

    public function stopAreaAction(Request $request)
    {
        $this->denyAccessUnlessGranted(array(
            'BUSINESS_VIEW_STOPS',
            'BUSINESS_MANAGE_STOPS'
        ));

        $this->isAjax($request, Request::METHOD_POST);

        $term = $request->request->get('term');
        $data = $this->get('tisseo_endiv.manager.stop_area')->findStopAreasLike($term);
        return $this->prepareJsonResponse($data);
    }

    public function stopAndStopAreaAction(Request $request)
    {
        $this->denyAccessUnlessGranted(array(
            'BUSINESS_VIEW_STOPS',
            'BUSINESS_MANAGE_STOPS'
        ));

        $this->isAjax($request, Request::METHOD_POST);

        $term = $request->request->get('term');
        $stopData = $this->get('tisseo_endiv.manager.stop')->findStopsLike($term, null, true);
        foreach ($stopData as $key => $stopItem){
            $stopData[$key]['type'] = 'sp';
        }

        $stopAreaData = $this->get('tisseo_endiv.manager.stop_area')->findStopAreasLike($term);
        foreach ($stopAreaData as $key => $stopItem){
            $stopAreaData[$key]['type'] = 'sa';
        }
        $data = array_merge($stopAreaData, $stopData);
        return $this->prepareJsonResponse($data);
    }

    public function stopAndOdtAreaAction(Request $request)
    {
        $this->denyAccessUnlessGranted(array(
            'BUSINESS_VIEW_STOPS',
            'BUSINESS_MANAGE_STOPS'
        ));

        $this->isAjax($request, Request::METHOD_POST);

        $term = $request->request->get('term');
        $stops = $this->get('tisseo_endiv.manager.stop')->findStopsLike($term, null, true);
        foreach ($stops as $key => $item){
            $stops[$key]['type'] = 'sp';
        }

        $odtAreas = $this->get('tisseo_endiv.manager.odt_area')->findOdtAreasLike($term);
        foreach ($odtAreas as $key => $item){
            $odtAreas[$key]['type'] = 'oa';
        }
        $data = array_merge($odtAreas, $stops);
        return $this->prepareJsonResponse($data);
    }

    public function cityAction(Request $request)
    {
        $this->denyAccessUnlessGranted(array(
            'BUSINESS_VIEW_STOPS',
            'BUSINESS_MANAGE_STOPS'
        ));

        $this->isAjax($request, Request::METHOD_POST);

        $term = $request->request->get('term');
        $data = $this->get('tisseo_endiv.manager.city')->findCityLike($term);

        return $this->prepareJsonResponse($data);
    }

    public function stopTransferAction(Request $request, $stopAreaId)
    {
        $this->denyAccessUnlessGranted(array(
            'BUSINESS_VIEW_STOPS',
            'BUSINESS_MANAGE_STOPS'
        ));

        $this->isAjax($request, Request::METHOD_POST);

        $term = $request->request->get('term');
        $result = array();
        $data = $this->get('tisseo_endiv.manager.stop_area')->findStopAreasLike($term, $stopAreaId);
        foreach ($data as $item)
        {
            $result[] = array(
                "id" => $item["id"],
                "name" => $item["name"],
                "type" => "sa"
            );
        }
        $data = $this->get('tisseo_endiv.manager.stop')->findStopsLike($term, $stopAreaId, true);
        foreach ($data as $item)
        {
            $result[] = array(
                "id" => $item["id"],
                "name" => $item["name"],
                "type" => "sp"
            );
        }

        return $this->prepareJsonResponse($result);
    }

    public function tripTemplateAction(Request $request)
    {
        $this->denyAccessUnlessGranted(array(
            'BUSINESS_VIEW_ROUTES',
            'BUSINESS_MANAGE_ROUTES'
        ));

        $this->isAjax($request, Request::METHOD_POST);

        $data = $this->get('tisseo_endiv.manager.trip')->getTripTemplates(
            $request->request->get('term'),
            $request->request->get('routeId')
        );

        return $this->prepareJsonResponse($data);
    }
}
