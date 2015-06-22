<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\EndivBundle\Entity\Stop;
use Tisseo\EndivBundle\Entity\StopHistory;
use Tisseo\EndivBundle\Entity\StopAccessibility;
use Tisseo\EndivBundle\Entity\StopDatasource;
use Tisseo\BoaBundle\Form\Type\StopType;
use Tisseo\BoaBundle\Form\Type\NewStopType;
use Tisseo\BoaBundle\Form\Type\StopAccessibilityType;
use Tisseo\BoaBundle\Form\Type\StopHistoryType;

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
                'title' => 'stop.title'
            )
        );
    }

    public function editAction(Request $request, $stopId)
    {
        if ($request->isMethod('POST')) {
            $this->isGranted('BUSINESS_MANAGE_STOPS');
        } else {
            $this->isGranted(array(
                    'BUSINESS_MANAGE_STOPS',
                    'BUSINESS_VIEW_STOPS',
                )
            );
        }

        //current stop
        $StopManager = $this->get('tisseo_endiv.stop_manager');
        $stop = $StopManager->find($stopId);

        if (empty($stop)) $stop = new Stop($StopManager);

        $masterStopLabel = "";
        $masterStop = $stop->getMasterStop();
        $stopHistories = $StopManager->getStopHistoriesOrderByDate($stop);
        $phantoms = $stop->getPhantoms();
        $accessibilities = $stop->getStopAccessibilities();
        $phantomAccessibilities = null;
        if (!empty($masterStop)) {
            $masterStopLabel = $StopManager->getStopLabel($masterStop);
            $phantomAccessibilities = $masterStop->getStopAccessibilities();
        }

        $form = $this->createForm( new StopType($StopManager), $stop);
        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                $datas = $form->getData();

                //id not serial and not in formtype ...
                if( $stopId ) {
                    $datas->setId($stopId);
                }

                $StopManager->save($datas);

                return $this->redirect(
                    $this->generateUrl('tisseo_boa_stop_edit',
                        array('stopId' => $stopId)
                    )
                );
            } catch(\Exception $e) {
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }
        }

        return $this->render(
            'TisseoBoaBundle:Stop:form.html.twig',
            array(
                'form' => $form->createView(),
                'stop' => $stop,
                'title' => 'stop.edit',
                'masterStopLabel' => $masterStopLabel,
                'stopHistories' => $stopHistories,
                'phantoms' => $phantoms,
                'accessibilities' => $accessibilities,
                'phantomAccessibilities' => $phantomAccessibilities
            )
        );
    }

    public function newAction(Request $request)
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');

        $StopManager = $this->get('tisseo_endiv.stop_manager');
        $stop = new Stop($StopManager);
        $stop->addStopHistory(new StopHistory());
        $stop->addStopDatasources(new StopDatasource());

        $stopAreaId = $request->query->get('stopAreaId');

        $stopArea = null;
        if( $stopAreaId ) {
            $StopAreaManager = $this->get('tisseo_endiv.stop_area_manager');
            $stopArea = $StopAreaManager->find($stopAreaId);
        }

        $form = $this->createForm( new NewStopType($StopManager), $stop,
                array('action' => $this->generateUrl('tisseo_boa_stop_new',
                                                        array('stopAreaId' => $stopAreaId)
                                            )
                )
        );

        $form->handleRequest($request);
        if ($form->isValid()) {
            $stopId =null;
            try {
                $datas = $form->getData();

                $x = $form->get('x')->getData();
                $y= $form->get('y')->getData();
                $srid = $form->get('srid')->getData();
                $StopManager->save($datas, $x, $y, $srid);

                if( $stopAreaId )
                    return $this->redirect(
                        $this->generateUrl('tisseo_boa_stop_area_edit',
                            array('stopAreaId' => $stopAreaId)
                        )
                    );
                else
                    $stopId = $datas->getId();
                    return $this->redirect(
                        $this->generateUrl('tisseo_boa_stop_edit',
                            array('stopId' => $stopId)
                        )
                    );
            } catch(\Exception $e) {
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }
        }

        return $this->render(
            'TisseoBoaBundle:Stop:new.html.twig',
            array(
                'form' => $form->createView(),
                'title' => 'stop.create',
                'stopArea' => $stopArea
            )
        );
    }

    public function newStopHistoryAction(Request $request, $stopId)
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');

        $StopManager = $this->get('tisseo_endiv.stop_manager');
        $stop = $StopManager->find($stopId);
        $currentStopHistory = $StopManager->getCurrentStopHistory($stop);

        $form = $this->createForm( new StopHistoryType($StopManager),
            new StopHistory($StopManager),
            array(
                'action' => $this->generateUrl('tisseo_boa_stop_history_new',
                        array('stopId' => $stopId)
                )
            )
        );

        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                $datas = $form->getData();
                $x = $form->get('x')->getData();
                $y= $form->get('y')->getData();
                $srid = $form->get('srid')->getData();


                $StopManager->addStopHistory($stop, $datas, $x, $y, $srid);

                return $this->redirect(
                    $this->generateUrl('tisseo_boa_stop_edit',
                        array('stopId' => $stopId)
                    )
                );
            } catch(\Exception $e) {
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }
        }

        return $this->render(
            'TisseoBoaBundle:Stop:new_stop_history.html.twig',
            array(
                'form' => $form->createView(),
                'title' => 'stop.add_history',
                'currentStopHistory' => $currentStopHistory
            )
        );
    }

    public function newAccessibilityAction(Request $request, $stopId)
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');

        $StopManager = $this->get('tisseo_endiv.stop_manager');
        $stop = $StopManager->find($stopId);
        $form = $this->createForm( new StopAccessibilityType($StopManager),
            new StopAccessibility($StopManager),
            array(
                'action' => $this->generateUrl('tisseo_boa_stop_accessibility_new',
                    array('stopId' => $stopId)
                )
            )
        );

        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                $datas = $form->getData();
                $StopManager->addAccessibility($stop, $datas);

                return $this->redirect(
                    $this->generateUrl('tisseo_boa_stop_edit',
                        array('stopId' => $stopId)
                    )
                );
            } catch(\Exception $e) {
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }
        }

        return $this->render(
            'TisseoBoaBundle:Stop:new_accessibility.html.twig',
            array(
                'form' => $form->createView(),
                'title' => 'stop.add_inaccessibility'
            )
        );
    }

    public function removeStopAccessibilityAction(Request $request, $stopId, $StopAccessibilityId)
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');

        $fp = fopen('/tmp/tmp/tmp', 'w');
        fwrite($fp, $stopId."\n");
        fwrite($fp, $StopAccessibilityId."\n");
        fclose($fp);


        $StopManager = $this->get('tisseo_endiv.stop_manager');
        $stop = $StopManager->find($stopId);
        $StopManager->removeStopAccessibility($stop, $StopAccessibilityId);

        return $this->redirect(
            $this->generateUrl('tisseo_boa_stop_edit',
                array('stopId' => $stopId)
            )
        );
    }

    public function removeStopHistoryAction(Request $request, $stopId, $StopHistoryId)
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');

        $StopManager = $this->get('tisseo_endiv.stop_manager');
        $stop = $StopManager->find($stopId);
        $StopManager->removeStopHistory($stop, $StopHistoryId);

        return $this->redirect(
            $this->generateUrl('tisseo_boa_stop_edit',
                array('stopId' => $stopId)
            )
        );
    }

    public function closeStopAction(Request $request, $stopId, $closingDate = null)
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');
        $StopManager = $this->get('tisseo_endiv.stop_manager');
        $stop = $StopManager->find($stopId);

        $closingDate = $request->query->get('closingDate');
        $stopAreaId = $request->query->get('stopAreaId');

        if( !$closingDate ) {
            $formBuilder = $this->createFormBuilder($stop)
            ->setAction($this->generateUrl('tisseo_boa_stop_close',
                    array('stopId' => $stopId)
                )
            );
            $form = $formBuilder->getForm();
            $form->handleRequest($request);
            if ($form->isValid()) {
                $closingDate = $request->request->get('closingDate');
            }
        }

        if( $closingDate ) {
            try {
                $StopManager->closeStop($stop, $closingDate);
            } catch(\Exception $e) {
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }

            //call from stop_area form
            if( $stopAreaId )
                return $this->redirect(
                    $this->generateUrl('tisseo_boa_stop_area_edit',
                        array('stopAreaId' => $stopAreaId)
                    )
                );

            return $this->redirect(
                $this->generateUrl('tisseo_boa_stop_edit',
                    array('stopId' => $stopId)
                )
            );
        }

        return $this->render('TisseoBoaBundle:Stop:close_stop.html.twig',
            array(
                'form' => $form->createView(),
                'title' => 'stop.close_stop'
            )
        );
    }
}
