<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\CoreBundle\Controller\CoreController;

class AccessibilityMonitoringController extends CoreController
{
    /**
     * Accessibility
     *
     * monitoring the accessibility of a lineVersion
     */
    public function searchAction($lineVersionId, $startDate)
    {
        $this->isGranted(
            array(
                'BUSINESS_VIEW_MONITORING'
            )
        );
        $lineVersionOptions = $this->get('tisseo_endiv.line_version_manager')->findAllSortedByLineNumber();

        $lineVersion = empty($lineVersionId) ? null : $this->get('tisseo_endiv.line_version_manager')->find($lineVersionId);
        $startDate = ($startDate == 0) ? null : \DateTime::createFromFormat('d-m-Y', $startDate);

        $stopAccessibilitiesByRoute = (empty($lineVersion) or empty($startDate)) ? null
            : $this->get('tisseo_endiv.line_version_manager')->getStopAccessibilityChangesByRoute($lineVersion, $startDate);

        return $this->render(
            'TisseoBoaBundle:Monitoring:accessibility_search.html.twig',
            array(
                'navTitle' => 'tisseo.boa.menu.monitoring.manage',
                'pageTitle' => 'tisseo.boa.monitoring.accessibility.title',
                'lineVersionOptions' => $lineVersionOptions,
                'lineVersion' => $lineVersion,
                'startDate' => $startDate,
                'stopAccessibilitiesByRoute' => $stopAccessibilitiesByRoute
            )
        );
    }
}
