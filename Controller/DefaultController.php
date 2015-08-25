<?php

namespace Tisseo\BoaBundle\Controller;

use Tisseo\CoreBundle\Controller\CoreController;

class DefaultController extends CoreController
{
    public function indexAction()
    {
        return $this->render(
            'TisseoCoreBundle::container.html.twig',
            array(
                'pageTitle' => 'tisseo.boa.welcome',
                'bundle' => 'TisseoBoaBundle'
            )
        );
    }
}
