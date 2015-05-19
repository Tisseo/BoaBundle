<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\BoaBundle\Form\Type\RouteType;
use Tisseo\EndivBundle\Entity\Route;

class RouteController extends AbstractController
{
    public function listAction(Request $request)
    {
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
        $routeManager = $this->get('tisseo_endiv.route_manager');
        $lineVersionManager = $this->get('tisseo_endiv.line_version_manager');
        $lv = $lineVersionManager->find($LineVersionId);
        $lineVersionName = $lv->getLine()->getNumber().' ( version '.$lv->getVersion().' )';
        $routes = $routeManager->getRoutesByLine($LineVersionId);

        return $this->render("TisseoBoaBundle:Route:route.html.twig", array(
           'routes' => $routes,
           'line_version_name' => $lineVersionName
       ));
    }

    public function editAction(Request $request, $RouteId = null)
    {
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
                                            array("RouteId"=>$RouteId)
                )
            )
        );

        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                $datas = $form->getData();
                $routeManager->save($datas);
            } catch(\Exception $e) {
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }
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


/*        

        $lineVersionManager = $this->get('tisseo_endiv.line_version_manager');
        $routeStopManager = $this->get('tisseo_endiv.routestop_manager');
        $tripManager =  $this->get('tisseo_endiv.trip_manager');

        if(isset($id)) {
            $route= $routeManager->findById($id);
        }

        if(!$route) {
            throw $this->createNotFoundException('route non trouvée');
        }

        $isZone = false;
        if($routeManager->checkZoneStop($route) == true){
            $isZone = true;
        }

        $formRoute = $this->createForm(new RouteType(),$route,
            array("action"=>$this->generateUrl('tisseo_boa_route_edit',
                array("id"=>$id))));

        $trips = $tripManager->findByRoute($id);

        if(isset($request)) {
            $this->processForm($request, $formRoute);
        }

        $routeStop = $request->request->get('routestop');
        $resp = [];
        if(isset($routeStop)) {
            foreach ($routeStop as $stop=>$key) {
                foreach($key as $val){
                    array_push($resp,$val);
                }
            }
            //return new Response(var_dump($resp),200);
        }

        $idLineVersion=$route->getLineVersionId();
        $lineVersion = $lineVersionManager->find($idLineVersion);
        $line = $lineVersion->getLine();
        $physicalMode = $this->getDoctrine()
                             ->getRepository('Tisseo\EndivBundle\Entity\PhysicalMode','endiv')
                             ->find($line->getPhysicalMode());
        $mode = $physicalMode->getName();

            return $this->render(
                'TisseoBoaBundle:Route:edit.html.twig',
                array(
                    'form' => $formRoute->createView(),
                    'pageTitle' => 'modification de route',
                    'route' => $route,
                    'id' => $id,
                    'mode' =>$mode,
                    'isZone' => $isZone,
                    'trips' => $trips
                )
            );
*/
    }

    public function datatableSaveAction(Request $request)
    {
        $id = $request->get('id');

        $routeStopManager = $this->get('tisseo_endiv.routestop_manager');
        $stopManager = $this->get('tisseo_endiv.stop_manager');

        $stops = $request->get('list');
       // $stop = $stopManager->find()//



        foreach ($stops as $stopRow){

               foreach($stopRow as $stop){

                   $idRouteStop = $routeStopManager->findByWaypoint($stop["Id"],$id);
                   //var_dump($idRouteStop[0]["id"]);
                   $currentStop =  $this->getDoctrine()
                       ->getRepository('Tisseo\EndivBundle\Entity\RouteStop','endiv')
                       ->find($idRouteStop[0]["id"]);


                   $currentStop->setRank($stop["Ordre"]);
                   $routeStopManager->save($currentStop);
               }




        }
        Return new Response("liste sauvée", 200);
    }




    public function createAction(Request $request){

        $route = new Route();
        $lineVersionManager = $this->get('tisseo_endiv.line_version_manager');
        if(isset($request)) {
            $lineVersion = $lineVersionManager->find($request->get('idLine'));
            $route->setLineVersion($lineVersion);

        }
        $routeManager = $this->get('tisseo_endiv.route_manager');
        $form = $this->createForm(new RouteType(), $route);


        if(isset($request)) {
            $this->processForm($request, $form);
        }


       return $this->render("TisseoBoaBundle:Route:create.html.twig", array(
            "form" =>$form->createView(),
            'title' => 'Creation de route',
            'lineVersion' => $request->get('idLine')
        ));
    }

    public function deleteAction($id, Request $request){

        $routeManager = $this->get('tisseo_endiv.route_manager');
        $route = $routeManager->findById($id);

        $lineId = $request->get('idLine');

        $hasServices = $routeManager->hasTrips($id);
        
        if($hasServices == true){
             $this->get('session')->getFlashBag()->add('failed',
               'route existante'
            );
        }
        else {
             $routeManager->removeRoute($route);
             $this->get('session')->getFlashBag()->add('suppression', 'la route a été supprimée');
        }
       

        return $this->redirect($this->generateUrl('tisseo_boa_route_list', array("lineId"=>$lineId) ));

    }



    private function processForm(Request $request, $form)
    {
        $form->handleRequest($request);
        $routeManager = $this->get('tisseo_endiv.route_manager');
        if ($form->isValid() && $request->getMethod() == "POST") {
            $routeManager->save($form->getData());
            $this->get('session')->getFlashBag()->add('success',
                $this->get('translator')->trans(
                    'route.created',
                    array(),
                    'default'
                )
            );
            /**return $this->redirect(
                $this->generateUrl('tisseo_boa_route_list')
            );**/
            $this->get('session')->getFlashBag()->add('modification', 'la route a été modifiée');
        }
        return (null);
    }
}
