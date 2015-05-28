<?php

namespace Tisseo\BoaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\EndivBundle\Entity\Property;
use Tisseo\BoaBundle\Form\Type\PropertyType;

class PropertyController extends AbstractController
{
    public function listAction()
    {
        $this->isGranted('BUSINESS_MANAGE_PARAMETERS');
        
        $PropertyManager = $this->get('tisseo_endiv.property_manager');
        return $this->render(
            'TisseoBoaBundle:Property:list.html.twig',
            array(
                'pageTitle' => 'menu.property',
                'properties' => $PropertyManager->findAll()
            )
        );
    }
    
    public function editAction(Request $request, $PropertyId)
    {
        $this->isGranted('BUSINESS_MANAGE_PARAMETERS');
        
        $PropertyManager = $this->get('tisseo_endiv.property_manager');
        $property = $PropertyManager->find($PropertyId);
        if (empty($property)) { $property = new Property(); }       
        $form = $this->createForm( new PropertyType(), $property,
            array(
                'action' => $this->generateUrl(
                    'tisseo_boa_property_edit',
                    array('PropertyId' => $PropertyId)
                )
            )
        );

        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                $PropertyManager->save($form->getData());
                
                $this->get('session')->getFlashBag()->add('success',
                    $this->get('translator')->trans(
                        'property.created',
                        array(),
                        'default'
                    )
                );
            } catch(\Exception $e) {
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }

            return $this->redirect(
                $this->generateUrl('tisseo_boa_property_list')
            );
        }

        return $this->render(
            'TisseoBoaBundle:Property:form.html.twig',
            array(
                'form' => $form->createView(),
                'title' => ($PropertyId ? 'property.edit' : 'property.create')
            )
        );
    }
}
