<?php

namespace Tisseo\BoaBundle\Controller;

use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\JsonResponse;
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

            if ($data['colors'] != null) {
                $colors = json_decode($data['colors']);
                foreach ($results as $key => &$result) {
                    $result['color'] = isset($colors[$key]) ? $colors[$key]->value : null;
                }
            }

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

    public function generateGraphAction(Request $request)
    {
        $this->denyAccessUnlessGranted('BUSINESS_VIEW_MONITORING');
        $response = new JsonResponse();

        try {
            $routes = $request->request->get('routes');
            if (!$routes) {
                throw new \Exception('Aucune route sélectionnée', 500);
            }

            $monitoring = $this->get('tisseo_boa.monitoring');
            $routeMng = $this->get('tisseo_endiv.route_manager');

            $data = [];

            foreach ($routes as $key => $route) {
                $date = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $route['date']);
                $objRoute = $routeMng->find($route['route_id']);

                $routeStopDeparture = null;
                foreach ($objRoute->getRouteStops() as $routeStop) {
                    if ($routeStop->getRank() == 1) {
                        $routeStopDeparture = $routeStop;
                        break;
                    }
                }

                // get route trips
                $trips = $objRoute->getTrips();

                // Compute each day of month
                $result = $monitoring->tripsByMonth($trips, $date, true);

                // Format
                $data['month']['labels'] = array_keys($result);
                $data['month']['datasets'][] = [
                    'label' => $route['name'],
                    'data' => array_values($result),
                    'backgroundColor' => $route['color'],
                    'borderColor' => $route['color'],
                    'borderWidth' => 1,
                ];

                // Compute each hour of day
                if (!is_null($routeStopDeparture)) {
                    $result = $monitoring->tripsByHour($routeStopDeparture, $date, true);
                    // Format
                    $data['hour']['labels'] = array_keys($result);
                    $data['hour']['datasets'][] = [
                        'label' => $route['name'],
                        'data' => array_values($result),
                        'backgroundColor' => $route['color'],
                        'borderColor' => $route['color'],
                        'borderWidth' => 1,
                    ];
                }
            }

            $serializer = $this->get('jms_serializer');
            $response->setContent(
                $serializer->serialize($data, 'json')
            );
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }

        return $response;
    }
}
