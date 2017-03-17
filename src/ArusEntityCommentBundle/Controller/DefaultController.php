<?php

namespace ArusEntityCommentBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use ArusEntityCommentBundle\Entity\ArusEntityComment;
use ArusEntityCommentBundle\Form\ArusEntityCommentType;

use ArusEntityCommentBundle\Entity\Search;
use ArusEntityCommentBundle\Form\SearchType;


class DefaultController extends Controller
{
	public function indexAction(Request $request)
	{
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
		$total_comment = $this->get('entity_comment')->search( $data, -1 );
		$n_page = ceil( $total_comment/$limit );
		
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
		
		$t_comment = $this->get('entity_comment')->search( $data, $page );
		$pagination = $this->get('app')->paginate( $total_comment, count($t_comment), $page );
		
		return $this->render('ArusEntityCommentBundle:Default:index.html.twig', array(
			'form' => $form->createView(),
			't_comment' => $t_comment,
			't_entity_type' => $t_entity_type,
			'pagination' => $pagination,
		));
	}
	
	
	/**
	 * Create a new ArusEntityComment entity.
	 *
	 */
	public function newAction(Request $request)
	{
		$comment = new ArusEntityComment();
		$form = $this->createForm( new ArusEntityCommentType(), $comment );
		$form->handleRequest( $request );
		
		if ($form->isSubmitted() && $form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$em->persist($comment);
			$em->flush();
			
			//$this->addFlash( 'success', 'New comment added!' );
			
			$response = new Response( json_encode(array('error'=>0)) );
			return $response;
		}
	}
	
	
	/**
	 * Edit an existing ArusEntityComment entity.
	 *
	 */
	public function editAction(Request $request, ArusEntityComment $comment)
	{
		$form = $this->createForm( new ArusEntityCommentType(), $comment );
		$form->handleRequest( $request );
		
		if ($form->isSubmitted() && $form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$em->persist($comment);
			$em->flush();
			
			//$this->addFlash( 'success', 'Your changes were saved!' );
			
			$response = new Response( json_encode(array('error'=>0)) );
			return $response;
		}
	}
	
	
	/**
	 * Deletes a ArusEntityComment entity.
	 *
	 */
	public function deleteAction(Request $request, ArusEntityComment $comment)
	{
		$em = $this->getDoctrine()->getManager();
		$em->remove($comment);
		$em->flush();
		
		$response = new Response( json_encode(array('error'=>0)) );
		return $response;
	}
	
	
	public function getListAction(Request $request, $entity_id)
	{
		echo $this->get('entity_comment')->getListAction( $entity_id );
		exit();
	}
}
