<?php

namespace ArusEntityTaskBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

use ArusEntityTaskBundle\Entity\ArusEntityTask;
use ArusEntityTaskBundle\Entity\Search;
use ArusEntityTaskBundle\Form\ArusEntityTaskAddType;

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


	public function search( $data=[], $page=1, $limit=-1 )
	{
		if( $limit < 0 ) {
			$limit = $this->getParameter('results_per_page');
		}
		$offset = $limit * ($page-1);

		$result = $this->em->getRepository('ArusEntityTaskBundle:ArusEntityTask')->search( $data, $offset, $limit );

		if( $offset >= 0 && is_array($result) && count($result) ) {
			$em = $this->em;
			$t_entity_type = array_flip( $this->container->getParameter('entity')['type'] );
			foreach( $result as &$task ) {
				$e = $em->getRepository('ArusEntityTaskBundle:ArusEntityTask')->getRelatedEntity( $task, $t_entity_type );
				$task->setEntity($e);
			}
		}

		return $result;
	}


	public function computeEntityDatas( $cmd, $entity )
	{
		$m = preg_match_all('#__E_([A-Z]+)__#', $cmd, $match);

		for( $i=0 ; $i<$m ; $i++ ) {
			$f = 'get' . ucfirst(strtolower($match[1][$i]));
			if( is_callable([$entity, $f]) ) {
				$replace = $entity->$f();
			} else {
				$replace = '';
			}
			$cmd = str_replace( $match[0][$i], $replace, $cmd );
		}

		return $cmd;
	}


	public function computeOptions( $cmd, $options )
	{
		$m = preg_match_all('#__O_([A-Z]+)__#', $cmd, $match);

		for( $i=0 ; $i<$m ; $i++ ) {
			if( isset($options[$match[1][$i]]) ) {
				$replace = $options[$match[1][$i]];
			} else {
				$replace = '';
			}
			$cmd = str_replace( $match[0][$i], $replace, $cmd );
		}

		return $cmd;
	}



	public function create( $entity, $task_name, $options=array(), $cmd=null, $priority=0 )
	{
		$em = $this->em;
		$container = $this->container;

		if( !is_object($entity) ) {
			$entity = $container->get('app')->getEntityById( $entity );
		}

		if( is_int($task_name) ) {
			$t = $em->getRepository('ArusTaskBundle:ArusTask')->findOneById( $task_name );
		} else {
			$t = $em->getRepository('ArusTaskBundle:ArusTask')->findOneByName( $task_name );
		}
		if( !$t ) {
			return false;
		}

		if( !$cmd ) {
			$cmd = $t->getCommand();
			$cmd = $this->computeEntityDatas( $cmd, $entity );
			$options = array_merge( $t->getDefaultOptions(), $options );
			$cmd = $this->computeOptions( $cmd, $options );
		}

		$task = new ArusEntityTask();
		$task->setTask( $t );
		$task->setProject( $container->get('app')->getEntityProject($entity) );
		$task->setEntityId( $entity->getEntityId() );
		$task->setCommand( $cmd );
		$task->setPriority( $priority );
		$em->persist( $task );
		$em->flush( $task );

        //$t_status = array_flip( $container->getParameter('entity')['status'] );
        //$entity->setStatus( $t_status['nothing'] );
		//$em->persist( $entity );
		//$em->flush( $entity );

		return $task;
	}


	public function kill( $task )
	{
		Utils::killProcess( $task->getRealPid() );
		//Utils::killProcess( $task->getPid() );
		
		return true;
	}
	
	
	public function stop( $task )
	{
		$em = $this->em;
		$t_status = $this->container->getParameter('task')['status'];
		
		$task->setStatus( $t_status['tokill'] );
		$em->persist( $task );
		$em->flush();
		
		return true;
	}

	
	public function delete( $task )
	{
		$em = $this->em;
		$t_status = $this->container->getParameter('task')['status'];

		if( $task->getPid() && $task->getStatus() == $t_status['running'] ) {
			$this->stop( $task );
			usleep( 2000000 ); // 3 secondes
		}
		
		$em->remove( $task );
		$em->flush();
		
		return true;
	}


	public function getModAction( $entity )
	{
		$task_list = $this->getListAction( $entity->getEntityId() );

		$task = new ArusEntityTask();
		$task->setEntityId( $entity->getEntityId() );
		$taskAddForm = $this->formFactory->create( new ArusEntityTaskAddType(), $task, ['action'=>$this->router->generate('task_new')] );

		return $this->templating->render(
			'ArusEntityTaskBundle:Default:mod.html.twig', array(
				'entity' => $entity,
				//'t_task' => json_encode($t_task),
				'task_list' => $task_list,
				'task_add_form' => $taskAddForm->createView(),
			)
		);
	}


	public function getListAction($entity_id)
	{
		$em = $this->em;
		$entity = $this->get('app')->getEntityById( $entity_id );
		$t_task = $em->getRepository('ArusEntityTaskBundle:ArusEntityTask')->search( ['entity_id'=>$entity_id] );
		$t_task_list = $em->getRepository( 'ArusTaskBundle:ArusTask' )->findAll();

		foreach( $t_task_list as &$t ) {
			$cmd = $this->computeEntityDatas( $t->getCommand(), $entity );
			$cmd = $this->computeOptions( $cmd, $t->getDefaultOptions() );
			$tmp[ $t->getId() ] = ['name'=>$t->getName(),'command'=>$cmd];
		}

		return $this->templating->render(
			'ArusEntityTaskBundle:Default:list.html.twig', array(
				'entity_id' => $entity_id,
				't_task' => $t_task,
				't_task_list' => json_encode($tmp),
			)
		);
	}
	
	
	public function interpretCancelledTask()
	{
		$t_status = $this->container->getParameter('task')['status'];
		$n_update = $this->em->getRepository('ArusEntityTaskBundle:ArusEntityTask')->massUpdateStatus( $t_status['cancelled'], $t_status['finished'] );
		return $n_update;
	}
}
