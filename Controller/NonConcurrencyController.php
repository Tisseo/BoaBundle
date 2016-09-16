<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\EndivBundle\Entity\NonConcurrency;
use Tisseo\EndivBundle\Entity\Line;
use Tisseo\CoreBundle\Controller\CoreController;
use Tisseo\BoaBundle\Form\Type\NonConcurrencyType;

class NonConcurrencyController extends CoreController
{
    /**
     * List
     *
     * Listing all NonConcurrencies
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted(
            array(
                'BUSINESS_MANAGE_ROUTES',
                'BUSINESS_VIEW_ROUTES'
            )
        );

        return $this->render(
            'TisseoBoaBundle:NonConcurrency:list.html.twig',
            array(
                'navTitle' => 'tisseo.boa.menu.transport.manage',
                'pageTitle' => 'tisseo.boa.non_concurrency.title.list',
                'nonConcurrencies' => $this->get('tisseo_endiv.non_concurrency_manager')->findAll(),
                'lines' => $this->get('tisseo_endiv.line_manager')->findAllLinesByPriority(),
            )
        );
    }

    public function deleteAction($nonConcurrencyId)
    {
        $this->denyAccessUnlessGranted(
            array(
                'BUSINESS_MANAGE_ROUTES',
                'BUSINESS_VIEW_ROUTES'
            )
        );

        try {
            $nonConcurrency = $this->get('tisseo_endiv.non_concurrency_manager')->findById($nonConcurrencyId);
            if (!empty($nonConcurrency))
                $this->get('tisseo_endiv.non_concurrency_manager')->delete($nonConcurrency);
        } catch(\Exception $e) {
            $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
        }

        return $this->redirect(
            $this->generateUrl('tisseo_boa_non_concurrency_list')
        );
    }

    /**
     * Edit
     * @param integer $priorityLineId, nonPriorityLineId
     *
     * Creating/editing NonConcurrency
     */
    public function editAction(Request $request, $nonConcurrencyId)
    {
        $this->denyAccessUnlessGranted('BUSINESS_MANAGE_ROUTES');

        $nonConcurrencyManager = $this->get('tisseo_endiv.non_concurrency_manager');
        $nonConcurrency = $nonConcurrencyManager->findById($nonConcurrencyId);

        if (empty($nonConcurrency))
            $nonConcurrency = new NonConcurrency();

        $form = $this->createForm(
            new NonConcurrencyType(),
            $nonConcurrency,
            array(
                'action' => $this->generateUrl(
                    'tisseo_boa_non_concurrency_edit',
                    array(
                        'nonConcurrencyId' => $nonConcurrencyId,
                    )
                )
            )
        );

        $form->handleRequest($request);
        if ($form->isValid())
        {
            try
            {
                $nonConcurrencyManager->save($form->getData());
                $this->addFlash('success', ($nonConcurrencyId ? 'tisseo.flash.success.edited' : 'tisseo.flash.success.created'));
            }
            catch (\Exception $e)
            {
                $this->addFlashException($e->getMessage());
            }

            return $this->redirectToRoute('tisseo_boa_non_concurrency_list');
        }

        return $this->render(
            'TisseoBoaBundle:NonConcurrency:form.html.twig',
            array(
                'form' => $form->createView(),
                'title' => ($nonConcurrencyId ? 'tisseo.boa.non_concurrency.title.edit' : 'tisseo.boa.non_concurrency.title.create'),
                'lines' => $this->get('tisseo_endiv.line_manager')->findAllLinesByPriority(),
            )
        );
    }

}
