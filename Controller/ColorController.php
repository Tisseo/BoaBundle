<?php

namespace Tisseo\BOABundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Tisseo\EndivBundle\Entity\Color;
use Tisseo\BOABundle\Form\Type\ColorType;

class ColorController extends AbstractController
{
    public function listAction()
    {
        $this->isGranted('BUSINESS_MANAGE_PARAMETERS');
		
		$ColorManager = $this->get('tisseo_endiv.color_manager');
        return $this->render(
            'TisseoBOABundle:Color:list.html.twig',
            array(
                'pageTitle' => 'menu.color',
                'colors' => $ColorManager->findAll()
            )
        );
    }
	
    public function editAction(Request $request, $ColorId)
    {
        $this->isGranted('BUSINESS_MANAGE_PARAMETERS');
		
		$ColorManager = $this->get('tisseo_endiv.color_manager');
		$form = $this->buildForm($ColorId, $ColorManager);
        $render = $this->processForm($request, $form);
		if (!$render) {
            return $this->render(
                'TisseoBOABundle:Color:form.html.twig',
                array(
                    'form' => $form->createView(),
                    'title' => ($ColorId ? 'color.edit' : 'color.create')
                )
            );
        }
        return ($render);
    }

    private function buildForm($ColorId, $ColorManager)
    {
        $color = $ColorManager->find($ColorId);
        if (empty($color)) {
            $color = new Color();
        }		

        $form = $this->createForm( new ColorType(), $color,
            array(
                'action' => $this->generateUrl('tisseo_boa_color_edit',
											array('ColorId' => $ColorId)
                )
            )
        );
		
		return ($form);
    }	

    private function processForm(Request $request, $form)
    {
        $form->handleRequest($request);
        $ColorManager = $this->get('tisseo_endiv.color_manager');
        if ($form->isValid()) {
            $ColorManager->save($form->getData());
            $this->get('session')->getFlashBag()->add('success',
                $this->get('translator')->trans(
                    'color.created',
                    array(),
                    'default'
                )
            );
            return $this->redirect(
                $this->generateUrl('tisseo_boa_color_list')
            );
        }
        return (null);
    }

}
