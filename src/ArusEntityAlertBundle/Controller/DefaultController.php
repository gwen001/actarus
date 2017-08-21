<?php

namespace ArusEntityAlertBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use ArusEntityAlertBundle\Entity\ArusEntityAlert;
use ArusEntityAlertBundle\Form\ArusEntityAlertAddType;
use ArusEntityAlertBundle\Form\ArusEntityAlertEditType;
use ArusEntityAlertBundle\Form\ArusEntityAlertEditLimitedType;

use ArusEntityAlertBundle\Entity\Search;
use ArusEntityAlertBundle\Form\SearchType;

use Actarus\Utils;


class DefaultController extends Controller
{
	public function indexAction(Request $request)
	{
		$t_level = $this->getParameter('alert')['level'];
		$t_level = array_flip( $t_level );

		$t_status = $this->getParameter('alert')['status'];
		$t_status = array_flip( $t_status );

		$t_entity_type = array_flip( $this->getParameter('entity')['type'] );

		$search = new Search();
		$search->setLevel( 1 );
		$form = $this->createForm( new SearchType(array('t_level'=>$t_level,'t_status'=>$t_status,'t_entity_type'=>$t_entity_type)), $search );
		$form->handleRequest($request);

		$data = null;
		if( $form->isSubmitted() && $form->isValid() )  {
			$data = $form->getData();
		} else {
			$data = $search;
		}

		$page = 1;
		$limit = $this->getParameter('results_per_page');
		$total_alert = $this->get('entity_alert')->search( $data, -1 );
		$n_page = ceil( $total_alert/$limit );

		if( is_array($data) || is_object($data) ) {
			if (is_array($data) && isset($data['page'])) {
				$page = $data['page'];
			} else {
				$page = $data->getPage();
			}
			if ($page <= 0 || $page > $n_page) {
				$page = 1;
			}
		}

		$t_alert = $this->get('entity_alert')->search( $data, $page );
		$pagination = $this->get('app')->paginate( $total_alert, count($t_alert), $page );

		return $this->render('ArusEntityAlertBundle:Default:index.html.twig', array(
			'form' => $form->createView(),
			't_alert' => $t_alert,
			't_entity_type' => $t_entity_type,
			'pagination' => $pagination,
		));
	}


    /**
     * Finds and displays a ArusEntityAlert entity.
     *
     */
    public function showAction(Request $request, ArusEntityAlert $alert)
    {
        $t_level = array_flip( $this->getParameter('alert')['level'] );
        $t_status = array_flip( $this->getParameter('alert')['status'] );
        $t_entity_type = array_flip( $this->getParameter('entity')['type'] );

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ArusEntityAlertBundle:ArusEntityAlert')->getRelatedEntity( $alert, $t_entity_type );
        $alert->setEntity( $entity );
        $alert->setProject( $this->get('app')->getEntityProject($entity) );

        $deleteForm = $this->createDeleteForm($alert);

        return $this->render('ArusEntityAlertBundle:Default:show.html.twig', array(
            'alert' => $alert,
            'entity' => $entity,
            't_level' => $t_level,
            't_status' => $t_status,
            't_entity_type' => $t_entity_type,
            'delete_form' => $deleteForm->createView(),
        ));
    }


    /**
	 * Create a new ArusEntityAlert entity.
	 *
	 */
	public function newAction(Request $request)
	{
		$t_level = array_flip( $this->getParameter('alert')['level'] );
		$t_status = $this->getParameter('alert')['status'];

		$alert = new ArusEntityAlert();
		$form = $this->createForm( new ArusEntityAlertAddType(['t_level'=>$t_level]), $alert );
		$form->handleRequest( $request );

		if ($form->isSubmitted() && $form->isValid()) {
			$this->get('entity_alert')->create( $alert->getEntityId(), $alert->getDescr(), $alert->getLevel(), null, $t_status['confirmed'] );
			//$this->addFlash( 'success', 'New alert added!' );
			$response = new Response( json_encode(array('error'=>0)) );
			return $response;
		}
	}


    /**
     * Displays a form to edit an existing ArusEntityAlert entity.
     *
     */
    public function editAction(Request $request, ArusEntityAlert $alert)
    {
        $em = $this->getDoctrine()->getManager();
        $t_level = array_flip( $this->getParameter('alert')['level'] );
        $t_status = array_flip( $this->getParameter('alert')['status'] );

        if( Utils::isAjax() ) {
            $form = $this->createForm( new ArusEntityAlertEditLimitedType(['t_level'=>$t_level]), $alert );
            $form->handleRequest( $request );

            if ($form->isSubmitted() && $form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($alert);
                $em->flush();

                //$this->addFlash( 'success', 'Your changes were saved!' );

                $response = new Response( json_encode(array('error'=>0)) );
                return $response;
            }
        }
        else
        {
            $deleteForm = $this->createDeleteForm($alert);

            $t_entity_type = array_flip( $this->getParameter('entity')['type'] );
            $entity = $em->getRepository('ArusEntityAlertBundle:ArusEntityAlert')->getRelatedEntity( $alert, $t_entity_type );
	        $alert->setEntity( $entity );
	        $alert->setProject( $this->get('app')->getEntityProject($entity) );

            $form = $this->createForm(new ArusEntityAlertEditType(['t_level'=>$t_level,'t_status'=>$t_status]), $alert );
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $em->persist($alert);
                $em->flush();
                $this->addFlash( 'success', 'Your changes were saved!' );
                return $this->redirectToRoute('alert_show',array('id'=>$alert->getId()));
            }

            return $this->render('ArusEntityAlertBundle:Default:edit.html.twig', array(
                'alert' => $alert,
                'entity' => $entity,
                'form' => $form->createView(),
                'delete_form' => $deleteForm->createView(),
	            't_entity_type' => $t_entity_type,
            ));
        }
    }


    /**
     * Edit an existing ArusEntityAlert entity.
     *
     */
    public function editLimitedAction(Request $request, ArusEntityAlert $alert)
    {
        $t_level = array_flip( $this->getParameter('alert')['level'] );

        $form = $this->createForm( new ArusEntityAlertEditLimitedType(['t_level'=>$t_level]), $alert );
        $form->handleRequest( $request );

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($alert);
            $em->flush();

            //$this->addFlash( 'success', 'Your changes were saved!' );

            $response = new Response( json_encode(array('error'=>0)) );
            return $response;
        }
    }


    /**
	 * Deletes a ArusEntityAlert entity.
	 *
	 */
	public function deleteAction(Request $request, ArusEntityAlert $alert)
	{
        if( Utils::isAjax() ) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($alert);
            $em->flush();
            $response = new Response( json_encode(array('error'=>0)) );
            return $response;
        } else {
            $form = $this->createDeleteForm($alert);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($alert);
                $em->flush();
                $this->addFlash( 'success', 'Alert deleted!' );
            }

            return $this->redirectToRoute('alert_homepage');
        }
	}


    /**
     * Creates a form to delete a ArusEntityAlert entity.
     *
     * @param ArusEntityAlert $alert The ArusEntityAlert entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(ArusEntityAlert $alert)
    {
        return $this->createFormBuilder()
        ->setAction($this->generateUrl('alert_delete', array('id' => $alert->getId())))
        ->setMethod('DELETE')
        ->getForm()
            ;
    }


    /**
     * Confirm a ArusEntityAlert entity.
     *
     */
    public function confirmAction(Request $request, ArusEntityAlert $alert)
    {
        $t_status = $this->getParameter('alert')['status'];

        $em = $this->getDoctrine()->getManager();
        $alert->setStatus( $t_status['confirmed'] );
        $em->persist($alert);
        $em->flush();

        $response = new Response( json_encode(array('error'=>0)) );
        return $response;
    }


	/**
	 * Cancel a ArusEntityAlert entity.
	 *
	 */
	public function cancelAction(Request $request, ArusEntityAlert $alert)
	{
		$t_status = $this->getParameter('alert')['status'];

		$em = $this->getDoctrine()->getManager();
		$alert->setStatus( $t_status['cancelled'] );
		$em->persist($alert);
		$em->flush();

		$response = new Response( json_encode(array('error'=>0)) );
		return $response;
	}


	public function getListAction(Request $request, $entity_id)
	{
		echo $this->get('entity_alert')->getListAction( $entity_id );
		exit();
	}
}
