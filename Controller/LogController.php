<?php

namespace Tisseo\BOABundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\EndivBundle\Entity\Log;
use Tisseo\BOABundle\Form\Type\LogType;

class LogController extends AbstractController
{
    public function listAction()
    {
        $this->isGranted('BUSINESS_MANAGE_PARAMETERS');
		
		$LogManager = $this->get('tisseo_endiv.log_manager');
        return $this->render(
            'TisseoBOABundle:Log:list.html.twig',
            array(
                'pageTitle' => 'menu.log',
                'logs' => $LogManager->findAll()
            )
        );
    }
}
