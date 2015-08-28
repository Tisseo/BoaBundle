<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Tisseo\CoreBundle\Controller\CoreController;
use Tisseo\EndivBundle\Entity\Calendar;
use Tisseo\EndivBundle\Entity\CalendarElement;
use Tisseo\BoaBundle\Form\Type\CalendarElementType;

class CalendarElementController extends CoreController
{
    /*
     * Build Form
     * @param Calendar $calendar
     * @param integer $rank
     * @return Form $form
     *
     * Build a new RouteStopType form.
     */
    private function buildForm(Calendar $calendar, $rank = 1)
    {
        $calendarElement = new CalendarElement();
        $calendarElement->setCalendar($calendar);
        $calendarElement->setRank($rank);

        $form = $this->createForm(
            new CalendarElementType(),
            $calendarElement,
            array(
                'em' => $this->getDoctrine()->getManager($this->container->getParameter('endiv_database_connection'))
            )
        );

        return ($form);
    }

    /*
     * Render Form
     * @param integer $calendarId
     * @param integer $rank
     *
     * This method is called through ajax request in order to display a new
     * fresh CalendarElementType form when a previous one has just been
     * submitted and validated.
     */
    public function renderFormAction($calendarId, $rank)
    {
        $this->isGranted('BUSINESS_MANAGE_ROUTES');

        $calendar = $this->get('tisseo_endiv.calendar_manager')->find($calendarId);

        $form = $this->buildForm($calendar, $rank);

        return $this->render(
            'TisseoBoaBundle:CalendarElement:form.html.twig',
            array(
                'form' => $form->createView(),
                'rank' => $rank
            )
        );
    }

    /**
     * Edit
     * @param integer $calendarElementId
     *
     * If request's method is GET, display a pseudo-form (ajax/json) which
     * purpose is to create/delete RouteStop.
     *
     * Otherwise, the pseudo-form data is sent as AJAX POST request and is
     * decoded then will be used for database update.
     */
    public function editAction(Request $request, $calendarId)
    {
        $this->isGranted('BUSINESS_MANAGE_ROUTES');

        $calendar = $this->get('tisseo_endiv.calendar_manager')->find($calendarId);

        if ($request->isXmlHttpRequest() && $request->getMethod() === 'POST')
        {
            $calendarElements = json_decode($request->getContent(), true);

            try
            {
                $this->get('tisseo_endiv.calendar_element_manager')->updateCalendarElements($calendarElements, $calendar);
                $this->addFlash('success', 'tisseo.flash.success.edited');

                return $this->redirectToRoute(
                    'tisseo_boa_calendar_edit',
                    array('calendarId' => $calendarId)
                );
            }
            catch (\Exception $e)
            {
                $response = new JsonResponse();
                $response->setData(array('error' => $e->getMessage()));

                return $response;
            }
        }

        return $this->render(
            'TisseoBoaBundle:CalendarElement:edit.html.twig',
            array(
                'calendar' => $calendar
            )
        );
    }

    /*
     * Create
     * @param integer $calendarId
     *
     * This function is called though ajax request and will launch CalendarElementType
     * form validation process.
     */
    public function createAction(Request $request, $calendarId)
    {
        $this->isGranted('BUSINESS_MANAGE_ROUTES');
        $this->isPostAjax($request);

        $calendar = $this->get('tisseo_endiv.calendar_manager')->find($calendarId);

        $form = $this->buildForm($calendar);
        $form->handleRequest($request);

        if ($form->isValid())
        {
            $calendarElement = $form->getData();

            return $this->render(
                'TisseoBoaBundle:CalendarElement:new.html.twig',
                array(
                    'calendarElement' => $calendarElement
                )
            );
        }

        return $this->render(
            'TisseoBoaBundle:CalendarElement:form.html.twig',
            array(
                'form' => $form->createView()
            )
        );
    }
}
