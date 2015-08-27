<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use Tisseo\CoreBundle\Controller\CoreController;
use Tisseo\EndivBundle\Entity\Stop;
use Tisseo\EndivBundle\Entity\StopHistory;
use Tisseo\EndivBundle\Entity\StopDatasource;
use Tisseo\BoaBundle\Form\Type\StopCreateType;
use Tisseo\BoaBundle\Form\Type\StopEditType;

class StopController extends CoreController
{
    /**
     * Search
     *
     * Searching for Stops
     */
    public function searchAction()
    {
        $this->isGranted(array(
            'BUSINESS_MANAGE_STOPS',
            'BUSINESS_VIEW_STOPS',
            )
        );

        return $this->render(
            'TisseoBoaBundle:Stop:search.html.twig',
            array(
                'navTitle' => 'tisseo.boa.menu.stop.manage',
                'pageTitle' => 'tisseo.boa.menu.stop.point'
            )
        );
    }

    /**
     * Create
     *
     * Creating Stop
     */
    public function createAction(Request $request)
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');

        $stop = new Stop();
        $stop->addStopHistory(new StopHistory());
        $stopDatasource = new StopDatasource();
        $this->addBoaDatasource($stopDatasource);
        $stop->addStopDatasources($stopDatasource);

        $form = $this->createForm(
            new StopCreateType(),
            $stop,
            array(
                'action' => $this->generateUrl(
                    'tisseo_boa_stop_create'
                )
            )
        );

        $form->handleRequest($request);
        if ($form->isValid())
        {
            try
            {
                foreach($form->get('stopHistories') as $stopHistory)
                {
                    $stopHistory->getData()->setTheGeom(
                        new Point(
                            $stopHistory->get('x')->getData(),
                            $stopHistory->get('y')->getData(),
                            $stopHistory->get('srid')->getData()
                        )
                    );
                }

                $stopId = $this->get('tisseo_endiv.stop_manager')->create($form->getData());
                $this->addFlash('success', 'tisseo.flash.success.create');
            }
            catch(\Exception $e)
            {
                $this->addFlashException($e->getMessage());
                $stopId = null;
            }

            return $this->redirectToRoute(
                'tisseo_boa_stop_edit',
                array('stopId' => $stopId)
            );
        }

        return $this->render(
            'TisseoBoaBundle:Stop:create.html.twig',
            array(
                'title' => 'tisseo.boa.stop_point.title.create',
                'form' => $form->createView()
            )
        );
    }

    // TODO: Fix, refactor the whole action (about accessibility and phantoms)
    public function editAction(Request $request, $stopId)
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');

        $stopManager = $this->get('tisseo_endiv.stop_manager');
        $stop = $stopManager->find($stopId);

        if (empty($stop))
            $stop = new Stop();

        // wtf: refactor may be needed
        $masterStopLabel = "";
        $masterStop = $stop->getMasterStop();
        
        //$phantoms = $stop->getPhantoms();
        //$accessibilities = $stop->getStopAccessibilities();
        //$phantomAccessibilities = null;
        if (!empty($masterStop)) {
            $masterStopLabel = $stopManager->getStopLabel($masterStop);
            //$phantomAccessibilities = $masterStop->getStopAccessibilities();
        }

        $form = $this->createForm(
            new StopEditType($stop->getCurrentOrLatestStopHistory(new \Datetime())),
            $stop,
            array(
                'action' => $this->generateUrl(
                    'tisseo_boa_stop_edit',
                    array('stopId' => $stopId)
                )
            )
        );

        $form->handleRequest($request);
        if ($form->isValid())
        {
            try
            {
                $stopManager->save($form->getData());
                $this->addFlash('success', 'tisseo.flash.success.edited');
            }
            catch(\Exception $e)
            {
                $this->addFlashException($e->getMessage());
            }
        
            return $this->redirectToRoute(
                'tisseo_boa_stop_edit',
                array('stopId' => $stopId)
            );
        }

        return $this->render(
            'TisseoBoaBundle:Stop:edit.html.twig',
            array(
                'pageTitle' => 'tisseo.boa.stop_point.title.edit',
                'form' => $form->createView(),
                'stop' => $stop,
                'masterStopLabel' => $masterStopLabel,
                'stopHistories' => $stopManager->getOrderedStopHistories($stopId)
                //'phantoms' => $phantoms,
                //'accessibilities' => $accessibilities,
                //'phantomAccessibilities' => $phantomAccessibilities
            )
        );
    }

    /**
     * Detach
     * @param integer $stopId
     *
     * Detaching a Stop from its StopArea
     */
    public function detachAction($stopId)
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');

        try
        {
            $stopAreaId = $this->get('tisseo_endiv.stop_manager')->detach($stopId);
            $this->addFlash('success', 'tisseo.flash.success.detached');
        }
        catch(\Exception $e)
        {
            $stop = $this->get('tisseo_endiv.stop_manager')->find($stopId);
            $stopAreaId = $stop->getStopArea()->getId();
            $this->addFlashException($e->getMessage());
        }
        
        return $this->redirectToRoute(
            'tisseo_boa_stop_area_edit',
            array('stopAreaId' => $stopAreaId)
        );
    }
}
