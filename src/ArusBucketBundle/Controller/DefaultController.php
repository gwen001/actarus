<?php

namespace ArusBucketBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\StreamedResponse;

use ArusBucketBundle\Entity\ArusBucket;
use ArusBucketBundle\Form\ArusBucketEditType;
use ArusBucketBundle\Form\ArusBucketQuickEditType;

use ArusBucketBundle\Entity\Multiple;
use ArusBucketBundle\Form\AddMultipleType;

use ArusBucketBundle\Entity\Import;
use ArusBucketBundle\Form\ImportType;

use ArusBucketBundle\Entity\Search;
use ArusBucketBundle\Form\SearchType;
use ArusBucketBundle\Form\ExportType;


class DefaultController extends Controller
{
	public function indexAction(Request $request)
    {
        $t_status = $this->getParameter('bucket')['status'];

		$search = new Search();
		$search_form = $this->createForm( new SearchType(['t_status'=>$t_status]), $search );
		$search_form->handleRequest($request);

		$export_form = $this->createForm( new ExportType(['t_status'=>$t_status]), $search, ['action'=>$this->generateUrl('bucket_export')] );
		
		$data = null;
		if( $search_form->isSubmitted() && $search_form->isValid() )  {
			$data = $search_form->getData();
		}

		$page = 1;
		$limit = $this->getParameter('results_per_page');
		$total_bucket = $this->get('bucket')->search( $data, -1 );
		$n_page = ceil( $total_bucket/$limit );

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

		$t_bucket = $this->get('bucket')->search( $data, $page );
		$pagination = $this->get('app')->paginate( $total_bucket, count($t_bucket), $page, true );

		return $this->render('ArusBucketBundle:Default:index.html.twig', array(
			'search_form' => $search_form->createView(),
			'export_form' => $export_form->createView(),
			't_bucket' => $t_bucket,
            't_status' => $t_status,
			'pagination' => $pagination,
		));
    }
    
    
	/**
	 * Create a new ArusBucket entity.
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

		$multiple_form = $this->createForm( new AddMultipleType(), $multiple, ['action'=>$this->generateUrl('bucket_add_multiple')] );
		$import_form = $this->createForm( new ImportType(), $import, ['action'=>$this->generateUrl('bucket_add_import')] );

		return $this->render('ArusBucketBundle:Default:add.html.twig', array(
			'multiple_form' => $multiple_form->createView(),
			'import_form' => $import_form->createView(),
		));
	}
	

	/**
	 * Create a new ArusBucket entity.
	 *
	 */
	public function addMultipleAction(Request $request)
	{
		$multiple = new Multiple();
		$form = $this->createForm( new AddMultipleType(), $multiple );
		$form->handleRequest( $request );

		if ($form->isSubmitted() && $form->isValid()) {
			$project = $multiple->getProject();
			$t_bucket = explode( "\n", $multiple->getNames() );
			$cnt = $this->get('bucket')->doImport( $project, $t_bucket, $multiple->getRecon() );
			$this->addFlash( 'success', $cnt.' bucket added!' );
			return $this->redirectToRoute( 'project_show',array('id'=>$project->getId()) );
		}

		$this->addFlash( 'danger', 'Error!' );
		return $this->redirectToRoute( 'bucket_homepage' );
	}


	/**
	 * Import Bucket from file
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
			$t_bucket = file( $source_file, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES );
			$cnt = $this->get('bucket')->doImport( $project, $t_bucket, $import->getRecon() );
			$this->addFlash( 'success', $cnt.' bucket imported!' );
			return $this->redirectToRoute( 'project_show',array('id'=>$project->getId()) );
		}

		$this->addFlash( 'danger', 'Error!' );
		return $this->redirectToRoute( 'bucket_homepage' );
	}

	
	/**
	 * Export search result
	 *
	 */
	public function exportAction( Request $request )
	{
		$t_status = $this->getParameter('bucket')['status'];

		$search = new Search();
		$export_form = $this->createForm( new ExportType(['t_status'=>$t_status]), $search, ['action'=>$this->generateUrl('bucket_export')] );
		$export_form->handleRequest( $request );

		$data = null;
		if( $export_form->isSubmitted() && $export_form->isValid() )  {
			$data = $export_form->getData();
		}
		//var_dump( $data );

		if( $data->getExportFull() == 'page' ) {
			$page = 1;
			$limit = $this->getParameter('results_per_page');
			$total_bucket = $this->get('bucket')->search( $data, -1 );
			$n_page = ceil( $total_bucket/$limit );
	
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
		
		$t_bucket = $this->get('bucket')->search( $data, $page, $limit );
		
		$response = new StreamedResponse();
		$response->setCallback(function() use($data,$t_bucket) {
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
			foreach( $t_bucket as $o ) {
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
		$response->headers->set( 'Content-Disposition','attachment; filename="bucket.csv"' );
		
		return $response;            
	}
}
