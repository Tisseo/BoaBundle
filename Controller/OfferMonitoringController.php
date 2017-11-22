<?php

namespace Tisseo\BoaBundle\Controller;

use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Tisseo\CoreBundle\Controller\CoreController;

class OfferMonitoringController extends CoreController
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction(Request $request)
    {
        $this->denyAccessUnlessGranted('BUSINESS_VIEW_MONITORING');

        $lvm = $this->get('tisseo_endiv.line_version_manager');
        $data = $request->request->get('boa_offer_by_line_type');

        $form = $this->createForm(
            'boa_offer_by_line_type',
            $data
        );

        $form->handleRequest($request);
        if ($form->isValid()) {
            $data = $form->getData();
            $result = $lvm->findLineVersionSortedByLineNumber($data['month']);
        }

        return $this->render(
            'TisseoBoaBundle:Monitoring:offer_search.html.twig',
            [
                'navTitle' => 'tisseo.boa.menu.monitoring.manage',
                'pageTitle' => 'tisseo.boa.monitoring.offer_by_line.title',
                'form' => $form->createView(),
                'result' => isset($result) ? $result : null
            ]
        );
    }

    /**
     * Search LineVersion who are active for the specified date
     *
     * @param $month
     * @param year
     *
     * @return Response JSON
     */
    public function searchLineVersionAction($month, $year)
    {
        $response = new Response();
        $lvm = $this->get('tisseo_endiv.line_version_manager');
        $serializer = $this->get('jms_serializer');
        $date = new \DateTime();
        $date->setDate($year, $month, 1);

        $result = $lvm->findLineVersionSortedByLineNumber($date);

        $response->setContent(
            $serializer->serialize(
                $result,
                'json',
                SerializationContext::create()->setGroups(array('monitoring'))
            )
        );

        return $response;
    }
}
