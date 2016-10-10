<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use Tisseo\CoreBundle\Controller\CoreController;
use Tisseo\EndivBundle\Entity\Stop;
use Tisseo\EndivBundle\Entity\StopHistory;
use Tisseo\EndivBundle\Entity\Datasource;
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
        $this->denyAccessUnlessGranted(array(
            'BUSINESS_MANAGE_STOPS',
            'BUSINESS_VIEW_STOPS',
        ));

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
        $this->denyAccessUnlessGranted('BUSINESS_MANAGE_STOPS');

        $stop = new Stop();
        $stop->addStopHistory(new StopHistory());

        $this->get('tisseo_endiv.manager.datasource')->fill(
            $stop,
            Datasource::DATA_SRC,
            $this->getUser()->getUsername()
        );

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

                $stopId = $this->get('tisseo_endiv.manager.stop')->create($form->getData());
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
        $this->denyAccessUnlessGranted(array(
            'BUSINESS_MANAGE_STOPS',
            'BUSINESS_VIEW_STOPS'
        ));

        $stopManager = $this->get('tisseo_endiv.manager.stop');
        $stop = $stopManager->find($stopId);

        if (empty($stop)) {
            $stop = new Stop();
        }

        $stopsJson = json_encode($stopManager->getStopsJson(array($stop), true));

        if ($stop->getMasterStop() instanceof Stop) {
            $stopHistory = $stop->getMasterStop()->getCurrentOrLatestStopHistory(new \Datetime());
        } else {
            $stopHistory = $stop->getCurrentOrLatestStopHistory(new \Datetime());
        }

        $disabled = !$this->isGranted('BUSINESS_MANAGE_STOPS');
        $form = $this->createForm(
            new StopEditType($stopHistory),
            $stop,
            array(
                'action' => $this->generateUrl(
                    'tisseo_boa_stop_edit',
                    array('stopId' => $stopId)
                ),
                'disabled' => $disabled
            )
        );

        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                $stopManager->save($form->getData());
                $this->addFlash('success', 'tisseo.flash.success.edited');
            } catch(\Exception $e) {
                $this->addFlashException($e->getMessage());
            }

            return $this->redirectToRoute(
                'tisseo_boa_stop_edit',
                array('stopId' => $stopId)
            );
        }

        if ($stop->getMasterStop() instanceof Stop) {
            $stopHistories = $stopManager->getOrderedStopHistories($stop->getMasterStop()->getId());
        }
        else {
            $stopHistories = $stopManager->getOrderedStopHistories($stopId);
        }

        return $this->render(
            'TisseoBoaBundle:Stop:edit.html.twig',
            array(
                'pageTitle' => 'tisseo.boa.stop_point.title.edit',
                'form' => $form->createView(),
                'stopHistories' => $stopHistories,
                'stopsJson' => $stopsJson,
                'lines' => $stopManager->getLinesByStop($stopId)
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
        $this->denyAccessUnlessGranted('BUSINESS_MANAGE_STOPS');

        try
        {
            $stopAreaId = $this->get('tisseo_endiv.manager.stop')->detach($stopId);
            $this->addFlash('success', 'tisseo.flash.success.detached');
        }
        catch(\Exception $e)
        {
            $stop = $this->get('tisseo_endiv.manager.stop')->find($stopId);
            $stopAreaId = $stop->getStopArea()->getId();
            $this->addFlashException($e->getMessage());
        }

        return $this->redirectToRoute(
            'tisseo_boa_stop_area_edit',
            array('stopAreaId' => $stopAreaId)
        );
    }

    /**
     * Lock/Unlock stop
     *
     * @param integer $identifier
     */
    public function switchLockAction($identifier)
    {
        $this->denyAccessUnlessGranted('BUSINESS_MANAGE_STOPS');

        $this->get('tisseo_endiv.manager.stop')->toggleLock(array($identifier));

        return $this->redirectToRoute(
            'tisseo_boa_stop_edit',
            array('stopId' => $identifier)
        );
    }

    /**
     * Lock/Unlock multiple stops
     */
    public function switchMultipleLockAction(Request $request)
    {
        $this->denyAccessUnlessGranted('BUSINESS_MANAGE_STOPS');

        $stops = $request->request->all();
        if (!empty($stops)) {
            $this->get('tisseo_endiv.manager.stop')->toggleLock($stops);
        }

        return $this->redirectToRoute('tisseo_boa_monitoring_stop_locked');
    }

    /**
     * List locked stops
     */
    public function lockedAction()
    {
        $this->denyAccessUnlessGranted(
            array(
                'BUSINESS_MANAGE_STOPS',
                'BUSINESS_VIEW_STOPS'
            )
        );

        return $this->render(
            'TisseoBoaBundle:Stop:locked_list.html.twig',
            array(
                'navTitle'  => 'tisseo.boa.menu.stop.locked',
                'pageTitle' => 'tisseo.boa.stop_point.title.locked',
                'stops'     => $this->get('tisseo_endiv.manager.stop')->findLockedStops()
            )
        );
    }
}
