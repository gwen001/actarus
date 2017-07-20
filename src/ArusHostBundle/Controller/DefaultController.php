<?php

namespace ArusHostBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormError;

use ArusHostBundle\Entity\ArusHost;
use ArusHostBundle\Form\ArusHostAddType;
use ArusHostBundle\Form\ArusHostEditType;
use ArusHostBundle\Form\ArusHostQuickEditType;

use ArusHostBundle\Entity\Import;
use ArusHostBundle\Form\ImportType;

use ArusHostBundle\Entity\Search;
use ArusHostBundle\Form\SearchType;

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
		$form = $this->createForm( new SearchType(['t_status'=>$t_status]), $search );
		$form->handleRequest($request);

		$data = null;
		if( $form->isSubmitted() && $form->isValid() )  {
			$data = $form->getData();
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

		$pagination = $this->get('app')->paginate( $total_host, count($t_host), $page );

		return $this->render('ArusHostBundle:Default:index.html.twig', array(
			'form' => $form->createView(),
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
	public function newAction(Request $request)
	{
		$host = new ArusHost();

		$form = $this->createForm( new ArusHostAddType(), $host );
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$exist = $this->get('host')->exist( $host->getProject(), $host->getName() );
			if( !$exist ) {
				$domain = $this->get('domain')->exist( $host->getProject(), $host->getDomain(), null, true );
				if( !$domain ) {
					$domain = $this->get('domain')->create( $host->getProject(), $host->getDomain(), $host->getRecon() );
				}

				$host = $this->get('host')->create( $host->getProject(), $domain, $host->getName(), $host->getRecon() );
				$this->addFlash( 'success', 'New host added!' );
				return $this->redirectToRoute('host_show',array('id'=>$host->getId()));
			} else {
				$form->get('name')->addError( new FormError('This host already exist') );
			}
		}

		return $this->render('ArusHostBundle:Default:new.html.twig', array(
			'host' => $host,
			'form' => $form->createView(),
		));
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


	/**
	 * Import Host from file
	 *
	 */
	public function importAction(Request $request, $project_id )
	{
		$rq = $this->getParameter('host');
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
			$this->addFlash( 'success', $r.' host imported!' );

			if( $project_id && $project ) {
				return $this->redirectToRoute('project_show',array('id'=>$project->getId()));
			} else {
				return $this->redirectToRoute('host_homepage');
			}
		}

		return $this->render('ArusHostBundle:Default:import.html.twig', array(
			'import' => $import,
			'form' => $form->createView(),
			'allowed_extension' => $allowed_extension,
		));
	}


	private function doImport( $project, $sf, $recon=true )
	{
		$t_line = file( $sf, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES );
		$cnt = $this->get('host')->import( $project, $t_line, $recon );

		return $cnt;
	}


	public function getListAction(Request $request, $entity_id)
	{
		echo $this->get('entity_technology')->getListAction( $entity_id );
		exit();
	}
}
