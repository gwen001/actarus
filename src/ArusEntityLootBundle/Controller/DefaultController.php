<?php

namespace ArusEntityLootBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use ArusEntityLootBundle\Entity\ArusEntityLoot;
use ArusEntityLootBundle\Form\ArusEntityLootType;

use ArusEntityLootBundle\Entity\Search;
use ArusEntityLootBundle\Form\SearchType;


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
		$total_loot = $this->get('entity_loot')->search( $data, -1 );
		$n_page = ceil( $total_loot/$limit );
		
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
		
		$t_loot = $this->get('entity_loot')->search( $data, $page );
		$pagination = $this->get('app')->paginate( $total_loot, count($t_loot), $page );
		
		return $this->render('ArusEntityLootBundle:Default:index.html.twig', array(
			'form' => $form->createView(),
			't_loot' => $t_loot,
			't_entity_type' => $t_entity_type,
			'pagination' => $pagination,
		));
	}
	
	
	/**
	 * Create a new ArusEntityLoot entity.
	 *
	 */
	public function newAction(Request $request)
	{
		$loot = new ArusEntityLoot();
		$form = $this->createForm( new ArusEntityLootType(), $loot );
		$form->handleRequest( $request );
		
		if ($form->isSubmitted() && $form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$em->persist($loot);
			$em->flush();
			
			//$this->addFlash( 'success', 'New loot added!' );
			
			$response = new Response( json_encode(array('error'=>0)) );
			return $response;
		}
	}
	
	
	/**
	 * Edit an existing ArusEntityLoot entity.
	 *
	 */
	public function editAction(Request $request, ArusEntityLoot $loot)
	{
		$form = $this->createForm( new ArusEntityLootType(), $loot );
		$form->handleRequest( $request );
		
		if ($form->isSubmitted() && $form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$em->persist($loot);
			$em->flush();
			
			//$this->addFlash( 'success', 'Your changes were saved!' );
			
			$response = new Response( json_encode(array('error'=>0)) );
			return $response;
		}
	}
	
	
	/**
	 * Deletes a ArusEntityLoot entity.
	 *
	 */
	public function deleteAction(Request $request, ArusEntityLoot $loot)
	{
		$em = $this->getDoctrine()->getManager();
		$em->remove($loot);
		$em->flush();
		
		$response = new Response( json_encode(array('error'=>0)) );
		return $response;
	}
	
	
	/**
	 * Unconfirm a ArusEntityLoot entity.
	 *
	 */
	public function unconfirmAction(Request $request, ArusEntityLoot $loot)
	{
		$em = $this->getDoctrine()->getManager();
		$em->persist($loot);
		$em->flush();
		
		$response = new Response( json_encode(array('error'=>0)) );
		return $response;
	}
	
	
	/**
	 * Confirm a ArusEntityLoot entity.
	 *
	 */
	public function confirmAction(Request $request, ArusEntityLoot $loot)
	{
		$em = $this->getDoctrine()->getManager();
		$em->persist($loot);
		$em->flush();
		
		$response = new Response( json_encode(array('error'=>0)) );
		return $response;
	}
	
	
	public function getListAction(Request $request, $entity_id)
	{
		echo $this->get('entity_loot')->getListAction( $entity_id );
		exit();
	}
}
