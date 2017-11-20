<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\CoreBundle\Controller\CoreController;

class OfferMonitoringController extends CoreController
{

    public function searchAction()
    {
        $this->denyAccessUnlessGranted('BUSINESS_VIEW_MONITORING');

        /*$lineVersionOptions = $this->get('tisseo_endiv.line_version_manager')->findAllSortedByLineNumber();

        $lineVersion = empty($lineVersionId) ? null : $this->get('tisseo_endiv.line_version_manager')->find($lineVersionId);

        $poiByStopArea = (empty($lineVersion)) ? null
            : $this->get('tisseo_endiv.line_version_manager')->getPoiByStopArea($lineVersion);*/

        return $this->render(
            'TisseoBoaBundle:Monitoring:offer_search.html.twig',
            [
                'navTitle' => 'tisseo.boa.menu.monitoring.manage',
                'pageTitle' => 'tisseo.boa.monitoring.offer_by_line.title',
            ]
        );
    }
}
