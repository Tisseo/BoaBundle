<?php

namespace Tisseo\BOABundle\Controller;

use Doctrine\Common\Collections\Criteria;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends AbstractController
{
    public function indexAction($externalNetworkId = null)
    {
        $perimeterManager = $this->get('tisseo_boa.perimeter_manager');
        $userManager = $this->get('tisseo_boa.user_manager');

        // TODO: Put the current or default Network of User.
        $networks = $userManager->getNetworks();

        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('externalNetworkId', $externalNetworkId));

        $currentPerimeter =
            $externalNetworkId == null ?
            $networks->first() :
            $networks->matching($criteria)->first()
        ;

        return $this->render(
            'TisseoBOABundle:Default:index.html.twig',
            array(
                'externalNetworkId' => $currentPerimeter->getExternalNetworkId()
            )
        );
    }
}
