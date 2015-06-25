<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\BoaBundle\Form\Type\RouteType;
use Tisseo\BoaBundle\Form\Type\NewRouteType;
use Tisseo\BoaBundle\Form\Type\RouteDuplicateType;
use Tisseo\EndivBundle\Entity\Route;

class RouteController extends AbstractController
{
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

    public function listAction($lineVersionId)
    {
        $this->isGranted('BUSINESS_MANAGE_ROUTES');

        $routeManager = $this->get('tisseo_endiv.route_manager');
        $lineVersionManager = $this->get('tisseo_endiv.line_version_manager');

        return $this->render("TisseoBoaBundle:Route:list.html.twig",
            array(
                'pageTitle' => 'menu.route_manage',
                'routes' => $routeManager->getRoutesByLine($lineVersionId),
                'lineVersion' => $lineVersionManager->find($lineVersionId)
            )
        );
    }

    public function editAction($routeId = null)
    {
        $this->isGranted('BUSINESS_MANAGE_ROUTES');
        $request = $this->getRequest();

        $routeManager = $this->get('tisseo_endiv.route_manager');

        $routeStops = array();
        $serviceTemplates = array();
        $instantiatedServiceTemplates = array();
        $routeIsUpdatable = true;
        if ($routeId == null) {
            $route = new Route();
            $warnings = array();
        } else {
            $route = $routeManager->findById($routeId);
            $routeStops = $routeManager->getRouteStops($routeId);
            $serviceTemplates = $routeManager->getServiceTemplates($routeId);
            $instantiatedServiceTemplates = $routeManager->getInstantiatedServiceTemplates($route);
            $warnings = $routeManager->getRouteStopsWithoutRouteSection($routeStops);
            $routeIsUpdatable = (count($serviceTemplates) < 1);
        }

        $form = $this->createForm(
            new RouteType(),
            $route,
            array(
                'action'=> $this->generateUrl(
                    'tisseo_boa_route_edit',
                    array('routeId' => $routeId)
                )
            )
        );

        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                $route = $form->getData();
                $routeManager->save($route);

                $route_stops = $request->request->get('route_stops');
                $services = $request->request->get('services');
                    $routeManager->saveRouteStopsAndServices($route, $route_stops, $services, $routeIsUpdatable);
            } catch(\Exception $e) {
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }

            return $this->redirect($this->generateUrl('tisseo_boa_route_edit', array("routeId" => $routeId) ));
        }

        //route sections warnings
        if( $warnings ) {
            foreach ($warnings as $w) {
                $warning = $this->get('translator')->trans(
                    'route.route_stops.warning',
                    array('%s' => $w),
                    'messages'
                );
                $this->get('session')->getFlashBag()->add('danger', $warning);
            }
        }

        return $this->render(
            'TisseoBoaBundle:Route:edit.html.twig',
            array(
                'form' => $form->createView(),
                'title' => 'route.edit',
                'route' => $route,
                'routeStops' => $routeStops,
                'serviceTemplates' => $serviceTemplates,
                'instantiatedServiceTemplates' => $instantiatedServiceTemplates
            )
        );
    }

    public function createAction($lineVersionId)
    {
        $this->isGranted('BUSINESS_MANAGE_ROUTES');
        $request = $this->getRequest();

        $lineVersionManager = $this->get('tisseo_endiv.line_version_manager');
        $lineVersion = $lineVersionManager->find($lineVersionId);

        $route = new Route();
        $route->setLineVersion($lineVersion);

        $form = $this->createForm(
            new NewRouteType(),
            $route,
            array(
                'action' => $this->generateUrl(
                    'tisseo_boa_route_create',
                    array('lineVersionId' => $lineVersionId)
                )
            )
        );

        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                $routeManager = $this->get('tisseo_endiv.route_manager');
                $datas = $form->getData();
                $datas->setLineVersion($lineVersion);
                $routeManager->save($datas);

                return $this->redirect(
                    $this->generateUrl(
                        'tisseo_boa_route_edit',
                        array(
                            'routeId' => $datas->getId()
                        )
                    )
                );
            } catch(\Exception $e) {
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }
        }

       return $this->render("TisseoBoaBundle:Route:create.html.twig",
            array(
                "form" => $form->createView(),
                "route" => $route,
                'title' => 'route.create'
            )
        );
    }

    public function deleteAction($routeId)
    {
        $this->isGranted('BUSINESS_MANAGE_ROUTES');

        $routeManager = $this->get('tisseo_endiv.route_manager');
        $route = $routeManager->findById($routeId);
        $routeManager->remove($route);

        return $this->redirect(
            $this->generateUrl(
                'tisseo_boa_route_list',
                array('lineVersionId' => $route->getLineVersion()->getId())
            )
        );
    }

    public function duplicateAction($routeId)
    {
        $this->isGranted('BUSINESS_MANAGE_ROUTES');
        $request = $this->getRequest();

        $routeManager = $this->get('tisseo_endiv.route_manager');
        $route = $routeManager->findById($routeId);
        $line = $route->getLineVersion()->getLine();
        $activeLineVersions = $line->getActiveLineVersions();

        $form = $this->createForm(new RouteDuplicateType(),$route,
            array(
                "action"=>$this->generateUrl('tisseo_boa_route_duplicate',
                                             array("routeId" => $routeId)
                )
            )
        );

        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
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
