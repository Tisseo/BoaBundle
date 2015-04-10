<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\EndivBundle\Entity\Stop;
//use Tisseo\BoaBundle\Form\Type\StopAreaType;

class StopAreaController extends AbstractController
{
    public function searchAction()
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');
		
        return $this->render(
            'TisseoBoaBundle:StopArea:search.html.twig',
            array(
                'title' => 'stop_area.title'
            )
        );
    }	
}
