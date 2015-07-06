<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use Tisseo\EndivBundle\Entity\Stop;
use Tisseo\EndivBundle\Entity\StopHistory;
use Tisseo\EndivBundle\Entity\StopDatasource;
use Tisseo\BoaBundle\Form\Type\StopCreateType;
use Tisseo\BoaBundle\Form\Type\StopEditType;

class StopController extends AbstractController
{
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
                'pageTitle' => 'menu.stop_point'
            )
        );
    }

    public function createAction(Request $request)
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');

        $stop = new Stop();
        $stop->addStopHistory(new StopHistory());
        $stop->addStopDatasources(new StopDatasource());

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
            $stopManager = $this->get('tisseo_endiv.stop_manager');
            try {
                foreach($form->get('stopHistories') as $stopHistory)
                    $stopHistory->getData()->setTheGeom(new Point($stopHistory->get('x')->getData(), $stopHistory->get('y')->getData(), $stopHistory->get('srid')->getData()));

                $stopId = $stopManager->create($form->getData());
                $this->get('session')->getFlashBag()->add('success', 'stop.created');
            } catch(\Exception $e) {
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }

            return $this->redirect(
                $this->generateUrl('tisseo_boa_stop_edit',
                    array('stopId' => $stopId)
                )
            );
        }

        return $this->render(
            'TisseoBoaBundle:Stop:create.html.twig',
            array(
                'title' => 'stop.create',
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
            try {
                $stopManager->save($form->getData());
                $this->get('session')->getFlashBag()->add('success', 'stop.edited');
            } catch(\Exception $e) {
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }
        
            return $this->redirect(
                $this->generateUrl('tisseo_boa_stop_edit',
                    array('stopId' => $stopId)
                )
            );
        }

        return $this->render(
            'TisseoBoaBundle:Stop:edit.html.twig',
            array(
                'pageTitle' => 'menu.stop_point',
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

    public function detachAction($stopId)
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');

        try {
            $stopAreaId = $this->get('tisseo_endiv.stop_manager')->detach($stopId);
            $this->get('session')->getFlashBag()->add('success', 'stop.detached');
        } catch(\Exception $e) {
            $stop = $this->get('tisseo_endiv.stop_manager')->find($stopId);
            $stopAreaId = $stop->getStopArea()->getId();
            $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
        }
        
        return $this->redirect(
            $this->generateUrl('tisseo_boa_stop_area_edit',
                array('stopAreaId' => $stopAreaId)
            )
        );
    }
}
