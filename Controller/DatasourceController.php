<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\CoreBundle\Controller\CoreController;
use Tisseo\EndivBundle\Entity\Datasource;
use Tisseo\BoaBundle\Form\Type\DatasourceType;

class DatasourceController extends CoreController
{
    /**
     * List
     *
     * Listing all Datasources
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted(array(
            'BUSINESS_MANAGE_CONFIGURATION',
            'BUSINESS_VIEW_CONFIGURATION'
        ));

        return $this->render(
            'TisseoBoaBundle:Datasource:list.html.twig',
            array(
                'navTitle' => 'tisseo.boa.menu.configuration',
                'pageTitle' => 'tisseo.boa.datasource.title.list',
                'datasources' => $this->get('tisseo_endiv.manager.datasource')->findAll()
            )
        );
    }

    /**
     * Edit
     * @param integer $datasourceId
     *
     * Creating/editing Datasource
     */
    public function editAction(Request $request, $datasourceId)
    {
        $this->denyAccessUnlessGranted('BUSINESS_MANAGE_CONFIGURATION');

        $datasourceManager = $this->get('tisseo_endiv.manager.datasource');
        $datasource = $datasourceManager->find($datasourceId);

        if (empty($datasource))
            $datasource = new Datasource();

        $form = $this->createForm(
            new DatasourceType(),
            $datasource,
            array(
                'action' => $this->generateUrl(
                    'tisseo_boa_datasource_edit',
                    array('datasourceId' => $datasourceId)
                )
            )
        );

        $form->handleRequest($request);
        if ($form->isValid())
        {
            try
            {
                $datasourceManager->save($form->getData());
                $this->addFlash('success', ($datasourceId ? 'tisseo.flash.success.edited' : 'tisseo.flash.success.created'));
            }
            catch (\Exception $e)
            {
                $this->addFlashException($e->getMessage());
            }

            return $this->redirectToRoute('tisseo_boa_datasource_list');
        }

        return $this->render(
            'TisseoBoaBundle:Datasource:form.html.twig',
            array(
                'form' => $form->createView(),
                'title' => ($datasourceId ? 'tisseo.boa.datasource.title.edit' : 'tisseo.boa.datasource.title.create')
            )
        );
    }
}
