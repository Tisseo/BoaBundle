<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\BoaBundle\Form\Type\CustomerType;

/*
 * CalendarController
 */
class CustomerController extends AbstractController
{
    public function listAction($externalNetworkId)
    {
        $this->isGranted('BUSINESS_MANAGE_CUSTOMER');
        $customerManager = $this->get('sam_core.customer');
        $customerApplications = $customerManager->findByCurrentApp();
        $customers = array();

        foreach ($customerApplications as $customerApplication) {
            $customer = $customerManager->find($customerApplication->getCustomer());
            $customers[] = $customer;
        }

        return $this->render(
            'TisseoBoaBundle:Customer:list.html.twig',
            array(
                'externalNetworkId' => $externalNetworkId,
                'customers' => $customers
            )
        );
    }
}
