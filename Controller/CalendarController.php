<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\CoreBundle\Controller\CoreController;
use Tisseo\EndivBundle\Entity\Calendar;
use Tisseo\EndivBundle\Entity\CalendarElement;
use Tisseo\BoaBundle\Form\Type\CalendarType;
use Tisseo\BoaBundle\Form\Type\CalendarElementType;
use Tisseo\BoaBundle\Form\Type\RemoveElementType;


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
                'calendars' => ($calendarType ? $calendarManager->findbyType($calendarType) : $calendarManager->findAll()),
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
        $calendarEmtManager = $this->get('tisseo_endiv.calendar_element_manager');
        $calendar = $calendarManager->find($calendarId);

        if (empty($calendar))
        {
            $calendar = new Calendar();
            $calendarElements = new CalendarElement();
        }
        else
            $calendarElements = $calendarEmtManager->findbyCalendar($calendarId);

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

        if ($request->request->has('boa_calendar'))
        {
            $calendarForm->handleRequest($request);
            if ($calendarForm->isValid())
            {
                $datas = $calendarForm->getData();
                
                try
                {
                    $calendarManager->save($datas);
                    $calendarId = $datas->getId();

                    $newDatas = $calendarForm->get('calendarElement')->getData();
                    $removeDatas = $calendarForm->get('removeElement')->getData();

                    foreach ($newDatas as $calendarElement)
                        $calendarEmtManager->save($calendarId, $calendarElement);

                    for (end($removeDatas); key($removeDatas) !== null; prev($removeDatas))
                    {
                        $removeElement = current($removeDatas);
                        $calendarEmtManager->delete($removeElement["id"]);
                    }
                    $this->addFlash('success', ($calendarId ? 'tisseo.flash.success.edited' : 'tisseo.flash.success.created'));
                }
                catch(\Exception $e)
                {
                    $this->addFlashException($e->getMessage());
                }
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
                'calendarForm' => $calendarForm->createView(),
                'calendarElements' => $calendarElements,
                'calendarId' => $calendarId
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
