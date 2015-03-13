<?php

namespace Tisseo\BOABundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends AbstractController
{
    public function indexAction($externalNetworkId = null)
    {
        return $this->render('TisseoBOABundle:Default:index.html.twig');
    }
}
