<?php

namespace Tisseo\BoaBundle\Controller;

use Tisseo\CoreBundle\Controller\CoreController;

class LogController extends CoreController
{
    /**
     * List
     *
     * Listing all Logs
     */
    public function listAction()
    {
        $this->isGranted(
            array(
                'BUSINESS_MANAGE_CONFIGURATION',
                'BUSINESS_VIEW_CONFIGURATION'
            )
        );

        return $this->render(
            'TisseoBoaBundle:Log:list.html.twig',
            array(
                'navTitle' => 'tisseo.boa.menu.configuration',
                'pageTitle' => 'tisseo.boa.log.title.list',
                'logs' => $this->get('tisseo_endiv.log_manager')->findAll()
            )
        );
    }
}
