<?php

namespace ArusProjectBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use ArusProjectBundle\Entity\ArusProject;
use ArusProjectBundle\Form\ArusProjectAddType;
use ArusProjectBundle\Form\ArusProjectEditType;
use ArusProjectBundle\Form\ArusProjectQuickEditType;

use ArusServerBundle\Entity\Search;

use ArusCommentBundle\Entity\ArusComment;
use ArusCommentBundle\Form\ArusCommentType;


class DefaultController extends Controller
{
    public function indexAction()
    {
        $t_status = $this->getParameter('project')['status'];

		$em = $this->getDoctrine()->getManager();

		$t_project = $this->get('project')->search( [], 1, 500 );
		foreach( $t_project as $p ) {
			$p->setAlerts( $this->get('entity_alert')->search(['project'=>$p]) );
			$p->setEntityAlerts( $this->get('entity_alert')->search(['entity_id'=>$p->getEntityId()]) );
		}

		return $this->render('ArusProjectBundle:Default:index.html.twig', array(
            't_project'=>$t_project,
            't_status' => $t_status,
        ));
    }


	/**
	 * Finds and displays a Project entity.
	 *
	 */
	public function showAction(Request $request, ArusProject $project)
	{
        $t_status = $this->getParameter('project')['status'];
		$quick_edit = $this->createForm(new ArusProjectQuickEditType(['t_status'=>$t_status]), $project, ['action'=>$this->generateUrl('project_quickedit',['id'=>$project->getId()])] );

		$deleteForm = $this->createDeleteForm($project);

		$alert_mod = $this->get('entity_alert')->getModAction( $project );
		$task_mod = $this->get('entity_task')->getModAction( $project );

        $t_server = $this->get('server')->search( ['project'=>$project] );
        foreach( $t_server as $s ) {
            $s->setEntityAlerts( $this->get('entity_alert')->search(['entity_id'=>$s->getEntityId()]) );
        }

        $t_domain = $this->get('domain')->search( ['project'=>$project] );
        foreach( $t_domain as $d ) {
            $d->setEntityAlerts( $this->get('entity_alert')->search(['entity_id'=>$d->getEntityId()]) );
        }

		return $this->render('ArusProjectBundle:Default:show.html.twig', array(
			'project' => $project,
			'delete_form' => $deleteForm->createView(),
			'alert_mod' => $alert_mod,
			'task_mod' => $task_mod,
            't_status' => $t_status,
            't_server' => $t_server,
            't_domain' => $t_domain,
            'quick_edit' => $quick_edit->createView(),
		));
	}


	/**
	 * Creates a new Project entity.
	 *
	 */
	public function newAction(Request $request)
	{
		$project = new ArusProject();
		$form = $this->createForm(new ArusProjectAddType(), $project);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$exist = $this->get('project')->exist( $project->getName() );
			if( !$exist ) {
				$project = $this->get('project')->create( strtolower($project->getName()), $project->getRecon() );
				$this->addFlash('success', 'New project added!');
				return $this->redirectToRoute('project_show', array('id' => $project->getId()));
			} else {
				$form->get('name')->addError( new FormError('This project already exist') );
			}
		}

		return $this->render('ArusProjectBundle:Default:new.html.twig', array(
			'project' => $project,
			'form' => $form->createView(),
		));
	}


	/**
	 * Finds and displays a Project entity.
	 *
	 */
	public function viewAction(Request $request, ArusProject $project)
	{
        $t_status = $this->getParameter('project')['status'];
		$quick_edit = $this->createForm(new ArusProjectQuickEditType(['t_status'=>$t_status]), $project, ['action'=>$this->generateUrl('project_quickedit',['id'=>$project->getId()])] );

		return $this->render('ArusProjectBundle:Default:view.html.twig', array(
			'project' => $project,
            't_status' => $t_status,
            'quick_edit' => $quick_edit->createView(),
		));
	}


	/**
	 * Displays a form to edit an existing Project entity.
	 *
	 */
	public function quickeditAction(Request $request, ArusProject $project)
	{
		$r = ['error'=>0];
        $t_status = $this->getParameter('project')['status'];

		$form = $this->createForm( new ArusProjectQuickEditType(['t_status'=>$t_status]), $project, ['action'=>$this->generateUrl('project_quickedit',['id'=>$project->getId()])] );
		$form->handleRequest($request);

		if( $form->isSubmitted() && $form->isValid() ) {
			$em = $this->getDoctrine()->getManager();
			$em->persist( $project );
			$em->flush();
		}

		$response = new Response( json_encode($r) );
		return $response;
	}


	/**
	 * Displays a form to edit an existing Project entity.
	 *
	 */
	public function editAction(Request $request, ArusProject $project)
	{
        $t_status = $this->getParameter('project')['status'];

		$form = $this->createForm( new ArusProjectEditType(['t_status'=>$t_status]), $project, ['action'=>$this->generateUrl('project_edit',['id'=>$project->getId()])] );
		$form->handleRequest($request);

		if( $form->isSubmitted() && $form->isValid() ) {
			$exist = $this->get('project')->exist( $project->getName(), $project->getId() );
			if( !$exist ) {
				$em = $this->getDoctrine()->getManager();
				$em->persist( $project );
				$em->flush();
				$this->addFlash( 'success', 'Your changes were saved!' );
			} else {
				$this->addFlash( 'danger', 'Error!' );
			}
			return $this->redirectToRoute('project_show',array('id'=>$project->getId()));
		}

		return $this->render('ArusProjectBundle:Default:edit.html.twig', array(
			'project' => $project,
			'form' => $form->createView(),
            't_status' => $t_status,
		));
	}


	/**
	 * Deletes a Project entity.
	 *
	 */
	public function deleteAction(Request $request, ArusProject $project)
	{
		$form = $this->createDeleteForm($project);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->get('app')->entityDelete( $project );
			$em = $this->getDoctrine()->getManager();
			$em->remove($project);
			$em->flush();

			$this->addFlash( 'success', 'Project deleted!' );
		}

		return $this->redirectToRoute('project_homepage');
	}


	/**
	 * Creates a form to delete a Project entity.
	 *
	 * @param ArusProject $project The Project entity
	 *
	 * @return \Symfony\Component\Form\Form The form
	 */
	private function createDeleteForm(ArusProject $project)
	{
		return $this->createFormBuilder()
		->setAction($this->generateUrl('project_delete', array('id' => $project->getId())))
		->setMethod('DELETE')
		->getForm()
			;
	}
}
