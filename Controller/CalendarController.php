<?php

namespace Tisseo\BoaBundle\Controller;

use Doctrine\Common\Collections\Criteria;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Acl\Exception\Exception;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Tisseo\CoreBundle\Controller\CoreController;
use Tisseo\EndivBundle\Entity\Calendar;
use Tisseo\EndivBundle\Entity\Datasource;
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
        $this->denyAccessUnlessGranted(array(
            'BUSINESS_MANAGE_CALENDARS',
            'BUSINESS_VIEW_CALENDARS'
        ));

        return $this->render(
            'TisseoBoaBundle:Calendar:list.html.twig',
            array(
                'navTitle' => 'tisseo.boa.menu.calendar.manage',
                'pageTitle' => 'tisseo.boa.calendar.title.list',
                'calendarType' => $calendarType,
                'paginate' => true,
                'processing' => 'true',
                'serverSide' => 'true',
                'iDisplayLength' => 100,
                'caseInsensitive' => 'true',
                'ajax' => $this->generateUrl('tisseo_boa_calendar_list_paginate', array(
                    'calendarType' => $calendarType,
                )),
            )
        );
    }

    /**
     * @param $calendarType
     * @param $limit integer max result per page
     * @param $offset integer current offset
     */
    public function listPaginateAction(Request $request, $calendarType)
    {
        $this->denyAccessUnlessGranted(array(
            'BUSINESS_MANAGE_CALENDARS',
            'BUSINESS_VIEW_CALENDARS'
        ));

        $length = $request->get('length');
        $length = $length && ($length!=-1)?$length:0;

        $start = $request->get('start');
        $start = $length?($start && ($start!=-1)?$start:0)/$length:0;

        $order = $request->get('order');
        $orderParam = null;
        if (!is_null($order) && is_array($order)) {
            if ($calendarType == Calendar::CALENDAR_TYPE_HYBRID || Calendar::CALENDAR_TYPE_PERIOD) {
                $columnList = $this->container->getParameter('tisseo_boa.datatable_views')['calendar_mixte'];
            } else {
                $columnList = $this->container->getParameter('tisseo_boa.datatable_views')['default_view'];
            }
            foreach ($order as $key => $orderby) {
                foreach($columnList as $index => $columnDef) {
                    if ($columnDef['index'] == $orderby['column']) {
                        $orderParam[] = [
                            'columnName' => $columnDef['colDbName'],
                            'orderDir' => $orderby['dir']
                        ];
                        break; // exit foreach
                    }
                }
            }
        }

        $search = $request->get('search');
        $filters = (empty($search['value']))?[]:['name' => $search['value']];
        $filters = array_merge(array('calendarType' => $calendarType), $filters);

        $calendarManager = $this->get('tisseo_endiv.calendar_manager');
        $data = ($calendarType ? $calendarManager->advancedFindBy($filters, $orderParam, $length, $start) : $calendarManager->findAll());

        $dataTotal = $calendarManager->findByCountResult($filters);

        return $this->createJsonResponse($data, $dataTotal, $calendarType);
    }

    /**
     * Prepare data for inject it into "datatable" js object
     * @param $data array
     * @param $dataTotal int
     * @param $calendarType string
     * @return JsonResponse
     * @throws \Exception
     */
    private function createJsonResponse($data, $dataTotal, $calendarType)
    {
        $listCalendarFormated = [
            'data' => array(),
            'recordsTotal' => $dataTotal,
            'recordsFiltered' => $dataTotal,
        ];

        $trans = $this->get('translator');
        $edit = $this->isGranted('BUSINESS_MANAGE_CALENDARS');

        foreach($data as $key => $calendar) {
            $tabCalendar = array($calendar->getName());

            $dateEnd = ($calendar->getComputedEndDate() instanceof \DateTime) ? $calendar->getComputedEndDate()->format('d-m-Y'):'';
            $dateStart = ($calendar->getComputedStartDate() instanceof \DateTime) ? $calendar->getComputedStartDate()->format('d-m-Y'):'';

            if ($calendarType == Calendar::CALENDAR_TYPE_HYBRID || $calendarType == Calendar::CALENDAR_TYPE_PERIOD) {
                if (!is_null($calendar->getLineVersion())) {
                    $version = $calendar->getLineVersion()->getLine()->getNumber() . ' - v' .
                        $calendar->getLineVersion()->getVersion();
                } else {
                    $version = '';
                }
            }

            if (isset($version)) {
                array_push($tabCalendar, $version);
                unset($version);
            }

            try {
                $payload = array(
                    'calendar' => $calendar,
                    'btnEdit' => [
                        'url' =>  $this->generateUrl('tisseo_boa_calendar_edit', [
                            'calendarId' => $calendar->getId()
                        ]),
                        'label' => ($edit ? $trans->trans('tisseo.global.edit') : $trans->trans('tisseo.global.consult'))
                    ]
                );
                if ($edit) {
                    $payload['btnDelete'] = array(
                        'url' => $this->generateUrl('tisseo_boa_calendar_delete', [
                            'calendarId' => $calendar->getId(),
                            'calendarType' => $calendarType
                        ]),
                        'label' => $trans->trans('tisseo.global.delete'),
                    );
                }
                $btnAction = $this->renderView('TisseoBoaBundle:Calendar:button.html.twig', $payload);
            } catch(\Exception $e) {
                if (!$e instanceof AccessDeniedException) {
                    throw new \Exception($e->getMessage());
                }
                $btnAction = '';
            }

            array_push($tabCalendar, $dateStart, $dateEnd, $btnAction);
            $listCalendarFormated['data'][] = $tabCalendar;
        }

        return new JsonResponse($listCalendarFormated);
    }

    /**
     * Edit
     * @param $calendarId
     *
     * Creating/editing Calendar
     */
    public function editAction(Request $request, $calendarId)
    {
        $this->denyAccessUnlessGranted(array(
            'BUSINESS_MANAGE_CALENDARS',
            'BUSINESS_VIEW_CALENDARS'
        ));

        $calendarManager = $this->get('tisseo_endiv.calendar_manager');
        $calendar = $calendarManager->find($calendarId);

        if (empty($calendar)) {
            $calendar = new Calendar();
        }

        $disabled = !$this->isGranted('BUSINESS_MANAGE_CALENDARS');
        $form = $this->createForm(
            new CalendarType(),
            $calendar,
            array(
                'action' => $this->generateUrl(
                    'tisseo_boa_calendar_edit',
                    array('calendarId' => $calendarId)
                ),
                'disabled' => $disabled
            )
        );

        $form->handleRequest($request);
        if ($form->isValid()) {
            $calendar = $form->getData();

            try {
                $this->get('tisseo_endiv.datasource_manager')->fill(
                    $calendar,
                    Datasource::DATA_SRC,
                    $this->getUser()->getUsername()
                );
                $calendarManager->save($calendar);
                $this->addFlash('success', ($calendarId ? 'tisseo.flash.success.edited' : 'tisseo.flash.success.created'));
                $calendarId = $calendar->getId();
            } catch(\Exception $e) {
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
        $this->denyAccessUnlessGranted('BUSINESS_MANAGE_CALENDARS');

        try {
            $this->get('tisseo_endiv.calendar_manager')->remove($calendarId);
        } catch(\Exception $e) {
            $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
        }

        return $this->redirectToRoute(
            'tisseo_boa_calendar_list',
            array('calendarType' => $calendarType)
        );
    }

    public function bitmaskAction(Request $request)
    {
        $this->denyAccessUnlessGranted(
            array(
                'BUSINESS_VIEW_CALENDARS',
                'BUSINESS_MANAGE_CALENDARS'
            )
        );

        $this->isAjax($request, Request::METHOD_POST);

        $calendarId = $request->request->get('calendarId');
        $startDate = \Datetime::createFromFormat('D M d Y H:i:s e+', $request->request->get('startDate'));
        $endDate = \Datetime::createFromFormat('D M d Y H:i:s e+', $request->request->get('endDate'));
        $response = new JsonResponse();

        if ($startDate && $endDate) {
            $bitmask = $this->get('tisseo_endiv.calendar_manager')->getCalendarBitmask($calendarId, $startDate, $endDate);
            $response->setData($this->buildCalendarBitMask($startDate, $bitmask));
        } else {
            $response->setStatus(JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $response;
    }

    public function calendarsIntersectionAction(Request $request)
    {
        $this->denyAccessUnlessGranted(
            array(
                'BUSINESS_VIEW_CALENDARS',
                'BUSINESS_MANAGE_CALENDARS'
            )
        );

        $this->isAjax($request, Request::METHOD_POST);

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
