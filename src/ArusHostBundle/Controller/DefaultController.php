<?php

namespace ArusHostBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\StreamedResponse;

use ArusHostBundle\Entity\ArusHost;
use ArusHostBundle\Form\ArusHostEditType;
use ArusHostBundle\Form\ArusHostQuickEditType;

use ArusHostBundle\Entity\Multiple;
use ArusHostBundle\Form\AddMultipleType;

use ArusHostBundle\Entity\Import;
use ArusHostBundle\Form\ImportType;

use ArusHostBundle\Entity\Search;
use ArusHostBundle\Form\SearchType;
use ArusHostBundle\Form\ExportType;

use ArusDomainBundle\Entity\ArusDomain;
use ArusServerBundle\Entity\ArusServer;

use ArusEntityTaskBundle\Entity\ArusEntityTask;
use ArusEntityTaskBundle\Entity\Search as EntityTaskSearch;
use ArusEntityTaskBundle\Form\ArusEntityTaskAddType;

use ArusEntityAlertBundle\Entity\ArusEntityAlert;
use ArusEntityAlertBundle\Form\ArusEntityAlertType;

use ArusEntityTechnologyBundle\Entity\ArusEntityTechnology;
use ArusEntityTechnologyBundle\Form\ArusEntityTechnologyType;


class DefaultController extends Controller
{
	public function indexAction(Request $request)
    {
		$t_status = $this->getParameter('host')['status'];

		$search = new Search();
		$search_form = $this->createForm( new SearchType(['t_status'=>$t_status]), $search );
		$search_form->handleRequest($request);

		$export_form = $this->createForm( new ExportType(['t_status'=>$t_status]), $search, ['action'=>$this->generateUrl('host_export')] );

		$data = null;
		if( $search_form->isSubmitted() && $search_form->isValid() )  {
			$data = $search_form->getData();
		}

		$page = 1;
		$limit = $this->getParameter('results_per_page');
		$total_host = $this->get('host')->search( $data, -1 );
		$n_page = ceil( $total_host/$limit );

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

		$t_host = $this->get('host')->search( $data, $page );
		foreach( $t_host as $h ) {
			$h->setEntityAlerts( $this->get('entity_alert')->search(['entity_id'=>$h->getEntityId()]) );
			$h->setEntityTechnologies( $this->get('entity_technology')->getListAction($h->getEntityId()) );
		}

		$pagination = $this->get('app')->paginate( $total_host, count($t_host), $page, true );

		return $this->render('ArusHostBundle:Default:index.html.twig', array(
			'search_form' => $search_form->createView(),
			'export_form' => $export_form->createView(),
			't_host' => $t_host,
			't_status' => $t_status,
			'pagination' => $pagination,
		));
    }


	/**
	 * Finds and displays a ArusHost entity.
	 *
	 */
	public function showAction(Request $request, ArusHost $host)
	{
		$t_screenshot = $this->get('entity_attachment')->search( ['entityId'=>$host->getEntityId(),'title'=>'http%'] );
		
		$t_status = $this->getParameter('host')['status'];
		$quick_edit = $this->createForm(new ArusHostQuickEditType(['t_status'=>$t_status]), $host, ['action'=>$this->generateUrl('host_quickedit',['id'=>$host->getId()])] );

		$deleteForm = $this->createDeleteForm( $host );

		$alert_mod = $this->get('entity_alert')->getModAction( $host );
		$task_mod = $this->get('entity_task')->getModAction( $host );
		$techno_mod = $this->get('entity_technology')->getModAction( $host );

		foreach( $host->getHostServers() as $hs ) {
        	$s = $hs->getServer();
            $s->setEntityAlerts( $this->get('entity_alert')->search(['entity_id'=>$s->getEntityId()]) );
        }

		return $this->render('ArusHostBundle:Default:show.html.twig', array(
			'host' => $host,
			'delete_form' => $deleteForm->createView(),
			'alert_mod' => $alert_mod,
			'task_mod' => $task_mod,
			'techno_mod' => $techno_mod,
			't_status' => $t_status,
			't_screenshot' => $t_screenshot,
            'quick_edit' => $quick_edit->createView(),
		));
	}


	/**
	 * Create a new ArusHost entity.
	 *
	 */
	public function addAction(Request $request, $project_id )
	{
		$multiple = new Multiple();
		$import = new Import();

		if( $project_id ) {
			$project = $this->getDoctrine()->getRepository('ArusProjectBundle:ArusProject')->find( $project_id );
			if( $project ) {
				$multiple->setProject( $project );
				$import->setProject( $project );
			}
		}

		$multiple_form = $this->createForm( new AddMultipleType(), $multiple, ['action'=>$this->generateUrl('host_add_multiple')] );
		$import_form = $this->createForm( new ImportType(), $import, ['action'=>$this->generateUrl('host_add_import')] );

		return $this->render('ArusHostBundle:Default:add.html.twig', array(
			'multiple_form' => $multiple_form->createView(),
			'import_form' => $import_form->createView(),
		));
	}
	
	
	/**
	 * Create a new ArusHost entity.
	 *
	 */
	public function addMultipleAction(Request $request)
	{
		$multiple = new Multiple();
		$form = $this->createForm( new AddMultipleType(), $multiple );
		$form->handleRequest( $request );

		if ($form->isSubmitted() && $form->isValid()) {
			$project = $multiple->getProject();
			$t_host = explode( "\n", $multiple->getNames() );
			$cnt = $this->get('host')->import( $project, $t_host, $multiple->getRecon() );
			$this->addFlash( 'success', $cnt.' host added!' );
			return $this->redirectToRoute( 'project_show',array('id'=>$project->getId()) );
		}

		$this->addFlash( 'danger', 'Error!' );
		return $this->redirectToRoute( 'host_homepage' );
	}

	
	/**
	 * Import Host from file
	 *
	 */
	public function addImportAction(Request $request )
	{
		$import = new Import();
		$form = $this->createForm( new ImportType(), $import );
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$project = $import->getProject();
			$source_file = $import->getSourceFile();
			$t_host = file( $source_file, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES );
			$cnt = $this->get('host')->import( $project, $t_host, $import->getRecon() );
			$this->addFlash( 'success', $cnt.' host imported!' );
			return $this->redirectToRoute( 'project_show',array('id'=>$project->getId()) );
		}

		$this->addFlash( 'danger', 'Error!' );
		return $this->redirectToRoute( 'host_homepage' );
	}


	/**
	 * Finds and displays a ArusHost entity.
	 *
	 */
	public function viewAction(Request $request, ArusHost $host)
	{
		$techno_list = $this->get('entity_technology')->getListAction( $host->getEntityId() );
		$techno_mod = $this->get('entity_technology')->getModAction( $host );

		$t_status = $this->getParameter('host')['status'];
		$quick_edit = $this->createForm(new ArusHostQuickEditType(['t_status'=>$t_status]), $host, ['action'=>$this->generateUrl('host_quickedit',['id'=>$host->getId()])] );

		return $this->render('ArusHostBundle:Default:view.html.twig', array(
			'host' => $host,
			't_status' => $t_status,
			'techno_list' => $techno_list,
			'techno_mod' => $techno_mod,
            'quick_edit' => $quick_edit->createView(),
		));
	}


	/**
	 * Displays a form to edit an existing Host entity.
	 *
	 */
	public function quickeditAction(Request $request, ArusHost $host)
	{
		$r = ['error'=>0];
        $t_status = $this->getParameter('host')['status'];

		$form = $this->createForm( new ArusHostQuickEditType(['t_status'=>$t_status]), $host, ['action'=>$this->generateUrl('host_quickedit',['id'=>$host->getId()])] );
		$form->handleRequest($request);

		if( $form->isSubmitted() && $form->isValid() ) {
			$em = $this->getDoctrine()->getManager();
			$em->persist( $host );
			$em->flush();
		}

		$response = new Response( json_encode($r) );
		return $response;
	}


	/**
	 * Displays a form to edit an existing ArusHost entity.
	 *
	 */
	public function editAction(Request $request, ArusHost $host)
	{
		$t_status = $this->getParameter('host')['status'];

		$techno_list = $this->get('entity_technology')->getListAction( $host->getEntityId() );

		$form = $this->createForm(new ArusHostEditType(['t_status'=>$t_status]), $host, ['action'=>$this->generateUrl('host_edit',['id'=>$host->getId()])] );
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$exist = $this->get('host')->exist( $host->getProject(), $host->getName(), $host->getId() );
			if( !$exist ) {
				$em = $this->getDoctrine()->getManager();
				$em->persist( $host );
				$em->flush( $host );
				$this->addFlash('success', 'Your changes were saved!');
			} else {
				$this->addFlash( 'danger', 'Error!' );
			}
			return $this->redirectToRoute( 'host_show', ['id'=>$host->getId()] );
		}

		return $this->render('ArusHostBundle:Default:edit.html.twig', array(
			'host' => $host,
			//'project' => $project,
			'form' => $form->createView(),
			'techno_list' => $techno_list,
		));
	}


	/**
	 * Deletes a ArusHost entity.
	 *
	 */
	public function deleteAction(Request $request, ArusHost $host)
	{
		$form = $this->createDeleteForm($host);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->get('app')->entityDelete( $host );
			$em = $this->getDoctrine()->getManager();
			$em->remove($host);
			$em->flush();

			$this->addFlash( 'success', 'Host deleted!' );
		}

		return $this->redirectToRoute('host_homepage');
	}


	/**
	 * Creates a form to delete a ArusHost entity.
	 *
	 * @param ArusHost $host The ArusHost entity
	 *
	 * @return \Symfony\Component\Form\Form The form
	 */
	private function createDeleteForm(ArusHost $host)
	{
		return $this->createFormBuilder()
		->setAction($this->generateUrl('host_delete', array('id' => $host->getId())))
		->setMethod('DELETE')
		->getForm()
			;
	}


	public function getInfoAction(Request $request)
	{
		echo $this->get('host')->getInfo( $request->get('host') );
		exit();
	}


	public function getListAction(Request $request, $entity_id)
	{
		echo $this->get('entity_technology')->getListAction( $entity_id );
		exit();
	}


	/**
	 * Export search result
	 *
	 */
	public function exportAction( Request $request )
	{
		$t_status = $this->getParameter('host')['status'];

		$search = new Search();
		$export_form = $this->createForm( new ExportType(['t_status'=>$t_status]), $search, ['action'=>$this->generateUrl('host_export')] );
		$export_form->handleRequest( $request );

		$data = null;
		if( $export_form->isSubmitted() && $export_form->isValid() )  {
			$data = $export_form->getData();
		}

		if( $data->getExportFull() == 'page' ) {
			$page = 1;
			$limit = $this->getParameter('results_per_page');
			$total_host = $this->get('host')->search( $data, -1 );
			$n_page = ceil( $total_host/$limit );
	
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
		
		$t_host = $this->get('host')->search( $data, $page, $limit );
		
		$response = new StreamedResponse();
		$response->setCallback(function() use($data,$t_host) {
			$t_field = [];
			if( $data->getExportId() ) {
				$t_field[] = 'id';
			}
			if( $data->getExportProject() ) {
				$t_field[] = 'project';
			}
			if( $data->getExportName() ) {
				$t_field[] = 'name';
			}
			if( $data->getExportCreatedAt() ) {
				$t_field[] = 'created_date';
			}
			$handle = fopen( 'php://output', 'w+' );
			fputcsv( $handle, $t_field, ';' );
			foreach( $t_host as $o ) {
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
				if( $data->getExportCreatedAt() ) {
					$tmp[] = date( 'Y/m/d', $o->getCreatedAt()->getTimestamp() );
				}
				fputcsv( $handle, $tmp,';' );
			}
			fclose( $handle );
		});
		
		$response->setStatusCode( 200 );
		$response->headers->set( 'Content-Type', 'text/csv; charset=utf-8' );
		$response->headers->set( 'Content-Disposition','attachment; filename="host.csv"' );
		
		return $response;            
	}
}
