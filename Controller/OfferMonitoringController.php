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
            $monitoring = $this->get('tisseo_boa.monitoring');
            $results = $monitoring->compute($data['offer'], $data['month']);

            $date = \DateTimeImmutable::createFromMutable($data['month']);
            $format = 'Y-m-d H:m:i';
            $navDate = [
                'previous_month' => $date->modify('-1 month')->format($format),
                'next_month' => $date->modify('+1 month')->format($format),
                'previous_day' => $date->modify('-1 day')->format($format),
                'next_day' => $date->modify('+1 day')->format($format),
                'previous_hour' => $date->modify('-1 hour')->format($format),
                'next_hour' => $date->modify('+1 hour')->format($format)
            ];
        }

        return $this->render(
            'TisseoBoaBundle:Monitoring:offer_search.html.twig',
            [
                'navTitle' => 'tisseo.boa.menu.monitoring.manage',
                'pageTitle' => 'tisseo.boa.monitoring.offer_by_line.title',
                'form' => $form->createView(),
                'results' => isset($results) ? $results : null,
                'navDate' => isset($navDate) ? $navDate : null,
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
        $this->denyAccessUnlessGranted('BUSINESS_VIEW_MONITORING');

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

    /*
    data: {
        labels: ["1/11", "2/11", "3/11", "4/11", "5/11", "6/11", "7/11", "8/11", "9/11", "10/11", "11/11", "12/11",
            "13/11", "14/11", "15/11", "16/11", "17/11", "18/11", "19/11", "20/11", "21/11", "22/11", "23/11", "24/11",
            "25/11", "26/11", "27/11", "28/11", "29/11", "30/11"],
        datasets: [{
            label: '18/L01',
            data: [12, 19, 3, 5, 2, 3, 12, 19, 3, 5, 2, 3, 12, 19, 3, 5, 2, 3, 12, 19, 3, 5, 2, 3, 19, 3, 5, 2, 3],
            backgroundColor: 'rgba(41,37,237,0.8)',
            borderColor: 'rgba(41,37,237,0.8)',
            borderWidth: 1
        }, {
            label: '14/L01',
            data: [15, 10, 1, 6, 4, 9, 15, 10, 1, 6, 4, 9, 15, 10, 1, 6, 4, 9, 15, 10, 1, 6, 4, 9, 5, 10, 1, 6, 4, 9],
            backgroundColor: 'rgba(44,186,109,0.83)',
            borderColor: 'rgba(44,186,109,0.83)',
            borderWidth: 1
        }]
    }
    */
    public function generateGraphAction($routes, $dates)
    {
        $this->denyAccessUnlessGranted('BUSINESS_VIEW_MONITORING');

        $response = new Response();

        return $response;
    }
}
