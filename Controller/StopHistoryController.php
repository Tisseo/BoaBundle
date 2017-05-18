<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use Tisseo\CoreBundle\Controller\CoreController;
use Tisseo\EndivBundle\Entity\StopHistory;
use Tisseo\BoaBundle\Form\Type\StopHistoryType;
use Tisseo\BoaBundle\Form\Type\StopHistoryCloseType;

class StopHistoryController extends CoreController
{
    /**
     * Create
     * @param integer $stopid
     *
     * Creating StopHistory
     */
    public function createAction(Request $request, $stopId)
    {
        $this->denyAccessUnlessGranted('BUSINESS_MANAGE_STOPS');

        $stopManager = $this->get('tisseo_endiv.stop_manager');
        $stop = $stopManager->find($stopId);

        if (empty($stop)) {
            throw new \Exception("Can't create a new StopHistory because the related stop with ID: ".$stopId." can't be found.");
        }

        $latestStopHistory = $stop->getLatestStopHistory();

        if (empty($latestStopHistory)) {
            $stopHistory = new StopHistory();
            $stopHistory->setStop($stop);
        } else {
            $stopHistory = new StopHistory($latestStopHistory);
        }

        $startDate = new \Datetime();
        $startDate->modify('+1 day');

        $form = $this->createForm(
            new StopHistoryType(),
            $stopHistory,
            array(
                'action' => $this->generateUrl(
                    'tisseo_boa_stop_history_create',
                    array('stopId' => $stopId)
                )
            )
        );

        $form->handleRequest($request);
        if ($form->isValid())
        {
            $stopHistory = $form->getData();
            $stopHistory->setTheGeom(
                new Point(
                    $form->get('x')->getData(),
                    $form->get('y')->getData(),
                    $form->get('srid')->getData()
                )
            );

            try
            {
                $stopManager->createStopHistory($stopHistory, $latestStopHistory);
                $this->addFlash('success', 'tisseo.flash.success.created');
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
            'TisseoBoaBundle:StopHistory:form.html.twig',
            array(
                'title' => 'tisseo.boa.stop_history.title.create',
                'form' => $form->createView(),
                'theGeom' => $stopHistory->getTheGeom(),
                'startDate' => $startDate
            )
        );
    }

    /**
     * Close
     * @param integer $stopId
     *
     * Closing Stop properties
     */
    public function closeAction(Request $request, $stopId)
    {
        $this->denyAccessUnlessGranted('BUSINESS_MANAGE_STOPS');

        $stopManager = $this->get('tisseo_endiv.stop_manager');
        $stop = $stopManager->find($stopId);

        if (!$stop->closable())
        {
            $this->addFlash('warning', 'tisseo.boa.stop_point.message.unclosable');

            return $this->redirectToRoute(
                'tisseo_boa_stop_edit',
                array('stopId' => $stopId)
            );
        }

        $stopHistory = $stop->getLatestStopHistory();

        $form = $this->createForm(
            new StopHistoryCloseType(),
            $stopHistory,
            array(
                'action' => $this->generateUrl(
                    'tisseo_boa_stop_history_close',
                    array('stopId' => $stopId)
                )
            )
        );

        $form->handleRequest($request);
        if ($form->isValid())
        {
            try
            {
                $stopManager->saveStopHistory($stopHistory);
                $this->addFlash('success', 'tisseo.flash.success.closed');
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

        $message = null;
        $odtAreasNames = array();
        foreach ($stop->getOdtStops() as $odtStop) {
            if (!in_array($odtStop->getOdtArea()->getName(), $odtAreasNames)) {
                $odtAreasNames[] = $odtStop->getOdtArea()->getName();
            }
        }

        $nbOdtAreasNames = count($odtAreasNames);
        if (count($odtAreasNames)) {
            $message = $this->get('translator')->transChoice(
                'tisseo.boa.stop_history.message.odt_stop_close',
                $nbOdtAreasNames,
                array('%odt_area%' => implode(', ', $odtAreasNames))
            );
        }

        return $this->render('TisseoBoaBundle:StopHistory:close.html.twig',
            array(
                'title' => 'tisseo.boa.stop_history.title.close',
                'message' => $message,
                'form' => $form->createView()
            )
        );
    }
}
