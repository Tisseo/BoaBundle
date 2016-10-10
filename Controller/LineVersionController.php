<?php

namespace Tisseo\BoaBundle\Controller;

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
        $this->denyAccessUnlessGranted(array(
            'BUSINESS_MANAGE_ROUTES',
            'BUSINESS_VIEW_ROUTES'
        ));

        return $this->render(
            'TisseoBoaBundle:LineVersion:list.html.twig',
            array(
                'navTitle' => 'tisseo.boa.menu.transport.manage',
                'pageTitle' => 'tisseo.boa.line_version.title.list',
                'lineVersions' => $this->get('tisseo_endiv.manager.line_version')->findActiveLineVersions(false, true),
                'datasources' => $this->get('tisseo_endiv.manager.datasource')->findAll()
            )
        );
    }
}
