<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\EndivBundle\Entity\LineVersion;
use Tisseo\CoreBundle\Controller\CoreController;

class LineVersionController extends CoreController
{
    /**
     * List
     *
     * Listing all LineVersions
     */
    public function listAction()
    {
        $this->isGranted(
            array(
                'BUSINESS_MANAGE_ROUTES',
                'BUSINESS_VIEW_ROUTES'
            )
        );

        $now = new \Datetime();

        return $this->render(
            'TisseoBoaBundle:LineVersion:list.html.twig',
            array(
                'navTitle' => 'tisseo.boa.menu.transport.manage',
                'pageTitle' => 'tisseo.boa.line_version.title.list',
                'lineVersions' => $this->get('tisseo_endiv.line_version_manager')->findActiveLineVersions($now),
                'datasources' => $this->get('tisseo_endiv.datasource_manager')->findAll()
            )
        );
    }
}
