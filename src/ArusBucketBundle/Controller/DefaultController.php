<?php

namespace ArusBucketBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormError;

use ArusBucketBundle\Entity\ArusBucket;
use ArusBucketBundle\Form\ArusBucketEditType;
use ArusBucketBundle\Form\ArusBucketQuickEditType;

use ArusBucketBundle\Entity\Multiple;
use ArusBucketBundle\Form\AddMultipleType;

use ArusBucketBundle\Entity\Import;
use ArusBucketBundle\Form\ImportType;

use ArusBucketBundle\Entity\Search;
use ArusBucketBundle\Form\SearchType;


class DefaultController extends Controller
{
	public function indexAction(Request $request)
    {
        $t_status = $this->getParameter('bucket')['status'];

		$search = new Search();
		$form = $this->createForm( new SearchType(['t_status'=>$t_status]), $search );
		$form->handleRequest($request);

		$data = null;
		if( $form->isSubmitted() && $form->isValid() )  {
			$data = $form->getData();
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
		$pagination = $this->get('app')->paginate( $total_bucket, count($t_bucket), $page );

		return $this->render('ArusBucketBundle:Default:index.html.twig', array(
			'form' => $form->createView(),
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
}
