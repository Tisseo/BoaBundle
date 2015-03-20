<?php

namespace Tisseo\BOABundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\EndivBundle\Entity\Calendar;
use Tisseo\BOABundle\Form\Type\CalendarType;

class CalendarController extends AbstractController
{
    public function listAction(Request $request, $CalendarType)
    {
        $this->isGranted('BUSINESS_MANAGE_CALENDARS');
		$CalendarManager = $this->get('tisseo_endiv.calendar_manager');
		
		$filterForm = $this->createFormBuilder()
				->add('calendarType', 'choice', 
						array(
						'choices'   => array(
							'tous' => 'tous', 
							'jour' => 'jour', 
							'periode' => 'periode', 
							'accessibilite' => 'accessibilite', 
							'mixte' => 'mixte', 
							'brique' => 'brique'),
						'attr' => array("onchange" => 
							"javascript: this.form.submit();")))
				->getForm();
		
		 $value = 'value';
		 $filterForm->handleRequest($request);
		 if ($filterForm->isValid()) {
			 if(array_key_exists ('calendarType', $request->request->get('form'))){
				 if('tous' !== $request->request->get('form')['calendarType']) {
					 $CalendarType = $request->request->get('form')['calendarType'];
				 }
			 }
		 }
		
        return $this->render(
            'TisseoBOABundle:Calendar:list.html.twig',
            array(
                'pageTitle' => 'menu.calendar',
                'calendars' => ($CalendarType ? $CalendarManager->findbyType($CalendarType) : $CalendarManager->findAll()),
				'filterForm' => $filterForm->createView()
            )
        );
    }
	
    public function editAction(Request $request, $CalendarId)
    {
        $this->isGranted('BUSINESS_MANAGE_CALENDARS');
		
		$CalendarManager = $this->get('tisseo_endiv.calendar_manager');
		$form = $this->buildForm($CalendarId, $CalendarManager);
        $render = $this->processForm($request, $form);
		if (!$render) {
			
			
            return $this->render(
                'TisseoBOABundle:Calendar:form.html.twig',
                array(
                    'form' => $form->createView(),
                    'title' => ($CalendarId ? 'calendar.edit' : 'calendar.create')
                )
            );
        }
        return ($render);
    }

    private function buildForm($CalendarId, $CalendarManager)
    {
        $calendar = $CalendarManager->find($CalendarId);
        if (empty($calendar)) {
            $calendar = new Calendar();
        }		

        $form = $this->createForm( new CalendarType(), $calendar,
            array(
                'action' => $this->generateUrl('tisseo_boa_calendar_edit',
											array('CalendarId' => $CalendarId)
                )
            )
        );
		
		return ($form);
    }	

    private function processForm(Request $request, $form)
    {
        $form->handleRequest($request);
        $CalendarManager = $this->get('tisseo_endiv.calendar_manager');
        if ($form->isValid()) {
            $CalendarManager->save($form->getData());
            $this->get('session')->getFlashBag()->add('success',
                $this->get('translator')->trans(
                    'calendar.created',
                    array(),
                    'default'
                )
            );
            return $this->redirect(
                $this->generateUrl('tisseo_boa_calendar_list')
            );
        }
        return (null);
    }

}
