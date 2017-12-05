<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\CoreBundle\Controller\CoreController;
use Tisseo\EndivBundle\Entity\Property;
use Tisseo\BoaBundle\Form\Type\PropertyType;

class PropertyController extends CoreController
{
    /**
     * List
     *
     * Listing all Properties
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted(
            array(
                'BUSINESS_MANAGE_CONFIGURATION',
                'BUSINESS_VIEW_CONFIGURATION'
            )
        );

        return $this->render(
            'TisseoBoaBundle:Property:list.html.twig',
            array(
                'navTitle' => 'tisseo.boa.menu.configuration',
                'pageTitle' => 'tisseo.boa.property.title.list',
                'properties' => $this->get('tisseo_endiv.property_manager')->findAll()
            )
        );
    }

    /**
     * Edit
     *
     * @param int $propertyId
     *
     * Creating/editing Property
     */
    public function editAction(Request $request, $propertyId)
    {
        $this->denyAccessUnlessGranted('BUSINESS_MANAGE_CONFIGURATION');

        $propertyManager = $this->get('tisseo_endiv.property_manager');
        $property = $propertyManager->find($propertyId);

        if (empty($property)) {
            $property = new Property();
        }

        $form = $this->createForm(
            new PropertyType(),
            $property,
            array(
                'action' => $this->generateUrl(
                    'tisseo_boa_property_edit',
                    array('propertyId' => $propertyId)
                )
            )
        );

        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                $propertyManager->save($form->getData());
                $this->addFlash('success', ($propertyId ? 'tisseo.flash.success.edited' : 'tisseo.flash.success.created'));
            } catch (\Exception $e) {
                $this->addFlashException($e->getMessage());
            }

            return $this->redirectToRoute('tisseo_boa_property_list');
        }

        return $this->render(
            'TisseoBoaBundle:Property:form.html.twig',
            array(
                'form' => $form->createView(),
                'title' => ($propertyId ? 'tisseo.boa.property.title.edit' : 'tisseo.boa.property.title.create')
            )
        );
    }
}
