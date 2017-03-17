<?php

namespace ArusTaskCallbackBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

use ArusTaskCallbackBundle\Entity\ArusTaskCallback;
use ArusTaskCallbackBundle\Entity\Search;
use ArusTaskCallbackBundle\Form\ArusTaskCallbackType;

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

		return $this->em->getRepository('ArusTaskCallbackBundle:ArusTaskCallback')->search( $data, $offset, $limit );
	}


	public function getModAction( $task )
	{
		$callback_list = $this->getListAction( $task->getId() );

//		$callback = new ArusTaskCallback();
//		$callback->setTask( $task );
//		$callbackAddForm = $this->formFactory->create( new ArusTaskCallbackType(), $callback, ['action'=>$this->router->generate('settings_task_callback_new')] );

		//$callbackEditForm = $this->createForm( new ArusTaskCallbackType() );

		return $this->templating->render(
			'ArusTaskCallbackBundle:Default:mod.html.twig', array(
				'task' => $task,
				'callback_list' => $callback_list,
				//'callback_add_form' => $callbackAddForm->createView(),
				//'callback_edit_form' => $callbackEditForm->createView(),
			)
		);
	}


	public function addMod( $task )
	{
		$em = $this->em;

		$task_callback = $this->getParameter('task')['callback'];
		$alert_level = array_flip( $this->getParameter('alert')['level'] );
		$t_task = $em->getRepository('ArusTaskBundle:ArusTask')->findArray();
		$t_technology = $em->getRepository('ArusTechnologyBundle:ArusTechnology')->findArray();

		$callback = new ArusTaskCallback();
		$callback->setTask( $task );
		$callbackAddForm = $this->createForm( new ArusTaskCallbackType(['alert_level'=>$alert_level,'t_task'=>$t_task,'t_technology'=>$t_technology,'task_callback'=>$task_callback]), $callback, ['action'=>$this->generateUrl('settings_task_callback_new')] );

		return $this->templating->render(
			'ArusTaskCallbackBundle:Default:add.html.twig', array(
				'task' => $task,
				'callback_add_form' => $callbackAddForm->createView(),
			)
		);
	}


	public function getListAction( $task_id )
	{
		$em = $this->em;

		$t_params = [];
		$t_params['alert_level'] = array_flip( $this->getParameter('alert')['level'] );
		$t_params['task'] = $em->getRepository('ArusTaskBundle:ArusTask')->findArray( 'id', 'name' );
		$t_params['technology'] = $em->getRepository('ArusTechnologyBundle:ArusTechnology')->findArray();
		$t_callback = $em->getRepository('ArusTaskCallbackBundle:ArusTaskCallback')->search( ['task_id'=>$task_id] );

		$task = $em->getRepository('ArusTaskBundle:ArusTask')->findOneById( $task_id );

		return $this->templating->render(
			'ArusTaskCallbackBundle:Default:list.html.twig', array(
				'task' => $task,
				'task_id' => $task_id,
				't_callback' => $t_callback,
				't_params' => $t_params,
			)
		);
	}
}
