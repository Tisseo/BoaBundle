<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends AbstractController
{
    public function indexAction($externalNetworkId = null)
    {
        return $this->render('TisseoBoaBundle:Default:index.html.twig');
    }
}
