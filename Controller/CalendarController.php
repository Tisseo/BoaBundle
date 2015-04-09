<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\EndivBundle\Entity\Calendar;
use Tisseo\EndivBundle\Entity\CalendarElement;
use Tisseo\BoaBundle\Form\Type\CalendarType;
use Tisseo\BoaBundle\Form\Type\CalendarElementType;
use Tisseo\BoaBundle\Form\Type\RemoveElementType;


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

		if('POST' === $request->getMethod()) {
			if ($request->request->has('boa_calendar')) {
				$calendarForm->handleRequest($request);
				if ($calendarForm->isValid()) {
						$datas = $calendarForm->getData();		
						try {
							$CalendarManager->save($datas);
							$CalendarId = $datas->getId();
							
							$new_datas = $calendarForm->get('calendar_element')->getData();
							$remove_datas = $calendarForm->get('remove_element')->getData();
							
							foreach($new_datas as $calendarElement) {
								$CalendarElementManager->save($CalendarId, $calendarElement);
							}
							
							for (end($remove_datas); key($remove_datas)!==null; prev($remove_datas)){
								$removeElement = current($remove_datas);
								
								$CalendarElementManager->delete($removeElement["id"]);
							}
							
							
							
						} catch(\Exception $e) {
							$this->get('session')->getFlashBag()->add('danger', $e->getMessage());
						}
				}
			}
            return $this->redirect( $this->generateUrl('tisseo_boa_calendar_edit', 
												array(
													'CalendarId' => $CalendarId
													)));
		}
		
		return $this->render(
			'TisseoBoaBundle:Calendar:form.html.twig',
			array(
				'calendarForm' => $calendarForm->createView(),
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
		
        return $this->render(
            'TisseoBoaBundle:Calendar:list.html.twig',
            array(
                'pageTitle' => 'menu.calendar',
                'calendars' => ($CalendarType ? $CalendarManager->findbyType($CalendarType) : $CalendarManager->findAll()),
				'calendarType' => $CalendarType
            )
        );
    }	
		
		
    public function deleteAction(Request $request, $CalendarId,  $CalendarType)
    {
        $this->isGranted('BUSINESS_MANAGE_CALENDARS');
		
        $CalendarManager = $this->get('tisseo_endiv.calendar_manager');
		try {
			$CalendarManager->delete($CalendarId);
		} catch(\Exception $e) {
			$this->get('session')->getFlashBag()->add('danger', $e->getMessage());
		}

        return $this->redirect(
            $this->generateUrl('tisseo_boa_calendar_list', array('CalendarType' => $CalendarType))
        );
    }	
}
