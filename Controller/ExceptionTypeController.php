<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\EndivBundle\Entity\ExceptionType;
use Tisseo\BoaBundle\Form\Type\ExceptionTypeType;

class ExceptionTypeController extends AbstractController
{
    public function listAction()
    {
        $this->isGranted('BUSINESS_MANAGE_CONFIGURATION');

        $ExceptionTypeManager = $this->get('tisseo_endiv.exception_type_manager');
        return $this->render(
            'TisseoBoaBundle:ExceptionType:list.html.twig',
            array(
                'pageTitle' => 'menu.exception_type',
                'exception_types' => $ExceptionTypeManager->findAll()
            )
        );
    }

    public function editAction(Request $request, $exceptionTypeId)
    {
        $this->isGranted('BUSINESS_MANAGE_CONFIGURATION');

        $ExceptionTypeManager = $this->get('tisseo_endiv.exception_type_manager');
        $form = $this->buildForm($exceptionTypeId, $ExceptionTypeManager);
        $render = $this->processForm($request, $form);
        if (!$render) {
            return $this->render(
                'TisseoBoaBundle:ExceptionType:form.html.twig',
                array(
                    'form' => $form->createView(),
                    'title' => ($exceptionTypeId ? 'exception_type.edit' : 'exception_type.create')
                )
            );
        }
        return ($render);
    }

    private function buildForm($exceptionTypeId, $ExceptionTypeManager)
    {
        $exception_type = $ExceptionTypeManager->find($exceptionTypeId);
        if (empty($exception_type)) {
            $exception_type = new ExceptionType();
        }

        $form = $this->createForm( new ExceptionTypeType(), $exception_type,
            array(
                'action' => $this->generateUrl('tisseo_boa_exception_type_edit',
                                            array('exceptionTypeId' => $exceptionTypeId)
                )
            )
        );

        return ($form);
    }

    private function processForm(Request $request, $form)
    {
        $form->handleRequest($request);
        $ExceptionTypeManager = $this->get('tisseo_endiv.exception_type_manager');
        if ($form->isValid()) {
            $ExceptionTypeManager->save($form->getData());
            $this->get('session')->getFlashBag()->add('success',
                $this->get('translator')->trans(
                    'exception_type.created',
                    array(),
                    'default'
                )
            );
            return $this->redirect(
                $this->generateUrl('tisseo_boa_exception_type_list')
            );
        }
        return (null);
    }

}
