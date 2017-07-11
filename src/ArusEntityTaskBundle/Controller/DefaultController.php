<?php

namespace ArusEntityTaskBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use ArusEntityTaskBundle\Entity\ArusEntityTask;
use ArusEntityTaskBundle\Form\ArusEntityTaskAddType;
use ArusEntityTaskBundle\Form\ArusEntityTaskEditType;

use ArusEntityTaskBundle\Entity\Search;
use ArusEntityTaskBundle\Form\SearchType;

use Actarus\Utils;


class DefaultController extends Controller
{
	public function indexAction(Request $request)
	{
		$t_cluster = $this->getParameter( 'daemon' )['cluster'];

		$t_status = array_flip($this->getParameter('task')['status']);
		$t_entity_type = array_flip( $this->getParameter('entity')['type'] );

		$search = new Search();
		$search->setStatus( 0 );
		$form = $this->createForm(new SearchType(['t_status'=>$t_status,'t_entity_type'=>$t_entity_type,'t_cluster'=>$t_cluster]), $search);
		$form->handleRequest($request);

		$data = null;
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
		} else {
			$data = $search;
		}

		$page = 1;
		$limit = $this->getParameter('results_per_page');
		$total_task = $this->get('entity_task')->search( $data, -1 );
		$n_page = ceil( $total_task/$limit );

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

		$t_task = $this->get('entity_task')->search( $data, $page );
		$pagination = $this->get('app')->paginate( $total_task, count($t_task), $page );

		return $this->render('ArusEntityTaskBundle:Default:index.html.twig', array(
			'form' => $form->createView(),
			't_task' => $t_task,
			't_status' => $t_status,
			't_cluster' => $t_cluster,
			't_entity_type' => $t_entity_type,
			'pagination' => $pagination,
		));
	}


	/**
	 * Finds and displays a ArusEntityTask entity.
	 *
	 */
	public function showAction(Request $request, ArusEntityTask $task)
	{
		$t_status = array_flip( $this->getParameter('task')['status'] );
		$t_entity_type = array_flip( $this->getParameter('entity')['type'] );
		$t_cluster = $this->getParameter( 'daemon' )['cluster'];

		$em = $this->getDoctrine()->getManager();

		$entity = $em->getRepository('ArusEntityTaskBundle:ArusEntityTask')->getRelatedEntity( $task, $t_entity_type );
		$task->setEntity( $entity );
        $task->setProject( $this->get('app')->getEntityProject($entity) );

        $deleteForm = $this->createDeleteForm($task);
		$stopForm = $this->createStopForm($task);

		return $this->render('ArusEntityTaskBundle:Default:show.html.twig', array(
			'task' => $task,
			'entity' => $entity,
			't_status' => $t_status,
			't_entity_type' => $t_entity_type,
			't_cluster' => $t_cluster,
			'delete_form' => $deleteForm->createView(),
			'stop_form' => $stopForm->createView(),
		));
	}


	/**
	 * Finds and displays a ArusEntityTask entity.
	 *
	 */
	public function viewAction(Request $request, ArusEntityTask $task)
	{
		$em = $this->getDoctrine()->getManager();
		$t_status = array_flip( $this->getParameter('task')['status'] );
		$t_entity_type = array_flip( $this->getParameter('entity')['type'] );

		$entity = $em->getRepository('ArusEntityTaskBundle:ArusEntityTask')->getRelatedEntity( $task, $t_entity_type );
		$task->setEntity( $entity );
        $task->setProject( $this->get('app')->getEntityProject($entity) );

		return $this->render('ArusEntityTaskBundle:Default:view.html.twig', array(
			'task' => $task,
			't_status' => $t_status,
			't_entity_type' => $t_entity_type,
		));
	}


	/**
	 * Create a new ArusEntityTask entity.
	 *
	 */
	public function newAction(Request $request)
	{
		$r = ['error'=>0];

		$task = new ArusEntityTask();
		$form = $this->createForm( new ArusEntityTaskAddType(), $task );
		$form->handleRequest( $request );

		if ($form->isSubmitted() && $form->isValid()) {
			$this->get('entity_task')->create( $task->getEntityId(), $task->getTask()->getName(), [], $task->getCommand(), $task->getPriority() );
			$this->addFlash( 'success', 'New task added!' );
			return $this->redirect( $request->server->get('HTTP_REFERER') );
		}
		else {
			$r['error'] = 1;
		}

//		$response = new Response( json_encode($r) );
//		return $response;
		$this->addFlash( 'success', 'New task added!' );
		return $this->redirect( $request->server->get('HTTP_REFERER') );
	}


	/**
	 * Displays a form to edit an existing ArusEntityTask entity.
	 *
	 */
	public function editAction(Request $request, ArusEntityTask $task)
	{
		$em = $this->getDoctrine()->getManager();
		$t_status = array_flip( $this->getParameter('task')['status'] );
		$t_entity_type = array_flip( $this->getParameter('entity')['type'] );
		$t_cluster = $this->getParameter( 'daemon' )['cluster'];

		$deleteForm = $this->createDeleteForm($task);
		$stopForm = $this->createStopForm($task);

		$entity = $em->getRepository('ArusEntityTaskBundle:ArusEntityTask')->getRelatedEntity( $task, $t_entity_type );
		$task->setEntity( $entity );
        $task->setProject( $this->get('app')->getEntityProject($entity) );

		$form = $this->createForm(new ArusEntityTaskEditType(['t_status'=>$t_status]), $task );
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$em->persist($task);
			$em->flush();
			$this->addFlash( 'success', 'Your changes were saved!' );
			return $this->redirectToRoute('task_show',array('id'=>$task->getId()));
		}

		return $this->render('ArusEntityTaskBundle:Default:edit.html.twig', array(
			'task' => $task,
			'entity' => $entity,
			'form' => $form->createView(),
			'delete_form' => $deleteForm->createView(),
			'stop_form' => $stopForm->createView(),
			't_entity_type' => $t_entity_type,
			't_cluster' => $t_cluster,
		));
	}


	/**
	 * Show a ArusEntityTask entity.
	 *
	 */
	public function unitAction(Request $request, ArusEntityTask $task)
	{
		return $this->render('ArusEntityTaskBundle:Default:unit.html.twig', array(
			't' => $task,
		));
	}


	/**
	 * Stop a ArusEntityTask entity.
	 *
	 */
	public function stopAction(Request $request, ArusEntityTask $task)
	{
		$this->get('entity_task')->stop( $task );

		if( Utils::isAjax() ) {
			$response = new Response( json_encode(array('error'=>0)) );
			return $response;
		} else {
			$this->addFlash( 'success', 'Task stopped!' );
			return $this->redirectToRoute( 'task_show', ['id'=>$task->getId()] );
		}
	}


	/**
	 * Interpret a ArusEntityTask entity.
	 *
	 */
	public function interpretAction(Request $request, ArusEntityTask $task)
	{
		$t_status = $this->getParameter('task')['status'];

		$task->setStatus( $t_status['finished'] );

		$em = $this->getDoctrine()->getManager();
		$em->persist( $task );
		$em->flush();

		$response = new Response( json_encode(array('error'=>0)) );
		return $response;
	}


	/**
	 * Deletes a ArusEntityTask entity.
	 *
	 */
	public function deleteAction(Request $request, ArusEntityTask $task)
	{
		if( Utils::isAjax() ) {
			$this->get('entity_task')->delete( $task );
			$response = new Response( json_encode(array('error'=>0)) );
			return $response;
		} else {
			$form = $this->createDeleteForm($task);
			$form->handleRequest($request);

			if ($form->isSubmitted() && $form->isValid()) {
				$this->get('entity_task')->delete( $task );
				$this->addFlash( 'success', 'Task deleted!' );
			}

			return $this->redirectToRoute('task_homepage');
		}
	}


	/**
	 * Creates a form to delete a ArusEntityTask entity.
	 *
	 * @param ArusEntityTask $task The ArusEntityTask entity
	 *
	 * @return \Symfony\Component\Form\Form The form
	 */
	private function createDeleteForm(ArusEntityTask $task)
	{
		return $this->createFormBuilder()
		->setAction($this->generateUrl('task_delete', array('id' => $task->getId())))
		->setMethod('DELETE')
		->getForm()
			;
	}


	/**
	 * Creates a form to stop a ArusEntityTask entity.
	 *
	 * @param ArusEntityTask $task The ArusEntityTask entity
	 *
	 * @return \Symfony\Component\Form\Form The form
	 */
	private function createStopForm(ArusEntityTask $task)
	{
		return $this->createFormBuilder()
		->setAction($this->generateUrl('task_stop', array('id' => $task->getId())))
		->setMethod('POST')
		->getForm()
			;
	}


	public function getListAction(Request $request, $entity_id)
	{
		echo $this->get('entity_task')->getListAction( $entity_id );
		exit();
	}
}
