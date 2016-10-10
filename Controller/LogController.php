<?php

namespace Tisseo\BoaBundle\Controller;

use Tisseo\CoreBundle\Controller\CoreController;

class LogController extends CoreController
{
    /**
     * List
     * @param integer offset
     * @param integer limit
     *
     * Listing all Logs
     */
    public function listAction($offset = 0, $limit = 0)
    {
        $this->denyAccessUnlessGranted(array(
            'BUSINESS_MANAGE_CONFIGURATION',
            'BUSINESS_VIEW_CONFIGURATION'
        ));

        $logManager = $this->get('tisseo_endiv.manager.log');

        return $this->render(
            'TisseoBoaBundle:Log:list.html.twig',
            array(
                'navTitle' => 'tisseo.boa.menu.configuration',
                'pageTitle' => 'tisseo.boa.log.title.list',
                'logs' => $logManager->findBy(array(), array('id' => 'desc'), $limit, $offset),
                'max' => $logManager->count()
            )
        );
    }
}
