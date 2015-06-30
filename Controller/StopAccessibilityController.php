<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\EndivBundle\Entity\StopAccessibility;
use Tisseo\BoaBundle\Form\Type\StopAccessibilityType;

class StopAccessibilityController extends AbstractController
{
    public function createAction(Request $request, $stopId)
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');

        $form = $this->createForm(
            new StopAccessibilityType(),
            new StopAccessibility(),
            array(
                'action' => $this->generateUrl(
                    'tisseo_boa_stop_accessibility_create',
                    array('stopId' => $stopId)
                )
            )
        );

        $form->handleRequest($request);
        if ($form->isValid())
        {
            try
            {
                $stopAccessibility = $form->getData();
                $this->get('tisseo_endiv.stop_manager')->saveStopAccessibility($stopId, $stopAccessibility);
                $this->get('session')->getFlashBag()->add('success', 'stop_accessibility.created');
            }
            catch(\Exception $e) { 
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }

            return $this->redirect(
                $this->generateUrl('tisseo_boa_stop_edit',
                    array('stopId' => $stopId)
                )
            );
        }

        return $this->render(
            'TisseoBoaBundle:StopAccessibility:form.html.twig',
            array(
                'form' => $form->createView(),
                'title' => 'stop_accessibility.create'
            )
        );
    }

    public function deleteAction($stopId, $stopAccessibilityId)
    {
        $this->isGranted('BUSINESS_MANAGE_STOPS');

        $this->get('tisseo_endiv.stop_manager')->deleteStopAccessibility($stopId, $stopAccessibilityId);

        return $this->redirect(
            $this->generateUrl('tisseo_boa_stop_edit',
                array('stopId' => $stopId)
            )
        );
    }
}
