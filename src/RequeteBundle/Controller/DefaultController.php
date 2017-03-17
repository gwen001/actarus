<?php

namespace RequeteBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use RequeteBundle\Entity\Requete;
use RequeteBundle\Form\RequeteType;

use RequeteBundle\Entity\Search;
use RequeteBundle\Form\SearchType;

use RequeteBundle\Entity\Import;
use RequeteBundle\Form\ImportType;


class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
		$em = $this->getDoctrine()->getManager();
		$t_project = $em->getRepository('ArusProjectBundle:ArusProject')->findArray();
		$rq = $this->getParameter('requete');
		$t_protocol = $rq['http_protocol'];
		$t_method = $rq['http_method'];
	
		$search = new Search();
		$form = $this->createForm( new SearchType(array('t_project'=>$t_project,'t_protocol'=>$t_protocol,'t_method'=>$t_method,'port'=>80)), $search );
		$form->handleRequest($request);
		
		$data = null;
		if( $form->isSubmitted() && $form->isValid() )  {
			$data = $form->getData();
		}
	
		$t_requete = $em->getRepository('RequeteBundle:Requete')->search( $data );

		return $this->render('RequeteBundle:Default:index.html.twig', array(
			't_project' => $t_project,
			'form' => $form->createView(),
			't_requete' => $t_requete,
		));
    }
	
	
	/**
	 * Create a new Requete entity.
	 *
	 */
	public function newAction(Request $request)
	{
		$rq = $this->getParameter('requete');
		$t_protocol = $rq['http_protocol'];
		$t_method = $rq['http_method'];
		
		$requete = new Requete();
		$requete->setPort( $rq['default_port'] );
		$form = $this->createForm( new RequeteType(array('t_protocol'=>$t_protocol,'t_method'=>$t_method,'port'=>80)), $requete );
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$em->persist( $requete );
			$em->flush();
			
			$this->addFlash( 'success', 'New requete added!' );
			
			return $this->redirectToRoute('requete_homepage');
		}
		
		return $this->render('RequeteBundle:Default:new.html.twig', array(
			'requete' => $requete,
			'form' => $form->createView(),
		));
	}
	
	
	/**
	 * Import Requete from file
	 *
	 */
	public function importAction(Request $request, $project_id )
	{
		$rq = $this->getParameter('requete');
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
			$source_file = $import->getSourceFile();
			$r = $this->doImport( $import->getProject(), $source_file, $import->getFormat() );
			$this->addFlash( 'success', $r.' requete imported!' );
			
			if( $project_id && $project ) {
				return $this->redirectToRoute('import_edit',array('id'=>$project->getId()));
			} else {
				return $this->redirectToRoute('requete_homepage');
			}
		}
		
		return $this->render('RequeteBundle:Default:import.html.twig', array(
			'import' => $import,
			'form' => $form->createView(),
			'allowed_extension' => $allowed_extension,
		));
	}
	
	
	private function doImport( $project, $sf, $format )
	{
		switch( $format ) {
			case 'bs_txt';
				return $this->doImportTxt( $project, $sf->getPathName() );
			case 'bs_xml';
				return $this->doImportXml( $project, $sf->getPathName(), false );
			case 'bs_xml64';
				return $this->doImportXml( $project, $sf->getPathName(), true );
			default:
				return false;
		}
	}
	
	
	private function doImportTxt( $project, $sf )
	{
		return true;
	}
	
	
	private function doImportXml( $project, $sf, $base64 )
	{
		$cnt = 0;
		$query = null;
		$em = $this->getDoctrine()->getManager();
		
		$dom = new \DOMDocument();
		$dom->loadXML( file_get_contents($sf) );
		
		$items = $dom->getElementsByTagName( 'item' );
		
		foreach( $items as $i )
		{
			$r = new Requete();
			$r->setProject( $project );
			
			foreach( $i->childNodes as $n ) {
				switch ( $n->nodeName ) {
					case 'url':
						$r->setUrl( $n->nodeValue );
						break;
					case 'host':
						$r->setHost( $n->nodeValue );
						break;
					case 'port':
						$r->setPort( $n->nodeValue );
						break;
					case 'protocol':
						$r->setProtocol( $n->nodeValue );
						break;
					case 'method':
						$r->setMethod( $n->nodeValue );
						break;
					case 'request':
						$query = $n->nodeValue;
						if( $base64 ) {
							$query = base64_decode( $query );
						}
						break;
					default:
						break;
				}
				
			}
			
			$t_info = parse_url( $r->getUrl() );
			$r->setPath( $t_info['path'] );
			if( isset($t_info['query']) ) {
				$r->setQuery( $t_info['query'] );
			}
			
			$cnt++;
			$em->persist( $r );
			$em->flush();
		}
		
		return $cnt;
	}
	
	
	public function splitUrlAction(Request $request)
	{
		$url = $request->get('url');
		$t_info = parse_url( base64_decode($url) );
		$t_info['protocol'] = $t_info['scheme'];
		unset( $t_info['scheme'] );
		
		$response = new Response( json_encode($t_info) );
		return $response;
	}
}
