<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\CoreBundle\Controller\CoreController;

class JsonController extends CoreController
{
    public function StopAction(Request $request)
    {
        $this->denyAccessUnlessGranted(array(
            'BUSINESS_VIEW_STOPS',
            'BUSINESS_MANAGE_STOPS'
        ));

        $this->isAjax($request, Request::METHOD_POST);

        $term = $request->request->get('term');
        $data = $this->get('tisseo_endiv.stop_manager')->findStopsLike($term, null, true);

        return $this->prepareJsonResponse($data);
    }

    public function StopAreaAction(Request $request)
    {
        $this->denyAccessUnlessGranted(array(
            'BUSINESS_VIEW_STOPS',
            'BUSINESS_MANAGE_STOPS'
        ));

        $this->isAjax($request, Request::METHOD_POST);

        $term = $request->request->get('term');
        $data = $this->get('tisseo_endiv.stop_area_manager')->findStopAreasLike($term);

        return $this->prepareJsonResponse($data);
    }

    public function StopAndStopAreaAction(Request $request)
    {
        $this->denyAccessUnlessGranted(array(
            'BUSINESS_VIEW_STOPS',
            'BUSINESS_MANAGE_STOPS'
        ));

        $this->isAjax($request, Request::METHOD_POST);

        $term = $request->request->get('term');
        $stopData = $this->get('tisseo_endiv.stop_manager')->findStopsLike($term, null, true);
        foreach ($stopData as $key => $stopItem) {
            $stopData[$key]['type'] = 'sp';
        }

        $stopAreaData = $this->get('tisseo_endiv.stop_area_manager')->findStopAreasLike($term);
        foreach ($stopAreaData as $key => $stopItem) {
            $stopAreaData[$key]['type'] = 'sa';
        }
        $data = array_merge($stopAreaData, $stopData);

        return $this->prepareJsonResponse($data);
    }

    public function StopAndOdtAreaAction(Request $request)
    {
        $this->denyAccessUnlessGranted(array(
            'BUSINESS_VIEW_STOPS',
            'BUSINESS_MANAGE_STOPS'
        ));

        $this->isAjax($request, Request::METHOD_POST);

        $term = $request->request->get('term');
        $stops = $this->get('tisseo_endiv.stop_manager')->findStopsLike($term, null, true);
        foreach ($stops as $key => $item) {
            $stops[$key]['type'] = 'sp';
        }

        $odtAreas = $this->get('tisseo_endiv.odt_area_manager')->findOdtAreasLike($term);
        foreach ($odtAreas as $key => $item) {
            $odtAreas[$key]['type'] = 'oa';
        }
        $data = array_merge($odtAreas, $stops);

        return $this->prepareJsonResponse($data);
    }

    public function CityAction(Request $request)
    {
        $this->denyAccessUnlessGranted(array(
            'BUSINESS_VIEW_STOPS',
            'BUSINESS_MANAGE_STOPS'
        ));

        $this->isAjax($request, Request::METHOD_POST);

        $term = $request->request->get('term');
        $data = $this->get('tisseo_endiv.city_manager')->findCityLike($term);

        return $this->prepareJsonResponse($data);
    }

    public function StopTransferAction(Request $request, $stopAreaId)
    {
        $this->denyAccessUnlessGranted(array(
            'BUSINESS_VIEW_STOPS',
            'BUSINESS_MANAGE_STOPS'
        ));

        $this->isAjax($request, Request::METHOD_POST);

        $term = $request->request->get('term');
        $result = array();
        $data = $this->get('tisseo_endiv.stop_area_manager')->findStopAreasLike($term, $stopAreaId);
        foreach ($data as $item) {
            $result[] = array(
                'id' => $item['id'],
                'name' => $item['name'],
                'type' => 'sa'
            );
        }
        $data = $this->get('tisseo_endiv.stop_manager')->findStopsLike($term, $stopAreaId, true);
        foreach ($data as $item) {
            $result[] = array(
                'id' => $item['id'],
                'name' => $item['name'],
                'type' => 'sp'
            );
        }

        return $this->prepareJsonResponse($result);
    }

    public function TripTemplateAction(Request $request)
    {
        $this->denyAccessUnlessGranted(array(
            'BUSINESS_VIEW_ROUTES',
            'BUSINESS_MANAGE_ROUTES'
        ));

        $this->isAjax($request, Request::METHOD_POST);

        $data = $this->get('tisseo_endiv.trip_manager')->getTripTemplates(
            $request->request->get('term'),
            $request->request->get('routeId')
        );

        return $this->prepareJsonResponse($data);
    }
}
