<?php

namespace Tisseo\BoaBundle\Controller;

use Tisseo\CoreBundle\Controller\CoreController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Tisseo\BoaBundle\Form\Type\CityCreateType;
use Tisseo\BoaBundle\Form\Type\CityEditType;
use Tisseo\EndivBundle\Entity\City;

class CityController extends CoreController
{
    /**
     * Searching for city
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction()
    {
        $this->isGranted(array(
                'BUSINESS_MANAGE_STOPS',
                'BUSINESS_VIEW_STOPS',
            )
        );


        return $this->render(
            'TisseoBoaBundle:City:search.html.twig',
            array(
                'navTitle' => 'tisseo.boa.menu.stop.manage',
                'pageTitle' => 'tisseo.boa.menu.stop.city'
            )
        );
    }

    /**
     * Create City
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');

        $form = $this->createForm(
            new CityCreateType(),
            new City(),
            array(
                'action' => $this->generateUrl(
                    'tisseo_boa_city_create'
                )
            )
        );

        $form->handleRequest($request);
        if ($form->isValid())
        {
            try
            {

                $this->get('tisseo_endiv.city_manager')->save($form->getData());
                $this->addFlash('success', 'tisseo.flash.success.created');

            } catch(\Exception $e) {

                $this->addFlashException($e->getMessage());

            }

            return $this->redirectToRoute( 'tisseo_boa_city_search');
        }

        return $this->render(
            'TisseoBoaBundle:City:create.html.twig',
            array(
                'title' => 'tisseo.boa.city.title.create',
                'form' => $form->createView()
            )
        );

    }

    /**
     * Edit city
     *
     * @param Request $request
     * @param $stopId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, $cityId)
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');

        $cityManager = $this->get('tisseo_endiv.city_manager');
        $city = $cityManager->find($cityId);

        $form = $this->createForm(
            new CityEditType(),
            $city,
            array(
                'action' => $this->generateUrl(
                    'tisseo_boa_city_edit',
                    array('cityId' => $cityId)
                )
            )
        );

        $form->handleRequest($request);
        if ($form->isValid())
        {
            try
            {
                $cityManager->save($form->getData());
                $this->addFlash('success', 'tisseo.flash.success.edited');
            }
            catch(\Exception $e)
            {
                $this->addFlashException($e->getMessage());
            }

            return $this->redirectToRoute(
                'tisseo_boa_city_edit',
                array('cityId' => $cityId)
            );
        }

        return $this->render(
            'TisseoBoaBundle:City:edit.html.twig',
            array(
                'pageTitle' => 'tisseo.boa.city.title.edit',
                'form' => $form->createView(),
                'listStopAreas' => (!isset($listStopAreas)) ? null : $listStopAreas,
                'paginate' => true,
                'processing' => 'true',
                'serverSide' => 'true',
                'iDisplayLength' => 100,
                'ajax' => $this->generateUrl('tisseo_boa_city_stoparea_json', array(
                    'cityId' => $cityId,
                )),
            )
        );
    }

    /**
     * @param Request $request
     * @param $cityId
     * @return mixed
     */
    public function listStopAreaAction(Request $request, $cityId)
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');

        $stopAreaManager = $this->get('tisseo_endiv.stop_area_manager');

        $length = $request->get('length');
        $length = $length && ($length!=-1)?$length:0;

        $start = $request->get('start');
        $start = $length?($start && ($start!=-1)?$start:0)/$length:0;

        $order = $request->get('order');
        $orderParam = array();
        $columnList = $this->container->getParameter('tisseo_boa.datatable_views')['city_edit'];
        if (!is_null($order) && is_array($order)) {

            foreach ($order as $key => $orderby) {
                foreach($columnList as $index => $columnDef) {
                    if ($columnDef['index'] == $orderby['column']) {
                        $orderParam[] = [
                            'columnName' => $columnDef['colDbName'],
                            'orderDir' => $orderby['dir']
                        ];
                        break; // exit foreach
                    }
                }
            }
        }
        else {
            $columnName = $columnList[0]['colDbName'];
            $orderParam[] = array('columnName' => $columnName, 'orderDir' => 'asc');
        }
        $search = $request->get('search');
        $search = (empty($search['value']))?[]:['longName' => $search['value']];

        $data = $stopAreaManager->findByCityId($cityId, $search, $orderParam, $length, $start);
        $dataTotal = $stopAreaManager->findByCountResult($cityId, $search);

        return $this->createJsonResponse($data, $dataTotal);
    }


    public function deleteStopAreaAction(Request $request, $stopAreaId)
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');
        $stopAreaManager = $this->get('tisseo_endiv.stop_area_manager');

        try {
            /** @var /Tisseo/EndivBundle/Entity/StopArea $stopArea */
            $stopArea = $stopAreaManager->find($stopAreaId);

            if ($stopArea->getStops()->count() > 0) {
                $this->addFlash('error','tisseo.boa.city.message.error.stop_exist');
            } else {
                $stopAreaManager->delete($stopArea);
                $this->addFlash('success','tisseo.boa.city.message.success.stoparea_delete');
            }
        } catch(\Exception $e) {
            $this->addFlashException($e->getMessage());
        }

        return $this->redirectToRoute(
            'tisseo_boa_city_edit',
            array('cityId' => $stopArea->getCity()->getId())
        );

    }

    /**
     * Prepare data for inject it into "datatable" js object
     * @param $data array
     * @param $dataTotal int
     * @return JsonResponse
     * @throws \Exception
     */
    private function createJsonResponse($data, $dataTotal)
    {
        $arrayFormated = [
            'data' => array(),
            'recordsTotal' => $dataTotal,
            'recordsFiltered' => $dataTotal,
        ];

        $trans = $this->get('translator');

        $stopAreaManager = $this->get('tisseo_endiv.stop_area_manager');

        foreach($data as $key => $item) {

            $result = array();
            $longName = $item->getLongName();
            $stopCount =  $item->getStops()->count();
            $linesNumbers = $this->renderView(
                'TisseoBoaBundle:City:col_line.html.twig',
                [
                    'lines' => $stopAreaManager->getLinesByStop($item->getId(), false)
                ]
            );

            try {
                if ($stopCount == 0) {
                    $this->isGranted('BUSINESS_MANAGE_STOPS');
                    $btnAction = $this->renderView('TisseoBoaBundle:City:button_delete.html.twig', [
                        'stopArea' => $item
                    ]);
                } else {
                    $btnAction = null;
                }
            } catch(\Exception $e) {
                if (!$e instanceof AccessDeniedException) {
                    throw new \Exception($e->getMessage());
                }
                $btnAction = null;
            }
            array_push($result, $longName, $stopCount, $linesNumbers, $btnAction);
            $arrayFormated['data'][] = $result;
        }

        return new JsonResponse($arrayFormated);
    }
}
