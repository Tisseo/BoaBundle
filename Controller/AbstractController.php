<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractController extends Controller
{
    protected function isGranted($businessId)
    {
        if ($this->get('security.context')->isGranted($businessId) === false)
            throw new AccessDeniedException();
    }

    protected function isPostAjax(Request $request)
    {
        if (!($request->isXmlHttpRequest() && $request->isMethod('POST')))
            throw new AccessDeniedException();
    }

    // TODO: This is ugly, change it
    // SEE TODO IN ENDIVBUNDLE
    protected function buildDefaultDatasource($datasource)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $datasource->setCode($user->getUsername());
        $datasource->setDatasource($this->get('tisseo_endiv.datasource_manager')->findDefaultDatasource());
    }
}
