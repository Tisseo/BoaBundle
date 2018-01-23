<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\CoreBundle\Controller\CoreController;
use Tisseo\EndivBundle\Entity\ExceptionType;
use Tisseo\BoaBundle\Form\Type\ExceptionTypeType;

class ExceptionTypeController extends CoreController
{
    /**
     * List
     *
     * Listing all ExceptionTypes
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted(array(
            'BUSINESS_MANAGE_CONFIGURATION',
            'BUSINESS_VIEW_CONFIGURATION'
        ));

        return $this->render(
            'TisseoBoaBundle:ExceptionType:list.html.twig',
            array(
                'navTitle' => 'tisseo.boa.menu.configuration',
                'pageTitle' => 'tisseo.boa.exception_type.title.list',
                'exceptionTypes' => $this->get('tisseo_endiv.exception_type_manager')->findAll()
            )
        );
    }

    /**
     * Edit
     *
     * @param int $exceptionTypeId
     *
     * Creating/editing ExceptionType
     */
    public function editAction(Request $request, $exceptionTypeId)
    {
        $this->denyAccessUnlessGranted('BUSINESS_MANAGE_CONFIGURATION');

        $exceptionTypeManager = $this->get('tisseo_endiv.exception_type_manager');
        $exceptionType = $exceptionTypeManager->find($exceptionTypeId);

        if (empty($exceptionType)) {
            $exceptionType = new ExceptionType();
        }

        $form = $this->createForm(
            new ExceptionTypeType(),
            $exceptionType,
            array(
                'action' => $this->generateUrl(
                    'tisseo_boa_exception_type_edit',
                    array('exceptionTypeId' => $exceptionTypeId)
                )
            )
        );

        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                $exceptionTypeManager->save($form->getData());
                $this->addFlash('success', ($exceptionTypeId ? 'tisseo.flash.success.edited' : 'tisseo.flash.success.created'));
            } catch (\Exception $e) {
                $this->addFlashException($e->getMessage());
            }

            return $this->redirectToRoute('tisseo_boa_exception_type_list');
        }

        return $this->render(
            'TisseoBoaBundle:ExceptionType:form.html.twig',
            array(
                'form' => $form->createView(),
                'title' => ($exceptionTypeId ? 'tisseo.boa.exception_type.title.edit' : 'tisseo.boa.exception_type.title.create')
            )
        );
    }
}
