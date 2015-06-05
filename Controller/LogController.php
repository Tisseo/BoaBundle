<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\EndivBundle\Entity\Log;
use Tisseo\BoaBundle\Form\Type\LogType;

class LogController extends AbstractController
{
    public function listAction()
    {
        $this->isGranted('BUSINESS_MANAGE_CONFIGURATION');
		
		$LogManager = $this->get('tisseo_endiv.log_manager');
        return $this->render(
            'TisseoBoaBundle:Log:list.html.twig',
            array(
                'pageTitle' => 'menu.log',
                'logs' => $LogManager->findAll()
            )
        );
    }
}
