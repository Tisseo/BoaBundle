<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\BoaBundle\Form\Type\RouteType;
use Tisseo\BoaBundle\Form\Type\NewRouteType;
use Tisseo\EndivBundle\Entity\Route;

class RouteController extends AbstractController
{
    public function listAction(Request $request)
    {
        $this->isGranted('BUSINESS_MANAGE_ROUTES');

        $linesVManager = $this->get('tisseo_endiv.line_version_manager');
        $time = new \DateTime('now');
        $allLines = $linesVManager->findActiveLineVersions($time, "grouplines");

        return $this->render(
            'TisseoBoaBundle:Route:list.html.twig',
            array(
                'pageTitle' => 'menu.line',
                'linesV' => $allLines
            )
        );
    }

    public function routeAction(Request $request, $LineVersionId)
    {
        $this->isGranted('BUSINESS_MANAGE_ROUTES');

        $routeManager = $this->get('tisseo_endiv.route_manager');
        $lineVersionManager = $this->get('tisseo_endiv.line_version_manager');
        $lv = $lineVersionManager->find($LineVersionId);
        $lineVersionName = $lv->getLine()->getNumber().' ( version '.$lv->getVersion().' )';
        $routes = $routeManager->getRoutesByLine($LineVersionId);

        return $this->render("TisseoBoaBundle:Route:route.html.twig", array(
           'routes' => $routes,
           'line_version_name' => $lineVersionName,
           'lineVersionId' => $lv->getId()
       ));
    }

    public function editAction(Request $request, $RouteId = null)
    {
        $this->isGranted('BUSINESS_MANAGE_ROUTES');

        $routeManager = $this->get('tisseo_endiv.route_manager');

        $routeStops = array();
        $serviceTemplates = array();
        if( $RouteId == null) {
            $route = new Route();
        } else {
            $route = $routeManager->findById($RouteId);
            $routeStops = $routeManager->getRouteStops($RouteId);
            $serviceTemplates = $routeManager->getServiceTemplates($RouteId);
        }

        $form = $this->createForm(new RouteType(),$route,
            array(
                "action"=>$this->generateUrl('tisseo_boa_route_edit',
                                             array("RouteId" => $RouteId)
                )
            )
        );

        $form->handleRequest($request);
        if ($form->isValid()) {
            //try {
                $route = $form->getData();
                $routeManager->save($route);

                $route_stops = $request->request->get('route_stops');
                $services = $request->request->get('services');
                    $routeManager->saveRouteStopsAndServices($route, $route_stops, $services);
/*            
            } catch(\Exception $e) {
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }
*/
            return $this->redirect($this->generateUrl('tisseo_boa_route_edit', array("RouteId" => $RouteId) ));
        }

        return $this->render(
            'TisseoBoaBundle:Route:edit.html.twig',
            array(
                'form' => $form->createView(),
                'title' => 'route.edit',
                'route' => $route,
                'routeStops' => $routeStops,
                'serviceTemplates' => $serviceTemplates
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
        
        $form = $this->createForm(new NewRouteType(),$route,
            array(
                "action"=>$this->generateUrl('tisseo_boa_route_create',
                                             array("lineVersionId" => $lineVersionId)
                )
            )
        );

        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                $routeManager = $this->get('tisseo_endiv.route_manager');
                $datas = $form->getData();
                $routeManager->save($datas);

                return $this->redirect(
                    $this->generateUrl(
                        'tisseo_boa_route_edit', 
                        array(
                            "RouteId" => $datas->getId()
                        )
                    )
                );
            } catch(\Exception $e) {
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }
        }

       return $this->render("TisseoBoaBundle:Route:create.html.twig",
            array(
                "form" =>$form->createView(),
                'title' => 'route.create'
            )
        );
    }

    public function deleteAction(Request $request, $RouteId)
    {
        $this->isGranted('BUSINESS_MANAGE_ROUTES');

        $routeManager = $this->get('tisseo_endiv.route_manager');
        $route = $routeManager->findById($RouteId);
        $LineVersionId = $route->getLineVersion()->getId();
        $routeManager->remove($route);
        return $this->redirect($this->generateUrl('tisseo_boa_route_list', array("LineVersionId"=>$LineVersionId) ));
    }
}
