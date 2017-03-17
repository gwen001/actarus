<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/*use ArusEntityAlertBundle\Entity\ArusEntityAlert;
use ArusEntityCommentBundle\Entity\ArusEntityComment;
use ArusEntityLootBundle\Entity\ArusEntityLoot;
use ArusEntityTaskBundle\Entity\ArusEntityTask;
use ArusEntityTechnologyBundle\Entity\ArusEntityTechnology;
*/

use Actarus\Utils;


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


	public function getActarus()
	{
		$actarus_id = $this->container->getParameter( 'actarus_id' );
		$actarus = $this->container->get('project')->search( ['id'=>$actarus_id] );
		$actarus = $actarus[0];

		return $actarus;
	}


	public function getEntityById( $entity_id )
	{
		$t_entity_type = array_flip( $this->container->getParameter('entity')['type'] );

		$type = substr( $entity_id, 0, 1 );
		$class = 'Arus'.ucfirst($t_entity_type[$type]);
		$entity = $this->em->getRepository($class.'Bundle:'.$class)->findOneByEntityId( $entity_id );

		return $entity;
	}


	public function getEntityProject( $entity )
	{
		if( self::isProject($entity) ) {
			return $entity;
		} else {
			return $entity->getProject();
		}
	}
	public function isProject( $entity ) {
		if( (int)substr($entity->getEntityId(),0,1) == 1 ) {
			return true;
		} else {
			return false;
		}
	}


	public function paginate( $total, $count, $page )
	{
		if( (int)$total <= 0 ) {
			return '';
		}

		$limit = $this->container->getParameter('results_per_page');
		$n_page = ceil( $total/$limit );

		$p_showing = $limit * ($page-1);
		$p_to = $p_showing + $count - 1;
		$p_of = $total;

		return $this->templating->render(
			'AppBundle:Default:pagination.html.twig', array(
				'p_showing' => $p_showing,
				'p_to' => $p_to,
				'p_of' => $p_of,
				'page' => $page,
				'n_page' => $n_page,
			)
		);
	}


	public function recon( $entity, $entity_type )
	{
		$cnt = 0;
		$t_recon = $this->container->getParameter('entity')['recon'][$entity_type];

		foreach( $t_recon as $r ) {
			$u = $this->container->get('entity_task')->create( $entity, $r );
			if( $u ) {
				$cnt++;
			}
		}

		return $cnt;
	}


	public function getRelatedEntityObject( $entity, $count=false )
	{
		if( $count ) {
			$cnt = -1;
		} else {
			$cnt = null;
		}

		$em = $this->em;
		$alert      = $em->getRepository('ArusEntityAlertBundle:ArusEntityAlert')->search( ['entity_id'=>$entity->getEntityId()], $cnt );
		//$comment    = $em->getRepository('ArusEntityCommentBundle:ArusEntityComment')->search( ['entity_id'=>$entity->getEntityId()], $cnt );
		//$loot       = $em->getRepository('ArusEntityLootBundle:ArusEntityLoot')->search( ['entity_id'=>$entity->getEntityId()], $cnt );
		$task       = $em->getRepository('ArusEntityTaskBundle:ArusEntityTask')->search( ['entity_id'=>$entity->getEntityId()], $cnt );
		$technology = $em->getRepository('ArusEntityTechnologyBundle:ArusEntityTechnology')->search( ['entity_id'=>$entity->getEntityId()], $cnt );

		if( $count ) {
			$entity->setEntityAlerts( array_fill(0, $alert, null) );
			//$entity->setEntityComments( array_fill(0, $comment, null) );
			//$entity->setEntityLoots( array_fill(0, $loot, null) );
			$entity->setEntityTasks( array_fill(0, $task, null) );
			$entity->setEntityTechnologies( array_fill(0, $technology, null) );
		} else {
			$entity->setEntityAlerts( $alert );
			//$entity->setEntityComments( $comment );
			//$entity->setEntityLoots( $loot );
			$entity->setEntityTasks( $task );
			$entity->setEntityTechnologies( $technology );
		}
	}


	public function entityDelete( $entity )
	{
		$em = $this->em;
		$em->getRepository('ArusEntityAlertBundle:ArusEntityAlert')->deleteEntity( $entity );
		//$em->getRepository('ArusEntityCommentBundle:ArusEntityComment')->deleteEntity( $entity );
		//$em->getRepository('ArusEntityLootBundle:ArusEntityLoot')->deleteEntity( $entity );
		$em->getRepository('ArusEntityTaskBundle:ArusEntityTask')->deleteEntity( $entity );
		$em->getRepository('ArusEntityTechnologyBundle:ArusEntityTechnology')->deleteEntity( $entity );

		return true;
	}
}
