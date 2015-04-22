<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\DateTime;
use Tisseo\BoaBundle\Form\Type\RouteType;
use Tisseo\EndivBundle\Entity\Route;
use Tisseo\EndivBundle;

class RouteController extends AbstractController
{
    public function listAction(Request $request)
    {
        $ligneVersionManager = $this->get('tisseo_endiv.line_version_manager');

        $lineManager = $this->get('tisseo_endiv.line_manager');

        $linesVManager = $this->get('tisseo_endiv.line_version_manager');
        $time = new \DateTime('now');


        $allLines = $linesVManager->findActiveLineVersions($time,"grouplines");

        return $this->render(
            'TisseoBoaBundle:Route:list.html.twig',
            array(
                'pageTitle' => 'selection d\'une ligne',
                'linesV' => $allLines
                       )
        );
    }

    public function routeAction(Request $request) {

        $id = $request->get("lineId");

        $idRoute = $request->get('idroute');
        $routeManager = $this->get('tisseo_endiv.route_manager');
        $stopManager = $this->get('tisseo_endiv.stop_manager');

        $routes = $routeManager->findAllByLine($id);
        $isZone = false;

        foreach($routes as $route) {

            if($routeManager->checkZoneStop($route) == true){
                $isZone = true;
            }

        }
    

        if(isset($id) && ($request->getMethod()) == "POST") {
            $routes = $routeManager->findAllByLine($id);
        }

      return $this->render("TisseoBoaBundle:Route:route.html.twig", array(
           'pageTitle' => 'création de routes',
           'routes' => $routes,
           'id' => $id,
           'isZone' => $isZone
       ));

    }

    public function editAction($id=null, Request $request) {

        $id = $request->get('id');
        $routeManager = $this->get('tisseo_endiv.route_manager');
        $stopManager = $this->get('tisseo_endiv.stop_manager');
        $stopsArea = [];

        if(isset($id)) {
            $route= $routeManager->findById($id);

            $trips = $this->getDoctrine()
                ->getRepository("Tisseo\EndivBundle\Entity\Trip","endiv")
                ->findBy(array("route"=>$id));
        }


         if(!$route) {
            throw $this->createNotFoundException('route non trouvée');
        }


        $formRoute = $this->createForm(new RouteType(),$route);

               if(isset($request)) {
                   $this->processForm($request, $formRoute);
               }

            return $this->render(
                'TisseoBoaBundle:Route:edit.html.twig',
                array(
                    'form' => $formRoute->createView(),
                    'pageTitle' => 'modification de route',
                    'route' => $route,
                    'id' => $id,
                    'trips' => $trips



                )
            );

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

        public function datatableAction(Request $request) {

        $id = $request->get('id');

        $routeManager = $this->get('tisseo_endiv.route_manager');
        $stopManager = $this->get('tisseo_endiv.stop_manager');
        $stopsArea = [];
        $data=[];

        $order = 0;
        $dir = "Desc";
        $order = $request->query->get("order")[0]["column"];
        $dir = $request->query->get("order")[0]["column"];

        $index = -1;


        if(isset($id)) {
            $route= $routeManager->findById($id);
            $trips = $this->getDoctrine()
                ->getRepository("Tisseo\EndivBundle\Entity\Trip","endiv")
                ->findBy(array("route"=>$id));



        }

        if(!$route) {
            throw $this->createNotFoundException('route non trouvée');
        }

        $isZone = false;

        if($routeManager->checkZoneStop($route) == true){
            $isZone = true;
        }


            $stops = $stopManager->getStopsByRoute($id);

            foreach($stops as $stop) {

                $index++;
                $waypointId = $stop["waypoint"];

                $rank = $stop["rank"];

                if($isZone == false){
                    $stops = $stopManager->getStopsByRoute($id);

                    $stopAreas[] = $stopManager->getStops($waypointId);

                    $city = $stopAreas[$index][0]["city"];
                    $name = $stopAreas[$index][0]["shortName"];

                }
                else {

                    $zone = $this->getDoctrine()
                                 ->getRepository("Tisseo\EndivBundle\Entity\OdtArea","endiv")
                                 ->find($stop["waypoint"]);
                    $city = "";

                    $name = $zone->getName();
                }

                $desc = $stop["dropOff"];
                $pickup = $stop["pickup"];

                $timeArrival = "";
                $timeDeparture = "";




                $object = new \stdClass();
                $dataArr = [
                    "Id" => $stop["waypoint"],
                    "Ordre" => $rank,
                    "Ville"=>$city,
                    "Num" => "num",
                    "Nom"=>$name,
                    "Desc"=>$desc == true ? "Oui" : "Non",
                    "Pickup"=>$pickup == true ? "Oui" : "Non"

                ];


                foreach($trips as $trip) {

                    foreach($trip->getStopTimes() as $stoptimes)
                    {

                        if($stoptimes->getRouteStop()->getId() == $stop["id"]) {

                            $timeArrival = $stoptimes->getArrivalTime();
                            $timeDeparture = $stoptimes->getDepartureTime();



                            $dataArr["Dep"] = $timeDeparture;
                            $dataArr["Arr"] = $timeArrival;
                            //array_push(array("Dep"=>$timeDeparture),$dataArr);

                        }
                    }
                }
                array_push($data,$dataArr);

            }

        $data = ["draw"=>1,"recordsTotal" => sizeof($stops), "recordFiltered" => sizeof($stops),"data"=>$data];
        return new Response(json_encode($data,true),200);

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


        $routeManager->removeRoute($route);
        $this->get('session')->getFlashBag()->add('suppression', 'la route a été supprimée');

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
            return $this->redirect(
                $this->generateUrl('tisseo_boa_route_list')
            );
        }
        return (null);
    }
}
