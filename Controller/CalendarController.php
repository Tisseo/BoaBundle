<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Tisseo\CoreBundle\Controller\CoreController;
use Tisseo\EndivBundle\Entity\Calendar;
use Tisseo\EndivBundle\Entity\CalendarDatasource;
use Tisseo\BoaBundle\Form\Type\CalendarType;

class CalendarController extends CoreController
{
    /**
     * List
     * @param string $calendarType
     *
     * Listing Calendars
     */
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
                'navTitle' => 'tisseo.boa.menu.calendar.manage',
                'pageTitle' => 'tisseo.boa.calendar.title.list',
                'calendars' => ($calendarType ? $calendarManager->findBy(array('calendarType' => $calendarType)) : $calendarManager->findAll()),
                'calendarType' => $calendarType
            )
        );
    }

    /**
     * Edit
     * @param $calendarId
     *
     * Creating/editing Calendar
     */
    public function editAction(Request $request, $calendarId)
    {
        $this->isGranted('BUSINESS_MANAGE_CALENDARS');

        $calendarManager = $this->get('tisseo_endiv.calendar_manager');
        $calendar = $calendarManager->find($calendarId);

        if (empty($calendar))
            $calendar = new Calendar();

        $form = $this->createForm(
            new CalendarType(),
            $calendar,
            array(
                'action' => $this->generateUrl(
                    'tisseo_boa_calendar_edit',
                    array('calendarId' => $calendarId)
                )
            )
        );

        $form->handleRequest($request);
        if ($form->isValid())
        {
            $calendar = $form->getData();

            try
            {
                $calendarDatasource = new CalendarDatasource();
                $this->addBoaDatasource($calendarDatasource);
                $calendarDatasource->setCalendar($calendar);
                $calendar->addCalendarDatasource($calendarDatasource);
                $calendarManager->save($calendar);
                $this->addFlash('success', ($calendarId ? 'tisseo.flash.success.edited' : 'tisseo.flash.success.created'));
                $calendarId = $calendar->getId();
            }
            catch(\Exception $e)
            {
                $this->addFlashException($e->getMessage());
            }

            return $this->redirectToRoute(
                'tisseo_boa_calendar_edit',
                array('calendarId' => $calendarId)
            );
        }

        return $this->render(
            'TisseoBoaBundle:Calendar:edit.html.twig',
            array(
                'pageTitle' => ($calendarId ? 'tisseo.boa.calendar.title.edit' : 'tisseo.boa.calendar.title.create'),
                'form' => $form->createView(),
                'calendar' => $calendar
            )
        );
    }

    public function deleteAction($calendarId, $calendarType)
    {
        $this->isGranted('BUSINESS_MANAGE_CALENDARS');

        try {
            $this->get('tisseo_endiv.calendar_manager')->remove($calendarId);
        } catch(\Exception $e) {
            $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
        }

        return $this->redirect(
            $this->generateUrl('tisseo_boa_calendar_list', array('calendarType' => $calendarType))
        );
    }

    public function bitmaskAction(Request $request)
    {
        $this->isGranted(
            array(
                'BUSINESS_VIEW_CALENDARS',
                'BUSINESS_MANAGE_CALENDARS'
            )
        );

        $this->isPostAjax($request);

        $calendarId = $request->request->get('calendarId');
        $startDate = \Datetime::createFromFormat('D M d Y H:i:s e+', $request->request->get('startDate'));
        $endDate = \Datetime::createFromFormat('D M d Y H:i:s e+', $request->request->get('endDate'));
        $bitmask = $this->get('tisseo_endiv.calendar_manager')->getCalendarBitmask($calendarId, $startDate, $endDate);

        $response = new JsonResponse();
        $response->setData($this->buildCalendarBitMask($startDate, $bitmask));

        return $response;
    }

    public function calendarsIntersectionAction(Request $request)
    {
        $this->isGranted(
            array(
                'BUSINESS_VIEW_CALENDARS',
                'BUSINESS_MANAGE_CALENDARS'
            )
        );

        $this->isPostAjax($request);

        $dayCalendarId = $request->request->get('dayCalendarId');
        $periodCalendarId = $request->request->get('periodCalendarId');

        $calendarManager = $this->get('tisseo_endiv.calendar_manager');
        $periodCalendar = $calendarManager->find($periodCalendarId);

        $bitmask = $calendarManager->getCalendarsIntersectionBitmask(
            $dayCalendarId,
            $periodCalendarId,
            $periodCalendar->getComputedStartDate(),
            $periodCalendar->getComputedEndDate()
        );

        $response = new JsonResponse();

        if ($request->request->get('bitmask'))
        {
            $response->setData($this->buildCalendarBitMask($periodCalendar->getComputedStartDate(), $bitmask));
        }
        else
        {
            if (strpos($bitmask, '1') === false)
                $response->setData(false);
            else
                $response->setData(true);
        }

        return $response;
    }

    private function buildCalendarBitMask(\Datetime $startDate, $bitmask)
    {
        $data = array();
        $strlen = strlen($bitmask);
        for ($i = 0; $i < $strlen; $i++)
        {
            $bit = substr($bitmask, $i, 1);
            $data[$startDate->format('Ymd')] = $bit;
            $startDate->modify('+1 day');
        }

        return $data;
    }
}
