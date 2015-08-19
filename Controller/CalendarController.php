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
    public function editAction(Request $request, $calendarId)
    {
        $this->isGranted('BUSINESS_MANAGE_CALENDARS');

        $calendarManager = $this->get('tisseo_endiv.calendar_manager');
        $calendarElementManager = $this->get('tisseo_endiv.calendar_element_manager');
        $calendar = $calendarManager->find($calendarId);

        if (empty($calendar))
        {
            $calendar = new Calendar();
            $calendarElements = new CalendarElement();
        }
        else
            $calendarElements = $calendarElementManager->findbyCalendar($calendarId);

        $calendarForm = $this->createForm(
            new CalendarType(),
            $calendar,
            array(
                'action' => $this->generateUrl(
                    'tisseo_boa_calendar_edit',
                    array('calendarId' => $calendarId)
                )
            )
        );

        if ($request->request->has('boa_calendar')) {
            $calendarForm->handleRequest($request);
            if ($calendarForm->isValid()) {
                $datas = $calendarForm->getData();
                try {
                    $calendarManager->save($datas);
                    $calendarId = $datas->getId();

                    $newDatas = $calendarForm->get('calendarElement')->getData();
                    $removeDatas = $calendarForm->get('removeElement')->getData();

                    foreach ($newDatas as $calendarElement)
                        $calendarElementManager->save($calendarId, $calendarElement);

                    for (end($removeDatas); key($removeDatas) !== null; prev($removeDatas))
                    {
                        $removeElement = current($removeDatas);
                        $calendarElementManager->delete($removeElement["id"]);
                    }
                } catch(\Exception $e) {
                    $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
                }
            }

            return $this->redirect(
                $this->generateUrl(
                    'tisseo_boa_calendar_edit',
                    array('calendarId' => $calendarId)
                )
            );
        }

        return $this->render(
            'TisseoBoaBundle:Calendar:form.html.twig',
            array(
                'calendarForm' => $calendarForm->createView(),
                'calendarElements' => $calendarElements,
                'calendarId' => $calendarId,
                'title' => ($calendarId ? 'calendar.edit' : 'calendar.create')
            )
        );
    }

    public function listAction($calendarType)
    {
        $this->isGranted(
            array(
                'BUSINESS_MANAGE_CALENDARS',
                'BUSINESS_VIEW_CALENDARS'
            )
        );

        $calendarManager = $this->get('tisseo_endiv.calendar_manager');

        return $this->render(
            'TisseoBoaBundle:Calendar:list.html.twig',
            array(
                'pageTitle' => 'menu.calendar',
                'calendars' => ($calendarType ? $calendarManager->findbyType($calendarType) : $calendarManager->findAll()),
                'calendarType' => $calendarType
            )
        );
    }


    public function deleteAction($calendarId, $calendarType)
    {
        $this->isGranted('BUSINESS_MANAGE_CALENDARS');

        try {
            $this->get('tisseo_endiv.calendar_manager')->delete($calendarId);
        } catch(\Exception $e) {
            $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
        }

        return $this->redirect(
            $this->generateUrl('tisseo_boa_calendar_list', array('calendarType' => $calendarType))
        );
    }
}
