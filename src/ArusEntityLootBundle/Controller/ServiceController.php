<?php

namespace ArusEntityLootBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use ArusEntityLootBundle\Entity\ArusEntityLoot;
use ArusEntityLootBundle\Entity\Search;
use ArusEntityLootBundle\Form\ArusEntityLootType;


class ServiceController extends Controller
{
	protected $em;
	
	protected $container;
	
	protected $router;
	
	protected $formFactory;
	
	protected $templating;
	
	
	public function __construct( $entityManager, $container, $router, $formFactory, $templating ) {
		$this->em          = $entityManager;
		$this->container   = $container;
		$this->router      = $router;
		$this->formFactory = $formFactory;
		$this->templating  = $templating;
	}
	
	
	public function search( $data=[], $page=1, $limit=-1 )
	{
		if( $limit < 0 ) {
			$limit = $this->getParameter('results_per_page');
		}
		$limit = $this->getParameter('results_per_page');
		$offset = $limit * ($page-1);
		
		$result = $this->em->getRepository('ArusEntityLootBundle:ArusEntityLoot')->search( $data, $offset, $limit );
		
		if( $offset >= 0 && is_array($result) && count($result) ) {
			$em = $this->em;
			$t_entity_type = array_flip( $this->container->getParameter('entity')['type'] );
			foreach ($result as $loot) {
				$e = $em->getRepository('ArusEntityLootBundle:ArusEntityLoot')->getRelatedEntity($loot, $t_entity_type);
				$loot->setEntity($e);
			}
		}
		
		return $result;
	}
	
	
	public function getModAction( $entity )
	{
		$loot_list = $this->get('entity_loot')->getListAction( $entity->getEntityId() );
		
		$loot = new ArusEntityLoot();
		$loot->setEntityId( $entity->getEntityId() );
		$lootAddForm = $this->createForm( new ArusEntityLootType(), $loot, ['action'=>$this->router->generate('loot_new')] );
		
		$lootEditForm = $this->createForm( new ArusEntityLootType() );

		return $this->templating->render(
			'ArusEntityLootBundle:Default:mod.html.twig', array(
				'entity' => $entity,
				'loot_list' => $loot_list,
				'loot_add_form' => $lootAddForm->createView(),
				'loot_edit_form' => $lootEditForm->createView(),
			)
		);
	}
	
	
	public function getListAction($entity_id)
	{
		$em = $this->em;
		
		$search = new Search();
		$search->setEntityId( $entity_id );
		$t_loot = $em->getRepository('ArusEntityLootBundle:ArusEntityLoot')->search( $search );
		
		return $this->templating->render(
			'ArusEntityLootBundle:Default:list.html.twig', array(
				'entity_id' => $entity_id,
				't_loot' => $t_loot,
			)
		);
	}
}
