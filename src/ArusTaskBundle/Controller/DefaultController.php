<?php

namespace ArusTaskBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;

use ArusProjectBundle\Entity\ArusProject;
use ArusDomainBundle\Entity\ArusDomain;
use ArusHostBundle\Entity\ArusHost;
use ArusServerBundle\Entity\ArusServer;
use ArusRequestBundle\Entity\ArusRequest;

use ArusTaskBundle\Entity\ArusTask;
use ArusTaskBundle\Form\ArusTaskType;

use ArusTaskCallbackBundle\Entity\ArusTaskCallback;
use ArusTaskCallbackBundle\Form\ArusTaskCallbackType;


class DefaultController extends Controller
{
	public function indexAction()
	{
		$em = $this->getDoctrine()->getManager();
        $bin_path = $this->getParameter('bin_path');
        $t_task = $em->getRepository('ArusTaskBundle:ArusTask')->findAll();

		foreach( $t_task as $t ) {
			$tmp = explode( ' ', $t->getCommand() );
			$t->setBinaryExists( (int)file_exists($bin_path.'/'.$tmp[0]) );
		}

		return $this->render('ArusTaskBundle:Default:index.html.twig', array('t_task'=>$t_task));
	}


	/**
	 * Creates a new Task entity.
	 *
	 */
	public function newAction(Request $request)
	{
		$task = new ArusTask();
		$form = $this->createForm( new ArusTaskType(), $task );
		$form->handleRequest( $request );

		if ($form->isSubmitted() && $form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$exist = $em->getRepository('ArusTaskBundle:ArusTask')->findByName( $task->getName() );
			if( !$exist ) {
				$t_entities = [];
				if( isset($_POST['entities_project']) ) {
					$t_entities[ArusProject::ENTITY_TYPE_ID] = ArusProject::ENTITY_TYPE_ID;
				} else {
					$t_entities[ArusProject::ENTITY_TYPE_ID] = 0;
				}
				if( isset($_POST['entities_domain']) ) {
					$t_entities[ArusDomain::ENTITY_TYPE_ID] = ArusDomain::ENTITY_TYPE_ID;
				} else {
					$t_entities[ArusDomain::ENTITY_TYPE_ID] = 0;
				}
				if( isset($_POST['entities_host']) ) {
					$t_entities[ArusHost::ENTITY_TYPE_ID] = ArusHost::ENTITY_TYPE_ID;
				} else {
					$t_entities[ArusHost::ENTITY_TYPE_ID] = 0;
				}
				if( isset($_POST['entities_server']) ) {
					$t_entities[ArusServer::ENTITY_TYPE_ID] = ArusServer::ENTITY_TYPE_ID;
				} else {
					$t_entities[ArusServer::ENTITY_TYPE_ID] = 0;
				}
				if( isset($_POST['entities_request']) ) {
					$t_entities[ArusRequest::ENTITY_TYPE_ID] = ArusRequest::ENTITY_TYPE_ID;
				} else {
					$t_entities[ArusRequest::ENTITY_TYPE_ID] = 0;
				}
				$task->setEntities( $t_entities );
				$em->persist($task);
				$em->flush();
				$this->addFlash('success', 'New task added!');
				return $this->redirectToRoute('settings_task_show', array('id' => $task->getId()));
			} else {
				$form->get('name')->addError( new FormError('This task already exist') );
			}
		}

		return $this->render('ArusTaskBundle:Default:new.html.twig', array(
			'task' => $task,
			'form' => $form->createView(),
		));
	}


	/**
	 * Finds and displays a Task entity.
	 *
	 */
	public function showAction(Request $request, ArusTask $task)
	{
		$em = $this->getDoctrine()->getManager();
		$deleteForm = $this->createDeleteForm($task);

		$callback_mod = $this->get('task_callback')->getModAction( $task );
		$callback_add_mod = $this->get('task_callback')->addMod( $task );

		$callbackEditForm = $this->createForm( new ArusTaskCallbackType() );

		return $this->render('ArusTaskBundle:Default:show.html.twig', array(
			'task' => $task,
			'delete_form' => $deleteForm->createView(),
			'callback_mod' => $callback_mod,
			'callback_add_mod' => $callback_add_mod,
			'callback_edit_form' => $callbackEditForm->createView(),
		));
	}


	/**
	 * Finds and displays a Task entity.
	 *
	 */
	public function viewAction(Request $request, ArusTask $task)
	{
		return $this->render('ArusTaskBundle:Default:view.html.twig', array(
			'task' => $task,
		));
	}


	/**
	 * Displays a form to edit an existing Task entity.
	 *
	 */
	public function editAction(Request $request, ArusTask $task)
	{
		$form = $this->createForm( new ArusTaskType(), $task, ['action'=>$this->generateUrl('settings_task_edit',['id'=>$task->getId()])] );
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$exist = $em->getRepository('ArusTaskBundle:ArusTask')->findByName( $task->getName() );
			if( !$exist || (count($exist) && $exist[0]->getId()==$task->getId()) ) {
				$t_entities = [];
				if( isset($_POST['entities_project']) ) {
					$t_entities[ArusProject::ENTITY_TYPE_ID] = ArusProject::ENTITY_TYPE_ID;
				} else {
					$t_entities[ArusProject::ENTITY_TYPE_ID] = 0;
				}
				if( isset($_POST['entities_domain']) ) {
					$t_entities[ArusDomain::ENTITY_TYPE_ID] = ArusDomain::ENTITY_TYPE_ID;
				} else {
					$t_entities[ArusDomain::ENTITY_TYPE_ID] = 0;
				}
				if( isset($_POST['entities_host']) ) {
					$t_entities[ArusHost::ENTITY_TYPE_ID] = ArusHost::ENTITY_TYPE_ID;
				} else {
					$t_entities[ArusHost::ENTITY_TYPE_ID] = 0;
				}
				if( isset($_POST['entities_server']) ) {
					$t_entities[ArusServer::ENTITY_TYPE_ID] = ArusServer::ENTITY_TYPE_ID;
				} else {
					$t_entities[ArusServer::ENTITY_TYPE_ID] = 0;
				}
				if( isset($_POST['entities_request']) ) {
					$t_entities[ArusRequest::ENTITY_TYPE_ID] = ArusRequest::ENTITY_TYPE_ID;
				} else {
					$t_entities[ArusRequest::ENTITY_TYPE_ID] = 0;
				}
				$task->setEntities( $t_entities );
				$em->persist($task);
				$em->flush();
				$this->addFlash( 'success', 'Your changes were saved!' );
			}
			return $this->redirectToRoute('settings_task_show',array('id'=>$task->getId()));
		}

		return $this->render('ArusTaskBundle:Default:edit.html.twig', array(
			'task' => $task,
			'form' => $form->createView(),
		));
	}


	/**
	 * Deletes a Task entity.
	 *
	 */
	public function deleteAction(Request $request, ArusTask $task)
	{
		$form = $this->createDeleteForm($task);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$em->remove($task);
			$em->flush();

			$this->addFlash( 'success', 'Task deleted!' );
		}

		return $this->redirectToRoute('settings_task');
	}


	/**
	 * Creates a form to delete a Task entity.
	 *
	 * @param ArusTask $task The Task entity
	 *
	 * @return \Symfony\Component\Form\Form The form
	 */
	private function createDeleteForm(ArusTask $task)
	{
		return $this->createFormBuilder()
		->setAction($this->generateUrl('settings_task_delete', array('id' => $task->getId())))
		->setMethod('DELETE')
		->getForm()
			;
	}
}
