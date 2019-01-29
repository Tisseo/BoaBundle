<?php

namespace Tisseo\BoaBundle\Controller;

use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Tisseo\CoreBundle\Controller\CoreController;

class OfferMonitoringController extends CoreController
{
    /**
     * Default route. Display the offers by line page.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction(Request $request)
    {
        $this->denyAccessUnlessGranted('BUSINESS_VIEW_MONITORING');

        $data = $request->request->get('boa_offer_by_line_type');
        $defaultColors = $this->container->getParameter('tisseo_boa.configuration')['defaultColors'];

        $form = $this->createForm(
            'boa_offer_by_line_type',
            $data
        );

        $form->handleRequest($request);
        if ($form->isValid()) {
            $data = $form->getData();
            $monitoring = $this->get('tisseo_boa.monitoring');
            $results = $monitoring->search($data['offer']);

        } else {
            $session = new Session();
            $session->set('cachedBitmask', []);
            $session->set('cachedTrip', []);
        }

        return $this->render(
            'TisseoBoaBundle:Monitoring:offer_search.html.twig',
            [
                'navTitle' => 'tisseo.boa.menu.monitoring.manage',
                'pageTitle' => 'tisseo.boa.monitoring.offer_by_line.title',
                'form' => $form->createView(),
                'defaultColors' => json_encode($defaultColors),
                'results' => isset($results) ? json_encode($results) : null,
                'navDate' => isset($navDate) ? $navDate : null,
            ]
        );
    }

  /**
   * Ajax route. Search the LineVersions of a line
   *
   * @param $lineId
   *
   * @return \Symfony\Component\HttpFoundation\Response JSON
   * @internal param $month
   *
   */
    public function searchLineVersionAction($lineId)
    {
        $this->denyAccessUnlessGranted('BUSINESS_VIEW_MONITORING');

        $response = new Response();
        $lvm = $this->get('tisseo_endiv.line_version_manager');
        $serializer = $this->get('jms_serializer');

        $result = $lvm->findBy(['line' => $lineId], ['version' => 'desc']);

        $response->setContent(
            $serializer->serialize(
                $result,
                'json',
                SerializationContext::create()->setGroups(['monitoring'])
            )
        );

        return $response;
    }

  /**
   * Ajax route. Generate data for graph
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   * @throws \Exception
   */
    public function genGraphAction(Request $request)
    {
        $this->denyAccessUnlessGranted('BUSINESS_VIEW_MONITORING');
        $response = new JsonResponse();
        try {
            $routes = $request->request->get('routes');
            if (!$routes) {
                throw new \Exception('Aucune route sélectionnée', 500);
            }

            $monitoring = $this->get('tisseo_boa.monitoring');
            $data = $monitoring->getGraphData($routes);
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
