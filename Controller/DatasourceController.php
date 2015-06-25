<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\EndivBundle\Entity\Datasource;
use Tisseo\BoaBundle\Form\Type\DatasourceType;

class DatasourceController extends AbstractController
{
    public function listAction()
    {
        $this->isGranted(
            array(
                'BUSINESS_MANAGE_CONFIGURATION',
                'BUSINESS_VIEW_CONFIGURATION'
            )    
        );

        $DatasourceManager = $this->get('tisseo_endiv.datasource_manager');
        return $this->render(
            'TisseoBoaBundle:Datasource:list.html.twig',
            array(
                'pageTitle' => 'menu.datasource',
                'datasources' => $DatasourceManager->findAll()
            )
        );
    }

    public function editAction(Request $request, $datasourceId)
    {
        $this->isGranted('BUSINESS_MANAGE_CONFIGURATION');

        $DatasourceManager = $this->get('tisseo_endiv.datasource_manager');
        $form = $this->buildForm($datasourceId, $DatasourceManager);
        $render = $this->processForm($request, $form);
        if (!$render) {
            return $this->render(
                'TisseoBoaBundle:Datasource:form.html.twig',
                array(
                    'form' => $form->createView(),
                    'title' => ($datasourceId ? 'datasource.edit' : 'datasource.create')
                )
            );
        }
        return ($render);
    }

    private function buildForm($datasourceId, $DatasourceManager)
    {
        $datasource = $DatasourceManager->find($datasourceId);
        if (empty($datasource)) {
            $datasource = new Datasource();
        }

        $form = $this->createForm( new DatasourceType(), $datasource,
            array(
                'action' => $this->generateUrl('tisseo_boa_datasource_edit',
                                            array('datasourceId' => $datasourceId)
                )
            )
        );

        return ($form);
    }

    private function processForm(Request $request, $form)
    {
        $form->handleRequest($request);
        $DatasourceManager = $this->get('tisseo_endiv.datasource_manager');
        if ($form->isValid()) {
            $DatasourceManager->save($form->getData());
            $this->get('session')->getFlashBag()->add('success',
                $this->get('translator')->trans(
                    'datasource.created',
                    array(),
                    'default'
                )
            );
            return $this->redirect(
                $this->generateUrl('tisseo_boa_datasource_list')
            );
        }
        return (null);
    }
}
