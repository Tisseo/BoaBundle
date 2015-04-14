<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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

        $stops = $stopManager->findAll();
       
        if(isset($id) && ($request->getMethod()) == "POST") {
            $routes = $routeManager->findAllByLine($id);
        }

      return $this->render("TisseoBoaBundle:Route:route.html.twig", array(
           'pageTitle' => 'création de routes',
           'routes' => $routes,
           'stops'=>$stops,
          'id' => $id
       ));

    }

    public function editAction(Request $request) {

        $id = $request->get('id');
        $routeManager = $this->get('tisseo_endiv.route_manager');
        $route= $routeManager->findById($id);

        $stopsManager = $this->get('tisseo_endiv.stop_manager');
        $stops = $stopsManager->getStopsByRoute($id);
        $sizeStops = sizeof($stops);
        $departure = "";
        $arrival ="";
        foreach($stops as $stop) {
            if($stop["rank"] == 1) {
                $departure = $stopsManager->find($stop["waypoint"]);

            }
            if($stop["rank"] == $sizeStops) {
                $arrival = $stopsManager->find($stop["waypoint"]);

            }
        }

        if(!$route) {
            throw $this->createNotFoundException('route non trouvée');
        }

        $formBuilder = $this->get('form.factory')->createBuilder('form', $route);
        $formBuilder->add('name', 'text')
                    ->add('way', 'text',  array('label' => 'Sens'))
                    ->add('direction', 'text',  array('label' => 'Arrivée'))
                    ->add('line_version_id', 'text',  array('label' => 'Commentaire'))
                    ->add('modifier', 'submit');
        $form = $formBuilder->getForm();

            return $this->render(
                'TisseoBoaBundle:Route:edit.html.twig',
                array(
                    'form' => $form->createView(),
                    'pageTitle' => 'modification de route',
                    'route' => $route,
                    'stops' => $stops,
                    'departure' => $departure,
                    'arrival' => $arrival,
                )
            );




    }

    public function createAction(Request $request){

        $route = new Route();
        $lineVersionManager = $this->get('tisseo_endiv.line_version_manager');
        if(isset($request)) {
            $lineVersion = $lineVersionManager->find($request->get('idLine'));
            $route->setLineVersion($lineVersion);

        }
        $routeManager = $this->get('tisseo_endiv.route_manager');
        $form = $this->createForm( new RouteType($routeManager), $route);
        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                $routeManager->save($form->getData());
                
            } catch(\Exception $e) {
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }
        }

       return $this->render("TisseoBoaBundle:Route:create.html.twig", array(
            "form" =>$form->createView(),
            'title' => 'Creation de route',
            'lineVersion' => $request->get('idLine')
        ));
    }


    private function buildForm($RouteId, $RouteManager)
    {
        $Route = $RouteManager->findById($RouteId);
        if (empty($Route)) {
            $Route = new Route();
        }


        $form = $this->createForm( new RouteType(), $Route,
            array(
                'action' => $this->generateUrl('tisseo_boa_route_edit',
                    array('id' => $RouteId)
                ),
                'method' => 'POST'
            )
        );

        return ($form);
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
