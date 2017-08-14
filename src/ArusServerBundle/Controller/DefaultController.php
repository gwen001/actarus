<?php

namespace ArusServerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\StreamedResponse;

use ArusServerBundle\Entity\ArusServer;
use ArusServerBundle\Form\ArusServerEditType;
use ArusServerBundle\Form\ArusServerQuickEditType;

use ArusServerBundle\Entity\Multiple;
use ArusServerBundle\Form\AddMultipleType;

use ArusServerBundle\Entity\Range;
use ArusServerBundle\Form\AddRangeType;

use ArusServerBundle\Entity\Import;
use ArusServerBundle\Form\ImportType;

use ArusServerBundle\Entity\Search;
use ArusServerBundle\Form\SearchType;
use ArusServerBundle\Form\ExportType;

use ArusEntityTaskBundle\Entity\ArusEntityTask;
use ArusEntityTaskBundle\Entity\Search as EntityTaskSearch;
use ArusEntityTaskBundle\Form\ArusEntityTaskAddType;

use ArusEntityAlertBundle\Entity\ArusEntityAlert;
use ArusEntityAlertBundle\Form\ArusEntityAlertType;

use ArusEntityTechnologyBundle\Entity\ArusEntityTechnology;
use ArusEntityTechnologyBundle\Form\ArusEntityTechnologyType;

use Actarus\Utils;


class DefaultController extends Controller
{
	public function indexAction(Request $request)
    {
		$t_status = $this->getParameter('server')['status'];

		$search = new Search();
		$search_form = $this->createForm( new SearchType(['t_status'=>$t_status]), $search );
		$search_form->handleRequest($request);

		$export_form = $this->createForm( new ExportType(['t_status'=>$t_status]), $search, ['action'=>$this->generateUrl('server_export')] );

		$data = null;
		if( $search_form->isSubmitted() && $search_form->isValid() )  {
			$data = $search_form->getData();
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
		}
		$pagination = $this->get('app')->paginate( $total_server, count($t_server), $page, true );

		return $this->render('ArusServerBundle:Default:index.html.twig', array(
			'search_form' => $search_form->createView(),
			'export_form' => $export_form->createView(),
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
	 * Add new ArusServer entity.
	 *
	 */
	public function addAction(Request $request, $project_id )
	{
		$multiple = new Multiple();
		$range = new Range();
		$import = new Import();

		if( $project_id ) {
			$project = $this->getDoctrine()->getRepository('ArusProjectBundle:ArusProject')->find( $project_id );
			if( $project ) {
				$multiple->setProject( $project );
				$range->setProject( $project );
				$import->setProject( $project );
			}
		}

		$multiple_form = $this->createForm( new AddMultipleType(), $multiple, ['action'=>$this->generateUrl('server_add_multiple')] );
		$range_form = $this->createForm( new AddRangeType(), $range, ['action'=>$this->generateUrl('server_add_range')] );
		$import_form = $this->createForm( new ImportType(), $import, ['action'=>$this->generateUrl('server_add_import')] );

		return $this->render('ArusServerBundle:Default:add.html.twig', array(
			'multiple_form' => $multiple_form->createView(),
			'range_form' => $range_form->createView(),
			'import_form' => $import_form->createView(),
		));
	}

	
	/**
	 * Create a new ArusServer entity.
	 *
	 */
	public function addMultipleAction(Request $request)
	{
		$multiple = new Multiple();
		$form = $this->createForm( new AddMultipleType(), $multiple );
		$form->handleRequest( $request );

		if ($form->isSubmitted() && $form->isValid()) {
			$project = $multiple->getProject();
			$t_server = explode( "\n", $multiple->getIps() );
			$cnt = $this->get('server')->import( $project, $t_server, $multiple->getRecon() );
			$this->addFlash( 'success', $cnt.' server added!' );
			return $this->redirectToRoute( 'project_show',array('id'=>$project->getId()) );
		}

		$this->addFlash( 'danger', 'Error!' );
		return $this->redirectToRoute( 'server_homepage' );
	}

	
	/**
	 * Create a new ArusServer entity.
	 *
	 */
	public function addRangeAction(Request $request)
	{
		$range = new Range();
		$form = $this->createForm( new AddRangeType(), $range );
		$form->handleRequest( $request );

		if ($form->isSubmitted() && $form->isValid()) {
			$project = $range->getProject();
			$start = ip2long( $range->getRangeStart() );
			$end = ip2long( $range->getRangeEnd() );
			$t_server = range( $start, $end, 1 );
			array_walk( $t_ips, create_function('&$val','$val=long2ip($val);') );
			$cnt = $this->get('server')->import( $project, $t_server, $range->getRecon() );
			$this->addFlash( 'success', $cnt.' server added!' );
			return $this->redirectToRoute( 'project_show',array('id'=>$project->getId()) );
		}

		$this->addFlash( 'danger', 'Error!' );
		return $this->redirectToRoute( 'server_homepage' );
	}


	/**
	 * Import Server from file
	 *
	 */
	public function addImportAction( Request $request )
	{
		$import = new Import();
		$form = $this->createForm( new ImportType(), $import );
		$form->handleRequest( $request );

		if( $form->isSubmitted() && $form->isValid() ) {
			$project = $import->getProject();
			$source_file = $import->getSourceFile();
			$t_server = file( $source_file, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES );
			$cnt = $this->get('server')->import( $project, $t_server, $import->getRecon() );
			$this->addFlash( 'success', $cnt.' server imported!' );
			return $this->redirectToRoute( 'project_show',array('id'=>$project->getId()) );
		}

		$this->addFlash( 'danger', 'Error!' );
		return $this->redirectToRoute( 'server_homepage' );
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
	 * Export search result
	 *
	 */
	public function exportAction( Request $request )
	{
		$t_status = $this->getParameter('server')['status'];

		$search = new Search();
		$export_form = $this->createForm( new ExportType(['t_status'=>$t_status]), $search, ['action'=>$this->generateUrl('server_export')] );
		$export_form->handleRequest( $request );

		$data = null;
		if( $export_form->isSubmitted() && $export_form->isValid() )  {
			$data = $export_form->getData();
		}
		//var_dump( $data );

		if( $data->getExportFull() == 'page' ) {
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
			$limit = -1;
		} else {
			$page = 1;
			$limit = null;
		}
		
		$t_server = $this->get('server')->search( $data, $page, $limit );
		
		$response = new StreamedResponse();
		$response->setCallback(function() use($data,$t_server) {
			$t_field = [];
			if( $data->getExportId() ) {
				$t_field[] = 'id';
			}
			if( $data->getExportProject() ) {
				$t_field[] = 'project';
			}
			if( $data->getExportName() ) {
				$t_field[] = 'ip';
			}
			if( $data->getExportAlias() ) {
				$t_field[] = 'alias';
			}
			if( $data->getExportCreatedAt() ) {
				$t_field[] = 'created_date';
			}
			$handle = fopen( 'php://output', 'w+' );
			fputcsv( $handle, $t_field, ';' );
			foreach( $t_server as $o ) {
				$tmp = [];
				if( $data->getExportId() ) {
					$tmp[] = $o->getId();
				}
				if( $data->getExportProject() ) {
					$tmp[] = $o->getProject()->getName();
				}
				if( $data->getExportName() ) {
					$tmp[] = $o->getName();
				}
				if( $data->getExportAlias() ) {
					$tmp[] = $o->getAlias();
				}
				if( $data->getExportCreatedAt() ) {
					$tmp[] = date( 'Y/m/d', $o->getCreatedAt()->getTimestamp() );
				}
				fputcsv( $handle, $tmp,';' );
			}
			fclose( $handle );
		});
		
		$response->setStatusCode( 200 );
		$response->headers->set( 'Content-Type', 'text/csv; charset=utf-8' );
		$response->headers->set( 'Content-Disposition','attachment; filename="server.csv"' );
		
		return $response;            
	}
}
