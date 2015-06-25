<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\EndivBundle\Entity\LineVersion;

class LineVersionController extends AbstractController
{
    /**
     * List
     *
     * Display the list view of all LineVersion.
     */
    public function listAction()
    {
        $this->isGranted(
            array(
                'BUSINESS_MANAGE_ROUTES',
                'BUSINESS_VIEW_ROUTES'
            )
        );

        $lineVersionManager = $this->get('tisseo_endiv.line_version_manager');
        $datasourceManager = $this->get('tisseo_endiv.datasource_manager');
        $now = new \Datetime();

        return $this->render(
            'TisseoBoaBundle:LineVersion:list.html.twig',
            array(
                'pageTitle' => 'menu.route_manage',
                'lineVersions' => $lineVersionManager->findActiveLineVersions($now),
                'datasources' => $datasourceManager->findAll()
            )
        );
    }
}
