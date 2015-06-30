<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use Tisseo\EndivBundle\Entity\StopHistory;
use Tisseo\BoaBundle\Form\Type\StopHistoryType;
use Tisseo\BoaBundle\Form\Type\StopHistoryCloseType;

class StopHistoryController extends AbstractController
{
    public function createAction(Request $request, $stopId)
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');

        $stopManager = $this->get('tisseo_endiv.stop_manager');
        $stop = $stopManager->find($stopId);

        if (empty($stop))
            throw new \Exception("Can't create a new StopHistory because the related stop with ID: ".$stopId." can't be found."); 
    
        $latestStopHistory = $stop->getLatestStopHistory();

        if (empty($latestStopHistory))
        {
            $stopHistory = new StopHistory();
            $stopHistory->setStop($stop);
        }
        else
        {
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
            $stopHistory->setTheGeom(new Point($form->get('x')->getData(), $form->get('y')->getData(), $form->get('srid')->getData()));
            
            try {
                $stopManager->createStopHistory($stopHistory, $latestStopHistory);
                $this->get('session')->getFlashBag()->add('success', 'stop_history.created');
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
            'TisseoBoaBundle:StopHistory:form.html.twig',
            array(
                'form' => $form->createView(),
                'theGeom' => $stopHistory->getTheGeom(),
                'startDate' => $startDate
            )
        );
    }

    public function closeAction(Request $request, $stopId)
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');

        $stopManager = $this->get('tisseo_endiv.stop_manager');
        $stop = $stopManager->find($stopId);
        if (!$stop->closable())
        {
            $this->get('session')->getFlashBag()->add(
                'warning',
                $this->get('translator')->trans(
                    'stop.errors.unclosable',
                    array(),
                    'messages'
                )
            );

            return $this->redirect(
                $this->generateUrl(
                    'tisseo_boa_stop_edit',
                    array('stopId' => $stopId)
                )
            );
        }

        $stopHistory = $stopManager->getLatestStopHistory($stopId);

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
            try {
                $stopManager->closeStopHistory($stopHistory);
                $this->get('session')->getFlashBag()->add('success', 'stop_history.closed');
            }
            catch(\Exception $e) {
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }

            return $this->redirect(
                $this->generateUrl(
                    'tisseo_boa_stop_edit',
                    array('stopId' => $stopId)
                )
            );
        }

        return $this->render('TisseoBoaBundle:StopHistory:close.html.twig',
            array(
                'form' => $form->createView()
            )
        );
    }
}
