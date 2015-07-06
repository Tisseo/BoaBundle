<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\BoaBundle\Form\Type\RouteCreateType;
use Tisseo\BoaBundle\Form\Type\RouteEditType;
use Tisseo\BoaBundle\Form\Type\RouteDuplicateType;
use Tisseo\EndivBundle\Entity\Route;
use Tisseo\EndivBundle\Entity\RouteDatasource;

class RouteController extends AbstractController
{
    public function listAction($lineVersionId)
    {
        $this->isGranted('BUSINESS_MANAGE_ROUTES');

        return $this->render("TisseoBoaBundle:Route:list.html.twig",
            array(
                'pageTitle' => 'menu.route_manage',
                'lineVersion' => $this->get('tisseo_endiv.line_version_manager')->find($lineVersionId)
            )
        );
    }

    public function createAction(Request $request, $lineVersionId)
    {
        $this->isGranted('BUSINESS_MANAGE_ROUTES');

        $lineVersionManager = $this->get('tisseo_endiv.line_version_manager');
        $lineVersion = $lineVersionManager->find($lineVersionId);

        $route = new Route();
        $route->setLineVersion($lineVersion);
        $routeDatasource = new RouteDatasource();
        $this->buildDefaultDatasource($routeDatasource);
        $route->addRouteDatasource($routeDatasource);

        $form = $this->createForm(
            new RouteCreateType(),
            $route,
            array(
                'action' => $this->generateUrl(
                    'tisseo_boa_route_create',
                    array('lineVersionId' => $lineVersionId)
                ),
                'em' => $this->getDoctrine()->getManager($this->container->getParameter('endiv_database_connection'))
            )
        );

        $form->handleRequest($request);
        if ($form->isValid())
        {
            $route = $form->getData();

            try {
                $this->get('tisseo_endiv.route_manager')->save($route);
                $this->get('session')->getFlashBag()->add('success', 'route.created');
                return $this->redirect(
                    $this->generateUrl(
                        'tisseo_boa_route_edit',
                        array(
                            'routeId' => $route->getId()
                        )
                    )
                );
            } catch(\Exception $e) {
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
                return $this->redirect(
                    $this->generateUrl(
                        'tisseo_boa_line_version_list',
                        array(
                            'lineVersionId' => $lineVersionId
                        )
                    )
                );
            }
        }

        return $this->render("TisseoBoaBundle:Route:create.html.twig",
            array(
                'title' => 'route.create',
                'form' => $form->createView(),
                'route' => $route
            )
        );
    }

    public function editAction(Request $request, $routeId)
    {
        $this->isGranted('BUSINESS_MANAGE_ROUTES');

        $routeManager = $this->get('tisseo_endiv.route_manager');
        $route = $routeManager->find($routeId);

        $form = $this->createForm(
            new RouteEditType(),
            $route,
            array(
                'action'=> $this->generateUrl(
                    'tisseo_boa_route_edit',
                    array('routeId' => $routeId)
                )
            )
        );

        $form->handleRequest($request);
        if ($form->isValid())
        {
            $route = $form->getData();
            try {
                $routeManager->save($route);
                $this->get('session')->getFlashBag()->add('success', 'route.edited');
            } catch(\Exception $e) {
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }

            return $this->redirect(
                $this->generateUrl(
                    'tisseo_boa_route_edit',
                    array("routeId" => $routeId)
                )
            );
        }

        return $this->render(
            'TisseoBoaBundle:Route:edit.html.twig',
            array(
                'pageTitle' => 'menu.route_manage',
                'form' => $form->createView(),
                'title' => 'route.edit',
                'route' => $route
            )
        );
    }

    public function deleteAction($routeId)
    {
        $this->isGranted('BUSINESS_MANAGE_ROUTES');

        try {
            $lineVersionId = $this->get('tisseo_endiv.route_manager')->remove($routeId);
            $this->get('session')->getFlashBag()->add('success', 'route.deleted');
        } catch(\Exception $e) {
            $lineVersionId = $this->get('tisseo_endiv.route_manager')->find($routeId)->getLineVersion()->getId();
            $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
        }

        return $this->redirect(
            $this->generateUrl(
                'tisseo_boa_route_list',
                array('lineVersionId' => $lineVersionId)
            )
        );
    }

    public function tripCalendarAction($lineVersionId)
    {
        $this->isGranted('BUSINESS_MANAGE_ROUTES');
        $request = $this->getRequest();
        $routeManager = $this->get('tisseo_endiv.route_manager');

        if ($request->getMethod() == 'POST') {
            try {
                $grids = $request->request->get('grid');
                $routeManager->saveFHCalendars($grids);
            } catch(\Exception $e) {
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }
        }

        return $this->render("TisseoBoaBundle:Route:tripCalendar.html.twig",
            array(
                'pageTitle' => 'menu.route_manage',
                'calendars' => $routeManager->getTimetableCalendars($lineVersionId),
                'calendarTypes' => $routeManager->getSortedTypesOfGridMaskType(),
                'calendarPeriods' => $routeManager->getSortedPeriodsOfGridMaskType()
            )
        );
    }

    public function duplicateAction(Request $request, $routeId)
    {
        $this->isGranted('BUSINESS_MANAGE_ROUTES');
        $request = $this->getRequest();

        $routeManager = $this->get('tisseo_endiv.route_manager');
        $route = $routeManager->find($routeId);
        $line = $route->getLineVersion()->getLine();
        $activeLineVersions = $line->getActiveLineVersions();

        $form = $this->createForm(
            new RouteDuplicateType(),
            $route,
            array(
                "action" => $this->generateUrl(
                    'tisseo_boa_route_duplicate',
                    array("routeId" => $routeId)
                )
            )
        );

        $form->handleRequest($request);
        if ($form->isValid())
        {
            try {
                // TODO: IT SMELLS BAD
                $userName = $this->get('security.context')->getToken()->getUser()->getUsername();
                $lineVersionId = $request->request->get('line_version');
                $lineVersionManager = $this->get('tisseo_endiv.line_version_manager');
                $lineVersion = $lineVersionManager->find($lineVersionId);

                $routeManager->duplicate($route, $lineVersion, $userName);
                return
                    $this->redirect(
                        $this->generateUrl(
                            'tisseo_boa_route_list',
                            array('lineVersionId' => $route->getLineVersion()->getId())
                        )
                    );
            } catch(\Exception $e) {
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }
        }

        return $this->render("TisseoBoaBundle:Route:duplicate.html.twig",
            array(
                'title' => 'route.duplicate',
                'form' =>$form->createView(),
                'activeLineVersions' => $activeLineVersions
            )
        );
    }
}
