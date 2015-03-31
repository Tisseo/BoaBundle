<?php

namespace Tisseo\BOABundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\EndivBundle\Entity\Calendar;
use Tisseo\EndivBundle\Entity\CalendarElement;
use Tisseo\BOABundle\Form\Type\CalendarType;
use Tisseo\BOABundle\Form\Type\CalendarElementType;
use Tisseo\BOABundle\Form\Type\RemoveElementType;


class CalendarController extends AbstractController
{
    public function editAction(Request $request, $CalendarId)
    {
        $this->isGranted('BUSINESS_MANAGE_CALENDARS');
		
		//current calendar
		$CalendarManager = $this->get('tisseo_endiv.calendar_manager');
        $calendar = $CalendarManager->find($CalendarId);		
        if (empty($calendar)) $calendar = new Calendar($CalendarManager);
        $calendarForm = $this->createForm( new CalendarType($CalendarManager), 
				$calendar,
				array('action' => $this->generateUrl('tisseo_boa_calendar_edit',
														array('CalendarId' => $CalendarId)
											)
				)
        );
		
		//calendar elements
		$CalendarElementManager = $this->get('tisseo_endiv.calendar_element_manager');
		if (!empty($CalendarId)) {
			$calendarElements = $CalendarElementManager->findbyCalendar($CalendarId);
		} else {
			$calendarElements = new CalendarElement($CalendarElementManager);
		}
		
		//build calendar element form
		$calendarElementsFormBuilder = $this->get('form.factory')
													 ->createNamedBuilder('boa_calendar_element', 'form', NULL, array());
													 													 
		//new calendar_elements container
		$calendarElementsFormBuilder->add('calendar_element', 'collection', array(
																'type' => new CalendarElementType(),
																'allow_add' => true,
																'by_reference' => false
														));
		//calendar_elements to remove
		$calendarElementsFormBuilder->add('remove_element', 'collection', array(
																'type' => new RemoveElementType(),
																'allow_add' => true,
																'by_reference' => false
														));
		$calendarElementsForm = $calendarElementsFormBuilder->getForm();
		$datas = null;
		$calendarElementDatas = null;
		if('POST' === $request->getMethod()) {
			if ($request->request->has('boa_calendar')) {
				$calendarForm->handleRequest($request);
				if ($calendarForm->isValid()) {
						$datas = $calendarForm->getData();		
						try {
							$CalendarManager->save($datas);
							$CalendarId = $datas->getId();
						} catch(\Exception $e) {
							$this->get('session')->getFlashBag()->add('danger', $e->getMessage());
						}
				}
			}
			
			if ($request->request->has('boa_calendar_element')) {
				$calendarElementsForm->handleRequest($request);
				if ($calendarElementsForm->isValid())  {
					$new_datas = $calendarElementsForm->get('calendar_element')->getData();
					$remove_datas = $calendarElementsForm->get('remove_element')->getData();
					
					try {
						foreach($new_datas as $calendarElement) {
							$CalendarElementManager->save($CalendarId, $calendarElement);
						}
						
						foreach($remove_datas as $removeElement) {
							$CalendarElementManager->delete($removeElement["id"]);
						}
					} catch(\Exception $e) {
						$this->get('session')->getFlashBag()->add('danger', $e->getMessage());
					}
				}
			}
			
            return $this->redirect( $this->generateUrl('tisseo_boa_calendar_edit', array('CalendarId' => $CalendarId)));
		}
		
		return $this->render(
			'TisseoBOABundle:Calendar:form.html.twig',
			array(
				'calendarForm' => $calendarForm->createView(),
				'calendarElementForm' => $calendarElementsForm->createView(),
				'calendarElements' => $calendarElements,
				'calendarId' => $CalendarId,
				'title' => ($CalendarId ? 'calendar.edit' : 'calendar.create')
			)
		);
	}

	
    public function listAction(Request $request, $CalendarType)
    {
        $this->isGranted('BUSINESS_MANAGE_CALENDARS');
		$CalendarManager = $this->get('tisseo_endiv.calendar_manager');
		
		$filterForm = $this->createFormBuilder()
				->add('calendarType', 'choice', 
						array(
						'choices'   => array(
							'jour' => 'jour', 
							'periode' => 'periode', 
							'accessibilite' => 'accessibilite', 
							'mixte' => 'mixte', 
							'brique' => 'brique'),
						'data' => $CalendarType,
						'attr' => array("onchange" => 
							"javascript: this.form.submit();")))
				->getForm();
		
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
	
	
    public function deleteAction(Request $request, $CalendarId,  $CalendarType)
    {
        $this->isGranted('BUSINESS_MANAGE_CALENDARS');
        $CalendarManager = $this->get('tisseo_endiv.calendar_manager');
        $CalendarManager->delete($CalendarId);

        return $this->redirect(
            $this->generateUrl('tisseo_boa_calendar_list', array('CalendarType' => $CalendarType))
        );
    }	
}
