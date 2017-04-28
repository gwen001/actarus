<?php

namespace ArusRequestBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use ArusRequestBundle\Entity\ArusRequest;
use ArusRequestBundle\Form\ArusRequestType;

use ArusRequestBundle\Entity\Search;
use ArusRequestBundle\Form\SearchType;

use ArusRequestBundle\Entity\Import;
use ArusRequestBundle\Form\ImportType;


class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
		$em = $this->getDoctrine()->getManager();
		$rq = $this->getParameter('request');
		$t_protocol = $rq['http_protocol'];
		$t_method = $rq['http_method'];
	
		$search = new Search();
		$form = $this->createForm( new SearchType(array('t_protocol'=>$t_protocol,'t_method'=>$t_method,'port'=>80)), $search );
		$form->handleRequest( $request );
		
		$data = null;
		if( $form->isSubmitted() && $form->isValid() )  {
			$data = $form->getData();
		}
	
		$page = 1;
		$limit = $this->getParameter('results_per_page');
		$total_request = $this->get('arequest')->search( $data, -1 );
		$n_page = ceil( $total_request/$limit );

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

		//$t_request = $em->getRepository('ArusRequestBundle:ArusRequest')->search( $data );
		$t_request = $this->get('arequest')->search( $data, $page );

		$pagination = $this->get('app')->paginate( $total_request, count($t_request), $page );

		return $this->render('ArusRequestBundle:Default:index.html.twig', array(
			'form' => $form->createView(),
			't_request' => $t_request,
			'pagination' => $pagination,
		));
    }
	
	
	/**
	 * Finds and displays a ArusRequest entity.
	 *
	 */
	public function showAction(Request $request, ArusRequest $arequest)
	{
		$t_screenshot = $this->get('entity_attachment')->search( ['entityId'=>$arequest->getEntityId(),'title'=>'http%'] );
		
		$t_status = $this->getParameter('request')['status'];
		//$quick_edit = $this->createForm(new ArusRequestQuickEditType(['t_status'=>$t_status]), $host, ['action'=>$this->generateUrl('host_quickedit',['id'=>$host->getId()])] );

		$deleteForm = $this->createDeleteForm( $arequest );

		$alert_mod = $this->get('entity_alert')->getModAction( $arequest );
		$task_mod = $this->get('entity_task')->getModAction( $arequest );

		return $this->render('ArusRequestBundle:Default:show.html.twig', array(
			'request' => $arequest,
			'delete_form' => $deleteForm->createView(),
			'alert_mod' => $alert_mod,
			'task_mod' => $task_mod,
			't_status' => $t_status,
			't_screenshot' => $t_screenshot,
            //'quick_edit' => $quick_edit->createView(),
		));
	}

	
	/**
	 * Create a new ArusRequest entity.
	 *
	 */
	public function newAction(Request $request)
	{
		$rq = $this->getParameter('request');
		$t_protocol = $rq['http_protocol'];
		$t_method = $rq['http_method'];
		
		$arequest = new ArusRequest();
		$arequest->setPort( $rq['default_port'] );
		$form = $this->createForm( new ArusRequestType(array('t_protocol'=>$t_protocol,'t_method'=>$t_method,'port'=>80)), $arequest );
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$em->persist( $arequest );
			$em->flush();
			
			$this->addFlash( 'success', 'New request added!' );
			
			return $this->redirectToRoute('request_homepage');
		}
		
		return $this->render('ArusRequestBundle:Default:new.html.twig', array(
			'request' => $arequest,
			'form' => $form->createView(),
		));
	}
	
	
	/**
	 * Import ArusRequest from file
	 *
	 */
	public function importAction(Request $request, $project_id )
	{
		$rq = $this->getParameter('request');
		$t_format = $rq['import_format'];
		$allowed_extension = implode( ',', $rq['allowed_extension'] );
		
		$import = new Import();
		
		if( $project_id ) {
			$project = $this->getDoctrine()->getRepository('ArusProjectBundle:ArusProject')->find( $project_id );
			if( $project ) {
				$import->setProject( $project );
			}
		}

		$form = $this->createForm( new ImportType(array('t_format'=>$t_format)), $import );
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {
			$source_file = $import->getSourceFile()->getPathName();
			$r = $this->get('arequest')->import( $import->getProject(), $source_file, $import->getFormat(), $import->getRecon() );
			$this->addFlash( 'success', $r.' requests imported!' );
						
			if( $project_id && $project ) {
				return $this->redirectToRoute('import_edit',array('id'=>$project->getId()));
			} else {
				return $this->redirectToRoute('request_homepage');
			}
		}
		
		return $this->render('ArusRequestBundle:Default:import.html.twig', array(
			'import' => $import,
			'form' => $form->createView(),
			'allowed_extension' => $allowed_extension,
		));
	}
	
	
	/**
	 * Deletes a ArusRequest entity.
	 *
	 */
	public function deleteAction(Request $request, ArusRequest $arequest)
	{
		$form = $this->createDeleteForm($arequest);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->get('app')->entityDelete( $arequest );
			$em = $this->getDoctrine()->getManager();
			$em->remove($arequest);
			$em->flush();

			$this->addFlash( 'success', 'Request deleted!' );
		}

		return $this->redirectToRoute('request_homepage');
	}


	/**
	 * Creates a form to delete a ArusRequest entity.
	 *
	 * @param ArusRequest $request The ArusRequest entity
	 *
	 * @return \Symfony\Component\Form\Form The form
	 */
	private function createDeleteForm(ArusRequest $request)
	{
		return $this->createFormBuilder()
		->setAction($this->generateUrl('request_delete', array('id' => $request->getId())))
		->setMethod('DELETE')
		->getForm()
			;
	}
}
