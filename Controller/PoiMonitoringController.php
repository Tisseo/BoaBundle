<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\CoreBundle\Controller\CoreController;

class PoiMonitoringController extends CoreController
{
    /**
     * Poi
     *
     * monitoring the POIs associated to the stops of a lineVersion
     */
    public function searchAction($lineVersionId)
    {
        $this->denyAccessUnlessGranted('BUSINESS_VIEW_MONITORING');

        $lineVersionOptions = $this->get('tisseo_endiv.manager.line_version')->findAllSortedByLineNumber();

        $lineVersion = empty($lineVersionId) ? null : $this->get('tisseo_endiv.manager.line_version')->find($lineVersionId);

        $poiByStopArea = (empty($lineVersion)) ? null
            : $this->get('tisseo_endiv.manager.line_version')->getPoiByStopArea($lineVersion);

        return $this->render(
            'TisseoBoaBundle:Monitoring:poi_search.html.twig',
            array(
                'navTitle' => 'tisseo.boa.menu.monitoring.manage',
                'pageTitle' => 'tisseo.boa.monitoring.poi.title',
                'lineVersionOptions' => $lineVersionOptions,
                'lineVersion' => $lineVersion,
                'poiByStopArea' => $poiByStopArea
            )
        );
    }
}
