<?php

namespace ArusEntityTechnologyBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use ArusTechnologyBundle\Entity\ArusTechnology;

use ArusEntityTechnologyBundle\Entity\ArusEntityTechnology;
use ArusEntityTechnologyBundle\Form\ArusEntityTechnologyType;
use ArusEntityTechnologyBundle\Entity\Search;
use ArusEntityTechnologyBundle\Form\SearchType;


class DefaultController extends Controller
{
	public function indexAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();

		$t_entity_type = array_flip( $this->getParameter('entity')['type'] );

		$search = new Search();
		$form = $this->createForm( new SearchType(array('t_entity_type'=>$t_entity_type)), $search );
		$form->handleRequest($request);

		$data = null;
		if( $form->isSubmitted() && $form->isValid() )  {
			$data = $form->getData();
		}

		$page = 1;
		$limit = $this->getParameter('results_per_page');
		$total_techno = $this->get('entity_technology')->search( $data, -1 );
		$n_page = ceil( $total_techno/$limit );

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

		$t_techno = $this->get('entity_technology')->search( $data, $page );
		foreach( $t_techno as $alt ) {
			$e = $em->getRepository('ArusEntityTechnologyBundle:ArusEntityTechnology')->getRelatedEntity( $alt, $t_entity_type );
			$alt->setEntity( $e );
		}

		$pagination = $this->get('app')->paginate( $total_techno, count($t_techno), $page );

		return $this->render('ArusEntityTechnologyBundle:Default:index.html.twig', array(
			'form' => $form->createView(),
			't_techno' => $t_techno,
			't_entity_type' => $t_entity_type,
			'pagination' => $pagination,
		));
	}


	/**
	 * Create a new ArusEntityTechnology entity.
	 *
	 */
	public function newAction(Request $request)
	{
		$r = ['error'=>0];

		$techno = new ArusEntityTechnology();
		$form = $this->createForm( new ArusEntityTechnologyType(), $techno );
		$form->handleRequest( $request );

		if ($form->isSubmitted() && $form->isValid()) {
			$this->get('entity_technology')->create( $techno->getTechnology(), $techno->getEntityId(), $techno->getVersion() );
			//$this->addFlash( 'success', 'New technology added!' );
		}
		else {
			$r['error'] = 1;
		}

		$response = new Response( json_encode($r) );
		return $response;
	}


	/**
	 * Edit an existing ArusEntityTechnology entity.
	 *
	 */
	public function editAction(Request $request, ArusEntityTechnology $techno)
	{
		$r = ['error'=>0];

		$form = $this->createForm( new ArusEntityTechnologyType(), $techno );
		$form->handleRequest( $request );

		if ($form->isSubmitted() && $form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$em->persist($techno);
			$em->flush();

			//$this->addFlash( 'success', 'Your changes were saved!' );

		}

		$response = new Response( json_encode($r) );
		return $response;
	}


	/**
	 * Deletes a ArusEntityTechnology entity.
	 *
	 */
	public function deleteAction(Request $request, ArusEntityTechnology $techno)
	{
		$r = ['error'=>0];

		$em = $this->getDoctrine()->getManager();
		$em->remove($techno);
		$em->flush();

		$response = new Response( json_encode($r) );
		return $response;
	}


	public function getListAction(Request $request, $entity_id)
	{
		echo $this->get('entity_technology')->getListAction( $entity_id );
		exit();
	}
}
