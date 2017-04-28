<?php

namespace ArusServerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormError;

use ArusServerBundle\Entity\ArusServer;
use ArusServerBundle\Form\ArusServerAddType;
use ArusServerBundle\Form\ArusServerEditType;
use ArusServerBundle\Form\ArusServerQuickEditType;

use ArusServerBundle\Entity\Import;
use ArusServerBundle\Form\ImportType;

use ArusServerBundle\Entity\Search;
use ArusServerBundle\Form\SearchType;

use ArusEntityTaskBundle\Entity\ArusEntityTask;
use ArusEntityTaskBundle\Entity\Search as EntityTaskSearch;
use ArusEntityTaskBundle\Form\ArusEntityTaskAddType;

use ArusEntityAlertBundle\Entity\ArusEntityAlert;
use ArusEntityAlertBundle\Form\ArusEntityAlertType;

use ArusEntityTechnologyBundle\Entity\ArusEntityTechnology;
use ArusEntityTechnologyBundle\Form\ArusEntityTechnologyType;

use ArusCommentBundle\Entity\ArusComment;
use ArusCommentBundle\Form\ArusCommentType;

use Actarus\Utils;


class DefaultController extends Controller
{
	public function indexAction(Request $request)
    {
		$t_status = $this->getParameter('server')['status'];

		$search = new Search();
		$form = $this->createForm( new SearchType(['t_status'=>$t_status]), $search );
		$form->handleRequest($request);

		$data = null;
		if( $form->isSubmitted() && $form->isValid() )  {
			$data = $form->getData();
		}

		$page = 1;
		$limit = $this->getParameter('results_per_page');
		$total_server = $this->get('server')->search( $data, -1 );
		$n_page = ceil( $total_server/$limit );

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

		$t_server = $this->get('server')->search( $data, $page );
		foreach( $t_server as $s ) {
			$s->setEntityAlerts( $this->get('entity_alert')->search(['entity_id'=>$s->getEntityId()]) );
			$s->setEntityTechnologies( $this->get('entity_technology')->getListAction($s->getEntityId()) );
			$open_port = '';
			$alert_port = $this->get('entity_alert')->search( ['entity_id'=>$s->getEntityId(),'descr'=>'Open ports are'], 1, 1 );
			if( $alert_port && is_array($alert_port) && count($alert_port)==1 ) {
				$open_port = trim( str_ireplace('Open ports are:','',$alert_port[0]->getDescr()), ' .' );
			}
			$s->open_port = $open_port;
		}
		$pagination = $this->get('app')->paginate( $total_server, count($t_server), $page );

		return $this->render('ArusServerBundle:Default:index.html.twig', array(
			'form' => $form->createView(),
			't_server' => $t_server,
			't_status' => $t_status,
			'pagination' => $pagination,
		));
    }


	/**
	 * Finds and displays a ArusServer entity.
	 *
	 */
	public function showAction(Request $request, ArusServer $server)
	{
		$t_screenshot = $this->get('entity_attachment')->search( ['entityId'=>$server->getEntityId(),'title'=>'http%'] );

		$t_status = $this->getParameter('server')['status'];
		$quick_edit = $this->createForm(new ArusServerQuickEditType(['t_status'=>$t_status]), $server, ['action'=>$this->generateUrl('server_quickedit',['id'=>$server->getId()])] );

		$deleteForm = $this->createDeleteForm($server);

		$alert_mod = $this->get('entity_alert')->getModAction( $server );
		$task_mod = $this->get('entity_task')->getModAction( $server );
		$techno_mod = $this->get('entity_technology')->getModAction( $server );

        foreach( $server->getHostServers() as $hs ) {
        	$h = $hs->getHost();
            $h->setEntityAlerts( $this->get('entity_alert')->search(['entity_id'=>$h->getEntityId()]) );
        }

		return $this->render('ArusServerBundle:Default:show.html.twig', array(
			'server' => $server,
			'delete_form' => $deleteForm->createView(),
			'alert_mod' => $alert_mod,
			'task_mod' => $task_mod,
			'techno_mod' => $techno_mod,
			//'t_host' => $t_host,
			't_status' => $t_status,
			't_screenshot' => $t_screenshot,
            'quick_edit' => $quick_edit->createView(),
		));
	}


	/**
	 * Create a new ArusServer entity.
	 *
	 */
	public function newAction(Request $request)
	{
		$server = new ArusServer();
		$form = $this->createForm( new ArusServerAddType(), $server );
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			if( !strlen($server->getName()) ) {
				$server->setName( gethostbyaddr($server->getName()) );
			}

			$exist = $this->get('server')->exist( $server->getProject(), $server->getName() );
			if( !$exist ) {
				$server = $this->get('server')->create( $server->getProject(), $server->getName(), $server->getRecon() );
				$this->addFlash( 'success', 'New server added!' );
				return $this->redirectToRoute('server_show',array('id'=>$server->getId()));
			} else {
				$form->get('name')->addError( new FormError('This server already exist') );
			}
		}

		return $this->render('ArusServerBundle:Default:new.html.twig', array(
			'server' => $server,
			'form' => $form->createView(),
		));
	}


	/**
	 * Finds and displays a ArusServer entity.
	 *
	 */
	public function viewAction(Request $request, ArusServer $server)
	{
		$t_status = $this->getParameter('server')['status'];
		$quick_edit = $this->createForm(new ArusServerQuickEditType(['t_status'=>$t_status]), $server, ['action'=>$this->generateUrl('server_quickedit',['id'=>$server->getId()])] );

		$techno_list = $this->get('entity_technology')->getListAction( $server->getEntityId() );
		$techno_mod = $this->get('entity_technology')->getModAction( $server );

		return $this->render('ArusServerBundle:Default:view.html.twig', array(
			'server' => $server,
			't_status' => $t_status,
			'techno_list' => $techno_list,
			'techno_mod' => $techno_mod,
            'quick_edit' => $quick_edit->createView(),
		));
	}


	/**
	 * Displays a form to edit an existing Server entity.
	 *
	 */
	public function quickeditAction(Request $request, ArusServer $server)
	{
		$r = ['error'=>0];
        $t_status = $this->getParameter('server')['status'];

		$form = $this->createForm( new ArusServerQuickEditType(['t_status'=>$t_status]), $server, ['action'=>$this->generateUrl('server_quickedit',['id'=>$server->getId()])] );
		$form->handleRequest($request);

		if( $form->isSubmitted() && $form->isValid() ) {
			$em = $this->getDoctrine()->getManager();
			$em->persist( $server );
			$em->flush();
		}

		$response = new Response( json_encode($r) );
		return $response;
	}


	/**
	 * Displays a form to edit an existing ArusServer entity.
	 *
	 */
	public function editAction(Request $request, ArusServer $server)
	{
		$t_status = $this->getParameter('server')['status'];

		$techno_list = $this->get('entity_technology')->getListAction( $server->getEntityId() );

		$form = $this->createForm(new ArusServerEditType(['t_status'=>$t_status]), $server, ['action'=>$this->generateUrl('server_edit',['id'=>$server->getId()])] );
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$exist = $this->get('server')->exist( $server->getProject(), $server->getName(), $server->getId() );
			if( !$exist ) {
				$em = $this->getDoctrine()->getManager();
				$em->persist( $server );
				$em->flush();
				$this->addFlash( 'success', 'Your changes were saved!' );
			} else {
				$this->addFlash( 'danger', 'Error!' );
			}
			return $this->redirectToRoute('server_show',array('id'=>$server->getId()));
		}

		return $this->render('ArusServerBundle:Default:edit.html.twig', array(
			'server' => $server,
			'form' => $form->createView(),
			't_status' => $t_status,
			'techno_list' => $techno_list,
		));
	}


	/**
	 * Deletes a ArusServer entity.
	 *
	 */
	public function deleteAction(Request $request, ArusServer $server)
	{
		$form = $this->createDeleteForm($server);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->get('app')->entityDelete( $server );
			$em = $this->getDoctrine()->getManager();
			$em->remove($server);
			$em->flush();

			$this->addFlash( 'success', 'Server deleted!' );
		}

		return $this->redirectToRoute('server_homepage');
	}


	/**
	 * Creates a form to delete a ArusServer entity.
	 *
	 * @param ArusServer $server The ArusServer entity
	 *
	 * @return \Symfony\Component\Form\Form The form
	 */
	private function createDeleteForm(ArusServer $server)
	{
		return $this->createFormBuilder()
		->setAction($this->generateUrl('server_delete', array('id' => $server->getId())))
		->setMethod('DELETE')
		->getForm()
			;
	}


	/**
	 * Import Server from file
	 *
	 */
	public function importAction(Request $request, $project_id )
	{
		$rq = $this->getParameter('server');
		$allowed_extension = implode( ',', $rq['allowed_extension'] );

		$import = new Import();

		if( $project_id ) {
			$project = $this->getDoctrine()->getRepository('ArusProjectBundle:ArusProject')->find( $project_id );
			if( $project ) {
				$import->setProject( $project );
			}
		}

		$form = $this->createForm( new ImportType(), $import );
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$source_file = $import->getSourceFile();
			$r = $this->doImport( $import->getProject(), $source_file, $import->getRecon() );
			$this->addFlash( 'success', $r.' server imported!' );

			if( $project_id && $project ) {
				return $this->redirectToRoute('project_show',array('id'=>$project->getId()));
			} else {
				return $this->redirectToRoute('server_homepage');
			}
		}

		return $this->render('ArusServerBundle:Default:import.html.twig', array(
			'import' => $import,
			'form' => $form->createView(),
			'allowed_extension' => $allowed_extension,
		));
	}


	private function doImport( $project, $sf, $recon=true )
	{
		$t_line = file( $sf, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES );
		$cnt = $this->get('server')->import( $project, $t_line, $recon );

		return $cnt;
	}
}
