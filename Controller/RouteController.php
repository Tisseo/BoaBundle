<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\DateTime;
use Tisseo\EndivBundle\Entity\Route;

class RouteController extends AbstractController
{
    public function listAction(Request $request)
    {
        $ligneVersionManager = $this->get('tisseo_endiv.line_version_manager');



        $lineManager = $this->get('tisseo_endiv.line_manager');

        $linesVManager = $this->get('tisseo_endiv.line_version_manager');
        $time = new \DateTime('now');
        $time = $time->format('Y-m-d H:i:s');


        $allLines = $linesVManager->findLineVersions($time);

        return $this->render(
            'TisseoBoaBundle:Route:list.html.twig',
            array(
                'pageTitle' => 'selection d\'une ligne',
                'linesV' => $allLines
                       )
        );
        //return new Response("routes", 200);//$this->render('TisseoBoaBundle:Routes:index.html.twig');
    }

    public function routeAction(Request $request) {
        $id = $request->request->get("lineId");
        $idRoute = $request->get('idroute');
        $routeManager = $this->get('tisseo_endiv.route_manager');
        $stopManager = $this->get('tisseo_endiv.stop_manager');

        $routes = $routeManager->findAllByLine($idRoute);
        $stops = $stopManager->findAll();
       
        if(isset($id) && ($request->getMethod()) == "POST") {
            $routes = $routeManager->findAllByLine($id);
        }

      return $this->render("TisseoBoaBundle:Route:route.html.twig", array(
           'pageTitle' => 'création de routes',
           'routes' => $routes,
           'stops'=>$stops
       ));

    }
}
